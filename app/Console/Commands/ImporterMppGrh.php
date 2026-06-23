<?php

namespace App\Console\Commands;

use App\Models\MppProcessus;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importe le référentiel MPP GRH (manuel des processus et procédures) depuis
 * Referentiel_MPP_GRH.xlsx : une feuille = un processus ; chaque feuille liste
 * les procédures et leurs opérations (structure responsable, fait générateur,
 * tâches, intervenants, résultats, délais). Idempotent : table reconstruite.
 */
class ImporterMppGrh extends Command
{
    protected $signature = 'mpp:importer';

    protected $description = 'Importe le référentiel MPP GRH (processus → procédures → opérations).';

    public function handle(): int
    {
        $fichier = (string) config('gesperes.gesper_mpp_path');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }

        // Reconstruction complète (les FK cascadent).
        MppProcessus::query()->delete();

        $classeur = IOFactory::load($fichier);
        $nbProc = 0;
        $nbOps = 0;

        foreach ($classeur->getAllSheets() as $i => $feuille) {
            [$numero, $libelle] = $this->titre($feuille->getTitle(), $i + 1);
            $processus = MppProcessus::create([
                'numero'  => $numero,
                'code'    => 'P' . $numero,
                'libelle' => $libelle,
                'ordre'   => $i,
            ]);

            $rows = $feuille->toArray(null, true, false, false);
            $debut = $this->ligneEntete($rows) + 1;

            $procedure = null;
            $ordreProc = 0;
            $ordreOp = 0;

            foreach (array_slice($rows, $debut) as $r) {
                $cells = array_map(fn ($c) => trim((string) ($c ?? '')), array_pad(array_slice($r, 0, 8), 8, ''));
                if (count(array_filter($cells)) === 0) {
                    continue; // ligne vide
                }

                // Nouvelle procédure si la 1re colonne est renseignée.
                if ($cells[0] !== '') {
                    $procedure = $processus->procedures()->create(['libelle' => $cells[0], 'ordre' => $ordreProc++]);
                    $ordreOp = 0;
                    $nbProc++;
                }
                if (! $procedure) {
                    continue; // données avant la 1re procédure
                }

                // Une opération = le reste de la ligne.
                if (count(array_filter(array_slice($cells, 1))) === 0) {
                    continue;
                }
                $procedure->operations()->create([
                    'libelle'               => $cells[1] ?: null,
                    'structure_responsable' => $cells[2] ?: null,
                    'fait_generateur'       => $cells[3] ?: null,
                    'taches'                => $cells[4] ?: null,
                    'intervenants'          => $cells[5] ?: null,
                    'resultats'             => $cells[6] ?: null,
                    'delais'                => $cells[7] ?: null,
                    'ordre'                 => $ordreOp++,
                ]);
                $nbOps++;
            }
        }

        $this->info("✓ MPP GRH : {$classeur->getSheetCount()} processus, {$nbProc} procédures, {$nbOps} opérations.");

        return self::SUCCESS;
    }

    /** [numéro, libellé] à partir du nom de feuille « P1_GESTION DES CARRIERES ». */
    private function titre(string $nom, int $defaut): array
    {
        if (preg_match('/^P\s*(\d+)\s*[_\-\s]\s*(.*)$/i', trim($nom), $m)) {
            $libelle = Str::of($m[2])->lower()->trim()->ucfirst()->value();
            return [(int) $m[1], $libelle ?: $nom];
        }
        return [$defaut, $nom];
    }

    /** Indice de la ligne d'en-tête (celle dont la 1re cellule vaut « Procédure »). */
    private function ligneEntete(array $rows): int
    {
        foreach ($rows as $i => $r) {
            if (Str::of((string) ($r[0] ?? ''))->ascii()->lower()->trim()->startsWith('procedure')) {
                return $i;
            }
        }
        return 1; // par défaut : 2e ligne
    }
}
