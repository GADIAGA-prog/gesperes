<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Structure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Rattache chaque agent à une structure (structure_id) depuis « Base des agents »
 * (NIVEAU 1-4), par MATRICULE. On retient la structure du niveau le PLUS PROFOND
 * qui existe au référentiel des structures (NIVEAU 4 → 3 → 2 → 1).
 *
 * Appariement par normalisation sans séparateurs (gère « DRESFPT-Bankui » vs
 * « DR-ESFPT BANKUI »). Couverture observée : 100 %.
 */
class RattacherStructureAgents extends Command
{
    protected $signature = 'agents:rattacher-structure {--dry-run}';

    protected $description = 'Rattache les agents à leur structure (NIVEAU 1-4) depuis « Base des agents ».';

    public function handle(): int
    {
        ini_set('memory_limit', '5120M');

        $fichier = base_path('../pour gesperes/Base des agents.xlsx');
        if (! is_file($fichier)) {
            $this->error("Fichier introuvable : {$fichier}");
            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');

        // Référentiel structures : clé normalisée (sans séparateurs) → id.
        $smap = [];
        foreach (Structure::all() as $s) {
            $smap[$this->sn($s->libelle)] = $s->id;
            $smap[$this->sn($s->code)] = $s->id;
        }

        $agents = Agent::withTrashed()->pluck('id', 'matricule')
            ->mapWithKeys(fn ($id, $m) => [strtoupper(trim((string) $m)) => $id])->all();

        $rows = $this->lignes($fichier);
        $this->info(count($rows) . ' ligne(s) lue(s).');

        $parStructure = [];      // structure_id => [agent_id, …]
        $parService = [];        // service (dernier niveau) => [agent_id, …]
        $niveau = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $stats = ['match' => 0, 'sans_structure' => 0, 'absents' => 0, 'doublons' => 0];
        $seen = [];

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

            $agentId = $agents[$matricule] ?? null;
            if (! $agentId) {
                $stats['absents']++;
                continue;
            }

            $niv = [1 => $r[19] ?? '', 2 => $r[20] ?? '', 3 => $r[21] ?? '', 4 => $r[22] ?? ''];

            // Service = dernier niveau de rattachement (le plus profond non vide).
            $service = '';
            for ($k = 4; $k >= 1; $k--) {
                if (trim((string) $niv[$k]) !== '') {
                    $service = trim((string) $niv[$k]);
                    break;
                }
            }
            if ($service !== '') {
                $parService[$service][] = $agentId;
            }

            $structureId = null;
            for ($k = 4; $k >= 1; $k--) {
                $v = trim((string) $niv[$k]);
                if ($v !== '' && isset($smap[$this->sn($v)])) {
                    $structureId = $smap[$this->sn($v)];
                    $niveau[$k]++;
                    break;
                }
            }

            if ($structureId === null) {
                $stats['sans_structure']++;
                continue;
            }
            $stats['match']++;
            $parStructure[$structureId][] = $agentId;
        }

        if (! $dry) {
            DB::transaction(function () use ($parStructure, $parService) {
                foreach ($parStructure as $structureId => $ids) {
                    foreach (array_chunk($ids, 5000) as $lot) {
                        DB::table('agents')->whereIn('id', $lot)->update(['structure_id' => $structureId]);
                    }
                }
                // Service (établissement / dernier niveau).
                foreach ($parService as $service => $ids) {
                    foreach (array_chunk($ids, 5000) as $lot) {
                        DB::table('agents')->whereIn('id', $lot)->update(['etablissement' => $service]);
                    }
                }
            });
        }

        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Bilan du rattachement :');
        $this->table(['Indicateur', 'Valeur'], [
            ['Agents rattachés', $stats['match']],
            ['  via NIVEAU 1 / 2 / 3 / 4', "{$niveau[1]} / {$niveau[2]} / {$niveau[3]} / {$niveau[4]}"],
            ['Sans structure trouvée', $stats['sans_structure']],
            ['Matricules absents de la base', $stats['absents']],
            ['Doublons (ignorés)', $stats['doublons']],
        ]);
        if ($dry) {
            $this->line('→ Relancez sans --dry-run pour appliquer.');
        }

        return self::SUCCESS;
    }

    private function lignes(string $fichier): array
    {
        $reader = IOFactory::createReaderForFile($fichier);
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell($col, $row, $ws = ''): bool
            {
                return Coordinate::columnIndexFromString($col) <= 23 && $row >= 2;
            }
        });

        return $reader->load($fichier)->getActiveSheet()->toArray(null, true, false, false);
    }

    /** Normalisation sans séparateurs (minuscules, sans accents ni ponctuation). */
    private function sn($s): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::of((string) $s)->ascii()->lower()->value());
    }
}
