<?php

namespace App\Imports;

use App\Models\Action;
use App\Models\Activite;
use App\Models\BudgetLigne;
use App\Models\Structure;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Charge un fichier PDF-PAR (une structure / un exercice) :
 *  - feuille « budget associé au programme » → activités + lignes budgétaires (AE/CP) ;
 *  - feuille « Programme d'activité annuelle » → enrichit le plan d'activité.
 *
 * Idempotent : ré-importer le même fichier remplace les données concernées
 * (upsert par exercice + code d'activité ; les lignes de chaque activité sont réécrites).
 */
class BudgetParImport
{
    public int $activites = 0;
    public int $lignes = 0;
    public int $planEnrichi = 0;
    public array $infos = [];

    /** Mots non significatifs ignorés pour le calcul des initiales d'un chapitre. */
    private const STOP = ['de', 'des', 'du', 'd', 'et', 'la', 'le', 'les', 'a', 'aux', 'au', 'en'];

    private array $structures = [];
    private array $lignesNettoyees = [];

    public function run(string $file, int $exerciceDefaut = 2026): void
    {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $wb = $reader->load($file);

        $actions = Action::pluck('id', 'code');
        $this->structures = Structure::get(['id', 'code', 'libelle'])->all();

        // ── Feuille budget ────────────────────────────────────────────
        $structureDetectee = null;
        foreach (array_slice($this->feuille($wb, 'budget associé au programme'), 1) as $r) {
            $codeAct = trim((string) ($r[7] ?? ''));
            if ($codeAct === '') {
                continue;
            }
            $exercice = ((int) ($r[0] ?? 0)) ?: $exerciceDefaut;
            $libChap = trim((string) ($r[6] ?? ''));
            $structure = $this->resoudreStructure($libChap);
            $structureDetectee ??= $structure;

            $activite = Activite::updateOrCreate(
                ['exercice' => $exercice, 'code' => $codeAct],
                [
                    'libelle'          => trim((string) ($r[8] ?? $codeAct)),
                    'action_id'        => $actions[trim((string) ($r[4] ?? ''))] ?? null,
                    'structure_id'     => $structure?->id,
                    'code_chapitre'    => trim((string) ($r[5] ?? '')) ?: null,
                    'libelle_chapitre' => $libChap ?: null,
                ]
            );
            if ($activite->wasRecentlyCreated) {
                $this->activites++;
            }

            // Réécriture des lignes : on vide une fois par activité dans ce run.
            if (! isset($this->lignesNettoyees[$activite->id])) {
                $activite->lignes()->delete();
                $this->lignesNettoyees[$activite->id] = true;
            }

            BudgetLigne::create([
                'activite_id'       => $activite->id,
                'exercice'          => $exercice,
                'code_article'      => trim((string) ($r[9] ?? '')) ?: null,
                'code_paragraphe'   => trim((string) ($r[10] ?? '')) ?: null,
                'libelle_categorie' => trim((string) ($r[11] ?? '')) ?: null,
                'montant_ae'        => (float) ($r[12] ?? 0),
                'montant_cp'        => (float) ($r[13] ?? 0),
            ]);
            $this->lignes++;
        }

        // ── Feuille programme d'activité ──────────────────────────────
        $par = $this->feuille($wb, "Programme d'activité annuelle");
        $objStrat = $this->apresDeuxPoints($par[1][1] ?? '');
        $objOp = $this->apresDeuxPoints($par[3][1] ?? '');

        foreach (array_slice($par, 5) as $r) {
            $code = trim((string) ($r[0] ?? ''));
            if ($code === '' || ! ctype_digit($code)) {
                continue;
            }
            $activite = Activite::updateOrCreate(
                ['exercice' => $exerciceDefaut, 'code' => $code],
                ['libelle' => trim((string) ($r[1] ?? $code))]
            );
            if ($activite->wasRecentlyCreated) {
                $this->activites++;
            }

            $activite->update([
                'objectif_strategique'  => $objStrat,
                'objectif_operationnel' => $objOp,
                'indicateur'            => trim((string) ($r[2] ?? '')) ?: null,
                'valeur_initiale'       => trim((string) ($r[3] ?? '')) ?: null,
                'cible'                 => trim((string) ($r[4] ?? '')) ?: null,
                'localite'              => trim((string) ($r[5] ?? '')) ?: null,
                'montant'               => (float) ($r[8] ?? 0),
                'trimestre_1'           => (float) ($r[9] ?? 0),
                'trimestre_2'           => (float) ($r[10] ?? 0),
                'trimestre_3'           => (float) ($r[11] ?? 0),
                'trimestre_4'           => (float) ($r[12] ?? 0),
            ]);
            $this->planEnrichi++;
        }

        $this->infos[] = $structureDetectee
            ? "Structure exécutante : {$structureDetectee->libelle}"
            : 'Structure exécutante non identifiée — chapitre conservé en texte.';
    }

    /** Résout une structure depuis le libellé de chapitre (contient, ou initiales : DRH). */
    private function resoudreStructure(string $libChap): ?Structure
    {
        $ref = Str::of($libChap)->ascii()->upper()->trim()->value();
        if ($ref === '') {
            return null;
        }

        foreach ($this->structures as $s) {
            $lib = Str::of($s->libelle)->ascii()->upper()->value();
            $initiales = collect(preg_split('/\s+/', Str::of($s->libelle)->ascii()->lower()->value()))
                ->reject(fn ($m) => $m === '' || in_array($m, self::STOP, true))
                ->map(fn ($m) => Str::upper(mb_substr($m, 0, 1)))
                ->implode('');

            if ($initiales === $ref || str_contains($lib, $ref)) {
                return $s;
            }
        }
        return null;
    }

    private function feuille($wb, string $pattern): array
    {
        foreach ($wb->getSheetNames() as $name) {
            if (preg_match('/' . preg_quote($pattern, '/') . '/u', $name)) {
                return $wb->getSheetByName($name)->toArray(null, true, true, false);
            }
        }
        return [];
    }

    private function apresDeuxPoints($valeur): ?string
    {
        $v = trim((string) $valeur);
        if (! str_contains($v, ':')) {
            return $v ?: null;
        }
        return trim(substr($v, strpos($v, ':') + 1)) ?: null;
    }
}
