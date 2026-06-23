<?php

namespace App\Console\Commands;

use App\Models\Structure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Propage l'action budgétaire dans l'arborescence : toute structure sans
 * action_id hérite de l'action de l'ancêtre le plus proche qui en possède une.
 */
class HeriterActionStructures extends Command
{
    protected $signature = 'structures:heriter-action {--dry-run}';

    protected $description = 'Affecte aux structures sans action celle de leur parent (remontée hiérarchique).';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $structures = Structure::all(['id', 'parent_id', 'action_id'])->keyBy('id');

        $resoudre = function (int $id) use (&$resoudre, $structures): ?int {
            $s = $structures->get($id);
            if (! $s) {
                return null;
            }
            if ($s->action_id) {
                return $s->action_id;
            }
            return $s->parent_id ? $resoudre($s->parent_id) : null;
        };

        $maj = 0;
        $maintenant = now();
        DB::transaction(function () use ($structures, $resoudre, $dry, &$maj, $maintenant) {
            foreach ($structures as $s) {
                if ($s->action_id) {
                    continue;
                }
                $herite = $s->parent_id ? $resoudre($s->parent_id) : null;
                if ($herite) {
                    $maj++;
                    if (! $dry) {
                        DB::table('structures')->where('id', $s->id)
                            ->update(['action_id' => $herite, 'updated_at' => $maintenant]);
                    }
                }
            }
        });

        $couvertes = $dry
            ? Structure::whereNotNull('action_id')->count() + $maj
            : Structure::whereNotNull('action_id')->count();

        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ {$maj} structure(s) ont hérité d'une action ; {$couvertes}/" . Structure::count() . ' couverte(s).');

        return self::SUCCESS;
    }
}
