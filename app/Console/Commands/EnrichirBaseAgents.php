<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Emploi;
use App\Models\Fonction;
use App\Models\Indice;
use App\Models\PositionAdministrative;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Enrichit les agents (par MATRICULE) depuis le fichier « Base des agents » :
 * grille indiciaire (catégorie/échelle/classe/échelon/indice), lieu d'exercice,
 * position administrative, fonction, dates, volume horaire, distinction, contact.
 *
 * - Indice : résolu via le code catégorie+échelle+classe+échelon (table indices).
 * - Fonction : mappée UNIQUEMENT vers le référentiel existant (jamais créée).
 * - Position « En Activité » → « En poste » (ENPOSTE).
 * - Structure (niveaux 1-4), localité et spécialité : étape séparée (non traités ici).
 */
class EnrichirBaseAgents extends Command
{
    protected $signature = 'agents:enrichir-base {--dry-run : Analyse sans aucune écriture}';

    protected $description = 'Enrichit les agents (par matricule) depuis « Base des agents » (grille, lieu, position, fonction, dates…).';

    public function handle(): int
    {
        ini_set('memory_limit', '5120M');

        $fichier = base_path('../pour gesperes/Base des agents.xlsx');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');

        $maps = $this->maps();
        $agents = Agent::withTrashed()->get()->keyBy(fn ($a) => strtoupper(trim((string) $a->matricule)));

        $rows = $this->lignes($fichier);
        $this->info(count($rows) . ' ligne(s) lue(s).');

        $stats = array_fill_keys(['maj', 'inchanges', 'absents', 'doublons', 'indice_ok', 'indice_ko', 'fonction_ok', 'position_ok'], 0);
        $seen = [];

        $traiter = function () use ($rows, $agents, $maps, $dry, &$stats, &$seen) {
            foreach ($rows as $r) {
                $matricule = strtoupper(trim((string) ($r[1] ?? '')));
                if ($matricule === '' || $matricule === 'MATRICULE') {
                    continue;
                }
                if (isset($seen[$matricule])) {
                    $stats['doublons']++;
                    continue;
                }
                $seen[$matricule] = true;

                $agent = $agents->get($matricule);
                if (! $agent) {
                    $stats['absents']++;
                    continue;
                }

                $champs = $this->champs($r, $maps, $stats);

                if ($agent->trashed()) {
                    if (! $dry) {
                        $agent->restore();
                    }
                }
                $agent->fill($champs);
                if ($agent->isDirty()) {
                    $stats['maj']++;
                    if (! $dry) {
                        $agent->save();
                    }
                } else {
                    $stats['inchanges']++;
                }
            }
        };

        if ($dry) {
            $traiter();
        } else {
            DB::transaction(fn () => Model::withoutEvents($traiter));
        }

        $this->bilan($dry, $stats);

        return self::SUCCESS;
    }

    private function champs(array $r, array $maps, array &$stats): array
    {
        $champs = [];
        $this->set($champs, 'cle', $this->cle($r[2] ?? null));
        $this->set($champs, 'nom', $this->txt($r[3] ?? null));
        $this->set($champs, 'prenoms', $this->txt($r[4] ?? null));
        $this->set($champs, 'sexe', $this->sexe($r[5] ?? null));
        $this->set($champs, 'date_naissance', $this->date($r[6] ?? null));
        $this->set($champs, 'date_integration', $this->date($r[7] ?? null));
        $this->set($champs, 'date_effet_emploi', $this->date($r[9] ?? null));
        $this->set($champs, 'date_affectation', $this->date($r[17] ?? null));
        $this->set($champs, 'lieu_exercice', $this->lieu($r[10] ?? null));
        $this->set($champs, 'volume_horaire_du', $this->entier($r[26] ?? null));
        $this->set($champs, 'volume_horaire_assure', $this->entier($r[27] ?? null));
        $this->set($champs, 'distinction_honorifique', $this->txt($r[28] ?? null));
        $this->set($champs, 'telephone', $this->txt($r[29] ?? null));

        // Emploi (référentiel + alias).
        $this->set($champs, 'emploi_id', $maps['emploi'][$this->norm($r[8] ?? '')] ?? null);

        // Fonction : mapping vers l'existant uniquement.
        $idFonction = $maps['fonction'][$this->norm($r[11] ?? '')] ?? null;
        if ($idFonction) {
            $champs['fonction_id'] = $idFonction;
            $stats['fonction_ok']++;
        }

        // Grille : catégorie / échelle / classe / échelon.
        $cat = strtoupper(trim((string) ($r[12] ?? '')));
        $ech = strtoupper(trim((string) ($r[13] ?? '')));
        $cl  = trim((string) ($r[14] ?? ''));
        $echelon = trim((string) ($r[15] ?? ''));

        $this->set($champs, 'categorie_id', $maps['categorie'][$cat] ?? $maps['categorie'][$this->norm($cat)] ?? null);
        $this->set($champs, 'echelle_id', $maps['echelle']['ECHL' . $ech] ?? null);
        $this->set($champs, 'classe_id', $maps['classe']['CL' . $cl] ?? null);
        $this->set($champs, 'echelon_id', $maps['echelon']['ECH' . $echelon] ?? null);

        // Indice : code catégorie+échelle+classe+échelon (ex. A121, PB112).
        if ($cat !== '' && $ech !== '' && $cl !== '' && $echelon !== '') {
            $code = $cat . $ech . $cl . $echelon;
            if ($id = $maps['indice'][$code] ?? null) {
                $champs['indice_id'] = $id;
                $stats['indice_ok']++;
            } else {
                $stats['indice_ko']++;
            }
        }

        // Position administrative (« En Activité » → ENPOSTE via alias).
        if ($id = $maps['position'][$this->norm($r[18] ?? '')] ?? null) {
            $champs['position_administrative_id'] = $id;
            $stats['position_ok']++;
        }

        return $champs;
    }

    private function maps(): array
    {
        $emploi = [];
        foreach (Emploi::all() as $e) {
            $emploi[$this->norm($e->libelle)] = $e->id;
            $emploi[$this->norm($e->code)] = $e->id;
        }
        $parCode = Emploi::pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [$this->norm($c) => $id]);
        foreach ((array) config('emplois_alias', []) as $lib => $code) {
            if ($id = $parCode[$this->norm($code)] ?? null) {
                $emploi[$this->norm($lib)] = $id;
            }
        }

        $categorie = [];
        foreach (Categorie::all() as $c) {
            $categorie[strtoupper($c->code)] = $c->id;
            $categorie[$this->norm($c->libelle)] = $c->id;
        }

        $fonction = [];
        foreach (Fonction::all() as $f) {
            $fonction[$this->norm($f->libelle)] = $f->id;
        }

        // Position : libellés + alias « en activite » → En poste.
        $position = [];
        $enPoste = null;
        foreach (PositionAdministrative::all() as $p) {
            $position[$this->norm($p->libelle)] = $p->id;
            if (strtoupper($p->code) === 'ENPOSTE') {
                $enPoste = $p->id;
            }
        }
        if ($enPoste) {
            $position['en activite'] = $enPoste;
        }

        return [
            'emploi'    => $emploi,
            'categorie' => $categorie,
            'fonction'  => $fonction,
            'position'  => $position,
            'echelle'   => Echelle::pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [strtoupper($c) => $id])->all(),
            'classe'    => Classe::pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [strtoupper($c) => $id])->all(),
            'echelon'   => Echelon::pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [strtoupper($c) => $id])->all(),
            'indice'    => Indice::pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [strtoupper($c) => $id])->all(),
        ];
    }

    private function lignes(string $fichier): array
    {
        $reader = IOFactory::createReaderForFile($fichier);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell($col, $row, $ws = ''): bool
            {
                return Coordinate::columnIndexFromString($col) <= 30 && $row >= 2;
            }
        });

        return $reader->load($fichier)->getActiveSheet()->toArray(null, true, false, false);
    }

    private function bilan(bool $dry, array $stats): void
    {
        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Bilan de l\'enrichissement :');
        $this->table(['Indicateur', 'Valeur'], [
            ['Agents mis à jour' . ($dry ? ' (simulé)' : ''), $stats['maj']],
            ['Agents inchangés', $stats['inchanges']],
            ['Matricules absents de la base (ignorés)', $stats['absents']],
            ['Doublons de matricule (ignorés)', $stats['doublons']],
            ['Indice rattaché', $stats['indice_ok']],
            ['Indice non résolu (hors grille)', $stats['indice_ko']],
            ['Fonction rattachée', $stats['fonction_ok']],
            ['Position rattachée', $stats['position_ok']],
        ]);
        if ($dry) {
            $this->line('→ Relancez sans --dry-run pour appliquer.');
        }
    }

    // --- Helpers ---

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

    private function lieu($v): ?string
    {
        return match ($this->norm($v ?? '')) {
            'en classe' => 'en_classe',
            'au bureau' => 'au_bureau',
            default => null,
        };
    }

    private function entier($v): ?int
    {
        return (is_numeric($v) && (int) $v > 0) ? (int) $v : null;
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

    private function norm($s): string
    {
        $s = Str::of((string) $s)->ascii()->lower()->value();
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $s));
    }
}
