<?php

namespace App\Services;

use App\Models\ActionFormation;
use App\Models\PlanFormation;
use App\Models\ProgrammeFormation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Logique métier du plan de formation : création d'un plan avec ses programmes
 * annuels, et calcul du suivi d'exécution (prévu vs réalisé) à partir des
 * sessions (table `formations`) rattachées aux actions.
 *
 * Les méthodes de calcul sont pures (sans accès base) pour être testables
 * unitairement (cf. tests/Unit/PlanFormationServiceTest.php).
 */
class PlanFormationService
{
    /** Crée un plan et génère un programme par année de la période couverte. */
    public function creerPlan(array $data, ?int $userId = null): PlanFormation
    {
        return DB::transaction(function () use ($data, $userId) {
            $plan = PlanFormation::create([
                'intitule'    => $data['intitule'],
                'annee_debut' => $data['annee_debut'],
                'annee_fin'   => $data['annee_fin'],
                'vision'      => $data['vision'] ?? null,
                'finalite'    => $data['finalite'] ?? null,
                'objectifs'   => $data['objectifs'] ?? null,
                'statut'      => $data['statut'] ?? 'brouillon',
                'created_by'  => $userId,
            ]);

            for ($annee = (int) $plan->annee_debut; $annee <= (int) $plan->annee_fin; $annee++) {
                ProgrammeFormation::firstOrCreate(
                    ['plan_formation_id' => $plan->id, 'annee' => $annee],
                    ['budget_previsionnel' => 0, 'statut' => 'brouillon']
                );
            }

            return $plan;
        });
    }

    /* ── Calculs purs ──────────────────────────────────────── */

    /** Pourcentage borné [0..100] de `$partie` sur `$total` (0 si total nul). */
    public function pourcentage(float $partie, float $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(min(100, max(0, $partie / $total * 100)), 1);
    }

    /** Taux de réalisation d'agents formés par rapport au prévisionnel (%). */
    public function tauxRealisation(int $agentsPrevus, int $agentsRealises): float
    {
        return $this->pourcentage($agentsRealises, $agentsPrevus);
    }

    /**
     * Écart budgétaire entre coût prévisionnel et coût réel.
     * Retour : ['ecart' => réel - prévu, 'taux' => % du prévu consommé].
     */
    public function ecartBudget(float $previsionnel, float $reel): array
    {
        return [
            'ecart' => round($reel - $previsionnel, 2),
            'taux'  => $this->pourcentage($reel, $previsionnel),
        ];
    }

    /**
     * Totaux d'un ensemble d'actions (coût, jours, agents prévus).
     * Accepte une collection de modèles ActionFormation ou de tableaux.
     */
    public function totauxActions(Collection $actions): array
    {
        return [
            'cout'    => (float) $actions->sum(fn ($a) => (float) data_get($a, 'cout', 0)),
            'jours'   => (int) $actions->sum(fn ($a) => (int) data_get($a, 'nombre_jours', 0)),
            'agents'  => (int) $actions->sum(fn ($a) => (int) data_get($a, 'nombre_agents', 0)),
            'nombre'  => $actions->count(),
        ];
    }

    /* ── Décoration (accès base) ───────────────────────────── */

    /**
     * Enrichit chaque action d'une collection avec les indicateurs de réalisation
     * issus des sessions rattachées : nombre de sessions, agents formés, coût réel
     * et taux de réalisation.
     */
    public function decorerRealisation(Collection $actions): Collection
    {
        $actions->loadMissing(['sessions' => fn ($q) => $q->withCount('agents')]);

        foreach ($actions as $action) {
            $sessions = $action->sessions;
            $action->sessions_count = $sessions->count();
            $action->agents_formes  = (int) $sessions->sum('agents_count');
            $action->cout_reel      = (float) $sessions->sum('cout');
            $action->taux_realisation = $this->tauxRealisation(
                (int) $action->nombre_agents,
                $action->agents_formes
            );
        }

        return $actions;
    }
}
