<?php

namespace App\Console\Commands;

use App\Models\Structure;
use App\Models\TypeEnseignement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Renseigne le type d'enseignement des agents : GÉNÉRAL par défaut, TECHNIQUE
 * pour le sous-arbre de la DGEFTP (Direction Générale de l'Enseignement et de
 * la Formation Technique et Professionnelle). Règle provisoire validée.
 */
class DeriverTypeEnseignement extends Command
{
    protected $signature = 'agents:deriver-type-enseignement {--dry-run}';

    protected $description = 'Type d\'enseignement : GÉNÉRAL partout, TECHNIQUE pour le sous-arbre DGEFTP.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $gen = TypeEnseignement::where('code', 'GEN')->value('id');
        $tech = TypeEnseignement::where('code', 'TECH')->value('id');
        if (! $gen || ! $tech) {
            $this->error('Types GEN/TECH absents du référentiel.');
            return self::FAILURE;
        }

        $dgeftp = Structure::where('code', 'N2-DGEFTP')->first();
        if (! $dgeftp) {
            $this->warn('Structure N2-DGEFTP introuvable — tout sera GÉNÉRAL.');
        }

        // Sous-arbre DGEFTP (descendants récursifs).
        $structures = Structure::all(['id', 'parent_id'])->groupBy('parent_id');
        $technique = [];
        if ($dgeftp) {
            $pile = [$dgeftp->id];
            while ($pile) {
                $id = array_pop($pile);
                $technique[$id] = true;
                foreach ($structures->get($id, []) as $enfant) {
                    $pile[] = $enfant->id;
                }
            }
        }
        $idsTech = array_keys($technique);

        if (! $dry) {
            DB::transaction(function () use ($gen, $tech, $idsTech) {
                // Tout agent rattaché → GÉNÉRAL …
                DB::table('agents')->whereNotNull('structure_id')->update(['type_enseignement_id' => $gen]);
                // … puis le sous-arbre DGEFTP → TECHNIQUE.
                if ($idsTech) {
                    DB::table('agents')->whereIn('structure_id', $idsTech)->update(['type_enseignement_id' => $tech]);
                }
            });
        }

        $nTech = $idsTech ? \App\Models\Agent::whereIn('structure_id', $idsTech)->count() : 0;
        $nGen = \App\Models\Agent::whereNotNull('structure_id')->count() - $nTech;
        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ Général : {$nGen} · Technique (DGEFTP) : {$nTech} (" . count($idsTech) . ' structure(s)).');

        return self::SUCCESS;
    }
}
