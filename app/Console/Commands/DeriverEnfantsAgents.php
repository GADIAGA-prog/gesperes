<?php

namespace App\Console\Commands;

use App\Models\AgentIndemnite;
use App\Models\Indemnite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Déduit le nombre d'enfants de chaque agent à partir de l'allocation familiale
 * (ALLOC de liehoun) : allocation = nombre_enfants × montant_par_enfant (2000),
 * plafonné à nombre_max_enfants. Donc nombre_enfants = round(ALLOC / 2000).
 */
class DeriverEnfantsAgents extends Command
{
    protected $signature = 'agents:deriver-enfants {--dry-run}';

    protected $description = 'Déduit agent.nombre_enfants depuis l\'allocation familiale (ALLOC / montant_par_enfant).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $parEnfant = (int) config('gesperes.allocation_familiale.montant_par_enfant', 2000);
        $max = (int) config('gesperes.allocation_familiale.nombre_max_enfants', 6);
        if ($parEnfant <= 0) {
            $this->error('montant_par_enfant invalide.');
            return self::FAILURE;
        }

        $allocId = Indemnite::where('code', 'ALLOC')->value('id');
        if (! $allocId) {
            $this->error('Indemnité ALLOC absente (lancez agents:importer-indemnites).');
            return self::FAILURE;
        }

        // agent_id → nombre d'enfants déduit, regroupé par valeur (mise à jour en lot).
        $parNb = [];
        AgentIndemnite::where('indemnite_id', $allocId)->select('agent_id', 'montant')
            ->orderBy('agent_id')->chunk(5000, function ($lignes) use (&$parNb, $parEnfant, $max) {
                foreach ($lignes as $l) {
                    $nb = (int) min($max, max(0, round($l->montant / $parEnfant)));
                    if ($nb > 0) {
                        $parNb[$nb][] = $l->agent_id;
                    }
                }
            });

        $total = 0;
        foreach ($parNb as $nb => $ids) {
            $total += count($ids);
            if (! $dry) {
                foreach (array_chunk($ids, 5000) as $lot) {
                    DB::table('agents')->whereIn('id', $lot)->update(['nombre_enfants' => $nb]);
                }
            }
        }

        ksort($parNb);
        foreach ($parNb as $nb => $ids) {
            $this->line("  {$nb} enfant(s) : " . count($ids) . ' agent(s)');
        }
        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ {$total} agent(s) renseigné(s).");

        return self::SUCCESS;
    }
}
