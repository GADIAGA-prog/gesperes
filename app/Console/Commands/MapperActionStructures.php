<?php

namespace App\Console\Commands;

use App\Models\Action;
use App\Models\Structure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Affecte l'action budgétaire aux structures, à partir du fichier Répartition
 * (chapitre → action) et du PAT :
 *   - Siège (directions centrales) : mapping explicite par code.
 *   - Directions régionales d'éducation (DR-ESFPT) : action 16301 par défaut
 *     (Accès à l'enseignement général post-primaire et secondaire, programme 163).
 * Puis l'action est propagée aux structures filles (provinces, établissements)
 * via structures:heriter-action.
 */
class MapperActionStructures extends Command
{
    protected $signature = 'structures:mapper-action {--dry-run}';

    protected $description = 'Affecte l\'action budgétaire aux structures (siège par code, régions = 16301), puis cascade.';

    /** Siège : code structure → code action (source : Répartition des dépenses de personnel). */
    private const SIEGE = [
        'N1-1'     => '10401', // Secrétariat général → Pilotage et coordination
        'N2-DRH'   => '10403', // GRH
        'N2-DGF'   => '10404', // Ressources financières et matérielles
        'N2-DGESS' => '10402', // Planification, suivi-évaluation, statistiques
        'N2-DAD'   => '10405', // Systèmes d'information et archivage
        'N2-DCRP'  => '10406', // Communication
        'N2-DGEFTP' => '10204', // EFTP — partenariat
        'N2-DGEG'  => '16303', // Enseignement général — partenariat
    ];

    /** Action par défaut des directions régionales d'éducation. */
    private const ACTION_REGIONALE = '16301';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $actions = Action::pluck('id', 'code');
        foreach (array_merge(array_values(self::SIEGE), [self::ACTION_REGIONALE]) as $code) {
            if (! isset($actions[$code])) {
                $this->warn("⚠ Action {$code} absente du référentiel.");
            }
        }

        $siege = 0;
        $regional = 0;
        DB::transaction(function () use ($actions, $dry, &$siege, &$regional) {
            foreach (Structure::all() as $s) {
                $code = null;

                if (isset(self::SIEGE[$s->code])) {
                    $code = self::SIEGE[$s->code];
                    $siege++;
                } elseif ($this->estRegionaleEducation($s->libelle)) {
                    $code = self::ACTION_REGIONALE;
                    $regional++;
                }

                if ($code && isset($actions[$code]) && ! $dry) {
                    DB::table('structures')->where('id', $s->id)->update(['action_id' => $actions[$code]]);
                }
            }
        });

        $this->info(($dry ? '[DRY-RUN] ' : '') . "✓ Siège : {$siege} structure(s) ; régionales d'éducation : {$regional}.");

        if (! $dry) {
            $this->call('structures:heriter-action');
        }

        return self::SUCCESS;
    }

    /** Direction régionale d'éducation (DR-ESFPT / DREFTP / DRESFPT…). */
    private function estRegionaleEducation(string $libelle): bool
    {
        $n = preg_replace('/[^a-z0-9]/', '', Str::of($libelle)->ascii()->lower()->value());
        return str_contains($n, 'dresfpt') || str_contains($n, 'dreftp') || str_contains($n, 'dresftp');
    }
}
