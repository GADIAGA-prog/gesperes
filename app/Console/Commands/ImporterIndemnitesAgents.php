<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Indemnite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Importe les montants d'indemnités par agent depuis « base liehoun »
 * (rapprochement par MATRICULE) dans agent_indemnites :
 *   RESP (responsabilité), ASTR (astreinte), LOG (logement), TECH (technicité),
 *   AUTRES, ALLOC (allocation familiale).
 *
 * Les entrées de catalogue manquantes sont créées. Idempotent : les attributions
 * de ces 6 indemnités sont remplacées à chaque exécution (insertion en masse).
 */
class ImporterIndemnitesAgents extends Command
{
    protected $signature = 'agents:importer-indemnites {--dry-run : Analyse sans aucune écriture}';

    protected $description = 'Importe les montants d\'indemnités par agent depuis « base liehoun » (par matricule).';

    /** [code, libellé, index de colonne liehoun (0-based)] */
    private const DEFS = [
        ['RESP', 'Indemnité de responsabilité', 28],
        ['ASTR', 'Indemnité d\'astreinte', 29],
        ['LOG', 'Indemnité de logement', 30],
        ['TECH', 'Indemnité de technicité', 31],
        // Dans « base liehoun », la colonne AUTRES (32) porte le SPÉCIFIQUE.
        ['SPEC', 'Indemnité spécifique harmonisée', 32],
        ['ALLOC', 'Allocation familiale', 33],
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '5120M');

        $fichier = (string) config('gesperes.gesper_liehoun_path');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');

        $indemnites = $this->catalogue();
        $agents = Agent::withTrashed()->pluck('id', 'matricule')
            ->mapWithKeys(fn ($id, $m) => [strtoupper(trim((string) $m)) => $id])->all();

        $rows = $this->lignes($fichier);
        $this->info(count($rows) . ' ligne(s) lue(s).');

        $seen = [];
        $aInserer = [];
        $stats = ['agents' => 0, 'absents' => 0, 'doublons' => 0];
        $parCode = array_fill_keys(array_column(self::DEFS, 0), 0);
        $maintenant = now();

        foreach ($rows as $r) {
            $matricule = strtoupper(trim((string) ($r[2] ?? '')));
            if ($matricule === '' || $matricule === 'MATRICULE') {
                continue;
            }
            if (isset($seen[$matricule])) {
                $stats['doublons']++;
                continue;
            }
            $seen[$matricule] = true;

            $agentId = $agents[$matricule] ?? null;
            if (! $agentId) {
                $stats['absents']++;
                continue;
            }
            $stats['agents']++;

            foreach (self::DEFS as [$code, , $col]) {
                $montant = $this->montant($r[$col] ?? null);
                if ($montant === null) {
                    continue;
                }
                $parCode[$code]++;
                $aInserer[] = [
                    'agent_id'     => $agentId,
                    'indemnite_id' => $indemnites[$code],
                    'montant'      => $montant,
                    'actif'        => true,
                    'created_at'   => $maintenant,
                    'updated_at'   => $maintenant,
                ];
            }
        }

        if (! $dry) {
            DB::transaction(function () use ($indemnites, $aInserer) {
                AgentIndemniteHelper::remplacer(array_values($indemnites), $aInserer);
            });
        }

        $this->bilan($dry, $stats, $parCode, count($aInserer));

        return self::SUCCESS;
    }

    /** Garantit les 6 indemnités au catalogue ; renvoie [code => id]. */
    private function catalogue(): array
    {
        $ids = [];
        foreach (self::DEFS as [$code, $libelle]) {
            $ids[$code] = Indemnite::firstOrCreate(
                ['code' => $code],
                ['libelle' => $libelle, 'mode' => 'montant_fixe', 'valeur' => 0,
                 'reference_texte' => 'base liehoun', 'actif' => true]
            )->id;
        }
        return $ids;
    }

    private function lignes(string $fichier): array
    {
        $reader = IOFactory::createReaderForFile($fichier);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell($col, $row, $ws = ''): bool
            {
                return Coordinate::columnIndexFromString($col) <= 34 && $row >= 2;
            }
        });

        return $reader->load($fichier)->getActiveSheet()->toArray(null, true, false, false);
    }

    private function bilan(bool $dry, array $stats, array $parCode, int $total): void
    {
        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Bilan de l\'import des indemnités :');
        $lignes = [
            ['Agents traités', $stats['agents']],
            ['Matricules absents de la base', $stats['absents']],
            ['Doublons de matricule (ignorés)', $stats['doublons']],
            ['— Attributions à créer —', $total],
        ];
        foreach ($parCode as $code => $n) {
            $lignes[] = ["  {$code}", $n];
        }
        $this->table(['Indicateur', 'Valeur'], $lignes);
        if ($dry) {
            $this->line('→ Relancez sans --dry-run pour appliquer.');
        }
    }

    /** Montant numérique strictement positif, sinon null. */
    private function montant($v): ?float
    {
        return (is_numeric($v) && (float) $v > 0) ? (float) $v : null;
    }
}

/**
 * Remplace en masse les attributions des indemnités concernées.
 */
class AgentIndemniteHelper
{
    public static function remplacer(array $indemniteIds, array $aInserer): void
    {
        DB::table('agent_indemnites')->whereIn('indemnite_id', $indemniteIds)->delete();
        foreach (array_chunk($aInserer, 2000) as $lot) {
            DB::table('agent_indemnites')->insert($lot);
        }
    }
}
