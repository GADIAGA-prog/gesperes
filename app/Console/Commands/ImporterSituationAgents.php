<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Categorie;
use App\Models\Emploi;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Met à jour la base agents (rapprochement par MATRICULE) à partir du fichier
 * nominatif « SITUATION DU PERSONNEL ». Champs directs uniquement : état civil,
 * sexe, emploi, catégorie, contact, dates. Le rattachement STRUCTURE (NIVEAU 1/2)
 * fait l'objet d'une étape séparée (harmonisation des structures via le PAT).
 *
 * Les matricules absents de la base sont COMPTÉS et listés, jamais créés.
 */
class ImporterSituationAgents extends Command
{
    protected $signature = 'agents:importer-situation {--dry-run : Analyse sans aucune écriture}';

    protected $description = 'Met à jour les agents (par matricule) depuis le fichier SITUATION (champs directs).';

    public function handle(): int
    {
        ini_set('memory_limit', '3072M');

        $fichier = (string) config('gesperes.gesper_situation_path');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');

        [$emploiMap, $categorieMap] = $this->referentiels();
        // withTrashed : la contrainte unique sur matricule couvre aussi les agents
        // soft-deleted ; on doit donc les voir (et les restaurer le cas échéant).
        $existants = Agent::withTrashed()->get()->keyBy(fn ($a) => $this->cleMatricule($a->matricule));

        $rows = $this->lignes($fichier);
        $this->info(count($rows) . ' ligne(s) lue(s).');

        $stats = ['crees' => 0, 'maj' => 0, 'inchanges' => 0, 'doublons' => 0, 'incomplets' => 0, 'emploi_non_resolu' => 0, 'cat_non_resolue' => 0];
        $emploiInconnus = [];
        $incompletsEx = [];
        $seen = [];

        $traiter = function () use ($rows, $existants, $emploiMap, $categorieMap, $dry, &$stats, &$emploiInconnus, &$incompletsEx, &$seen) {
            foreach ($rows as $r) {
                $matricule = trim((string) ($r[1] ?? ''));
                if ($matricule === '' || $this->norm($matricule) === 'matricule') {
                    continue;
                }
                $cle = $this->cleMatricule($matricule);

                // Dédoublonnage : une seule ligne traitée par matricule.
                if (isset($seen[$cle])) {
                    $stats['doublons']++;
                    continue;
                }
                $seen[$cle] = true;

                $champs = $this->champs($r, $emploiMap, $categorieMap, $stats, $emploiInconnus);

                // Mise à jour (matricule déjà en base, supprimé ou non).
                if ($agent = $existants->get($cle)) {
                    $restaure = $agent->trashed();
                    if ($restaure && ! $dry) {
                        $agent->restore();
                    }
                    $agent->fill($champs);
                    if ($agent->isDirty() || $restaure) {
                        $stats['maj']++;
                        if (! $dry) {
                            $agent->save();
                        }
                    } else {
                        $stats['inchanges']++;
                    }
                    continue;
                }

                // Création (matricule absent de la base).
                if (($champs['nom'] ?? '') === '' || ($champs['sexe'] ?? '') === '') {
                    $stats['incomplets']++;
                    if (count($incompletsEx) < 15) {
                        $incompletsEx[] = $matricule;
                    }
                    continue;
                }

                $champs['matricule'] = $matricule;
                $champs['prenoms'] = $champs['prenoms'] ?? '';
                $stats['crees']++;
                if (! $dry) {
                    Agent::create($champs);
                }
            }
        };

        // Import de masse : audit désactivé (sinon 43k entrées de journal).
        if ($dry) {
            $traiter();
        } else {
            DB::transaction(fn () => Model::withoutEvents($traiter));
        }

        $this->afficherBilan($dry, $stats, $incompletsEx, $emploiInconnus);

        return self::SUCCESS;
    }

    /** Construit le tableau des champs agent à mettre à jour (valeurs non vides). */
    private function champs(array $r, array $emploiMap, array $categorieMap, array &$stats, array &$emploiInconnus): array
    {
        $champs = [];
        $this->set($champs, 'cle', $this->cle($r[2] ?? null)); // R2 : clé alphabétique en MAJUSCULE
        $this->set($champs, 'nom', $this->txt($r[3] ?? null));
        $this->set($champs, 'prenoms', $this->txt($r[4] ?? null));
        $this->set($champs, 'sexe', $this->sexe($r[5] ?? null));
        $this->set($champs, 'telephone', $this->txt($r[10] ?? null));
        $this->set($champs, 'date_naissance', $this->date($r[12] ?? null));
        $this->set($champs, 'date_retraite', $this->date($r[13] ?? null));

        // Emploi (référentiel + alias).
        $emploi = trim((string) ($r[6] ?? ''));
        if ($emploi !== '' && $this->norm($emploi) !== 'emploi') {
            $id = $emploiMap[$this->norm($emploi)] ?? null;
            if ($id) {
                $champs['emploi_id'] = $id;
            } else {
                $stats['emploi_non_resolu']++;
                $emploiInconnus[$emploi] = ($emploiInconnus[$emploi] ?? 0) + 1;
            }
        }

        // Catégorie.
        $cat = trim((string) ($r[7] ?? ''));
        if ($cat !== '') {
            $id = $categorieMap[$this->norm($cat)] ?? $categorieMap[strtoupper($cat)] ?? null;
            if ($id) {
                $champs['categorie_id'] = $id;
            } else {
                $stats['cat_non_resolue']++;
            }
        }

        return $champs;
    }

    private function referentiels(): array
    {
        $emploiMap = [];
        foreach (Emploi::all() as $e) {
            $emploiMap[$this->norm($e->libelle)] = $e->id;
            $emploiMap[$this->norm($e->code)] = $e->id;
        }
        // Alias fichier → code → id.
        $parCode = Emploi::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [$this->norm($code) => $id]);
        foreach ((array) config('emplois_alias', []) as $libelle => $code) {
            if ($id = $parCode[$this->norm($code)] ?? null) {
                $emploiMap[$this->norm($libelle)] = $id;
            }
        }

        $categorieMap = [];
        foreach (Categorie::all() as $c) {
            $categorieMap[$this->norm($c->libelle)] = $c->id;
            $categorieMap[strtoupper($c->code)] = $c->id;
            $categorieMap[$this->norm($c->code)] = $c->id;
        }

        return [$emploiMap, $categorieMap];
    }

    /** Lignes de la feuille BASE GLOBALE (colonnes A→N, à partir de la ligne 11). */
    private function lignes(string $fichier): array
    {
        $reader = IOFactory::createReaderForFile($fichier);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['BASE GLOBALE']);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell($col, $row, $ws = ''): bool
            {
                return Coordinate::columnIndexFromString($col) <= 14 && $row >= 11;
            }
        });

        return $reader->load($fichier)->getActiveSheet()->toArray(null, true, false, false);
    }

    private function afficherBilan(bool $dry, array $stats, array $incompletsEx, array $emploiInconnus): void
    {
        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Bilan de l\'import :');
        $this->table(['Indicateur', 'Valeur'], [
            ['Agents créés' . ($dry ? ' (simulé)' : ''), $stats['crees']],
            ['Agents mis à jour' . ($dry ? ' (simulé)' : ''), $stats['maj']],
            ['Agents inchangés', $stats['inchanges']],
            ['Doublons de matricule (ignorés)', $stats['doublons']],
            ['Lignes incomplètes (nom/sexe manquant)', $stats['incomplets']],
            ['Emplois non résolus', $stats['emploi_non_resolu']],
            ['Catégories non résolues', $stats['cat_non_resolue']],
        ]);

        if ($incompletsEx !== []) {
            $this->warn('Exemples de lignes incomplètes : ' . implode(', ', $incompletsEx) . ' …');
        }
        if ($emploiInconnus !== []) {
            arsort($emploiInconnus);
            $this->warn('Emplois non résolus : ' . implode(' | ', array_map(
                fn ($v, $n) => "{$v} ({$n})",
                array_keys($emploiInconnus),
                array_values($emploiInconnus)
            )));
        }
        if ($dry) {
            $this->line('→ Relancez sans --dry-run pour appliquer.');
        }
    }

    // --- Helpers de conversion ---

    private function set(array &$champs, string $cle, $valeur): void
    {
        if ($valeur !== null && $valeur !== '') {
            $champs[$cle] = $valeur;
        }
    }

    private function txt($v): ?string
    {
        $v = trim((string) ($v ?? ''));
        return $v === '' ? null : $v;
    }

    private function cle($v): ?string
    {
        // R2 : la clé est alphabétique, en MAJUSCULE.
        $c = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($v ?? '')));
        return $c === '' ? null : $c;
    }

    private function sexe($v): ?string
    {
        $c = strtoupper(trim((string) ($v ?? '')));
        return match (true) {
            str_starts_with($c, 'M') => 'M',
            str_starts_with($c, 'F') => 'F',
            default => null,
        };
    }

    private function date($v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, trim((string) $v));
            if ($d !== false) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }

    private function cleMatricule(?string $m): string
    {
        // Matricule brut normalisé (correspond à la contrainte unique en base).
        return strtoupper(trim((string) $m));
    }

    private function norm(string $s): string
    {
        $s = Str::of($s)->ascii()->lower()->value();
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $s));
    }
}
