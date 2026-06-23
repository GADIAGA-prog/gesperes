<?php

namespace App\Services;

use App\Enums\CategorieAbsence;
use App\Enums\StatutConge;
use App\Models\Agent;
use App\Models\Conge;
use App\Models\Pointage;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;

/**
 * Calcule les droits et soldes congés/absences d'un agent pour une année civile.
 *
 * Règles de gestion :
 *  - Droit congé annuel        : 30 jours
 *  - Droit autorisations       : 10 jours
 *  - Les autorisations d'absence sont imputées sur les 10 jours ; tout dépassement
 *    est déduit du congé annuel (30 jours).
 *  - Seules les demandes VALIDÉES sont imputées.
 */
class SoldeCongeService
{
    public const DROIT_CONGE_ANNUEL = 30;
    public const DROIT_AUTORISATION = 10;

    /**
     * Retourne la situation chiffrée de l'agent pour l'année donnée.
     *
     * @return array{
     *   annee:int, droit_conge:int, droit_autorisation:int,
     *   autorisation_consommee:int, depassement_autorisation:int,
     *   conge_direct:int, conge_consomme:int,
     *   solde_conge:int, solde_autorisation:int,
     *   jours_injustifies:float, jours_justifies:float
     * }
     */
    public function pour(Agent|int $agent, ?int $annee = null): array
    {
        $agentId = $agent instanceof Agent ? $agent->id : $agent;
        $annee ??= (int) now()->year;

        $conges = Conge::query()
            ->where('agent_id', $agentId)
            ->where('statut', StatutConge::VALIDE->value)
            ->whereYear('date_debut', $annee)
            ->with('motifAbsence')
            ->get();

        $autorisationConsommee = (int) $conges
            ->filter(fn (Conge $c) => $c->motifAbsence?->categorie === CategorieAbsence::AUTORISATION)
            ->sum('nombre_jours');

        $congeDirect = (int) $conges
            ->filter(fn (Conge $c) => $c->motifAbsence?->categorie === CategorieAbsence::CONGE)
            ->sum('nombre_jours');

        // Dépassement du quota d'autorisations → reporté sur le congé annuel.
        $depassement = max(0, $autorisationConsommee - self::DROIT_AUTORISATION);
        $congeConsomme = $congeDirect + $depassement;

        // Absences relevées au pointage (Fiche A) sur l'année.
        $pointages = Pointage::query()
            ->where('agent_id', $agentId)
            ->where('present', false)
            ->whereYear('date_pointage', $annee)
            ->with('motifAbsence')
            ->get();

        $joursInjustifies = (float) $pointages
            ->filter(fn (Pointage $p) => $p->motif_absence_id === null
                || $p->motifAbsence?->categorie === CategorieAbsence::INJUSTIFIEE)
            ->sum('duree_jours');

        $joursJustifies = (float) $pointages
            ->filter(fn (Pointage $p) => $p->motifAbsence?->categorie === CategorieAbsence::JUSTIFIEE)
            ->sum('duree_jours');

        return [
            'annee'                    => $annee,
            'droit_conge'              => self::DROIT_CONGE_ANNUEL,
            'droit_autorisation'       => self::DROIT_AUTORISATION,
            'autorisation_consommee'   => $autorisationConsommee,
            'depassement_autorisation' => $depassement,
            'conge_direct'             => $congeDirect,
            'conge_consomme'           => $congeConsomme,
            'solde_conge'              => self::DROIT_CONGE_ANNUEL - $congeConsomme,
            'solde_autorisation'       => max(0, self::DROIT_AUTORISATION - $autorisationConsommee),
            'jours_injustifies'        => $joursInjustifies,
            'jours_justifies'          => $joursJustifies,
        ];
    }

    /**
     * Nombre de jours ouvrés (lundi→vendredi) entre deux dates incluses.
     * Utilitaire pour pré-remplir le nombre de jours d'une demande.
     */
    public static function joursOuvres(CarbonInterface $debut, CarbonInterface $fin): int
    {
        if ($fin->lessThan($debut)) {
            return 0;
        }

        $jours = 0;
        foreach (CarbonPeriod::create($debut->copy()->startOfDay(), $fin->copy()->startOfDay()) as $jour) {
            if (! $jour->isWeekend()) {
                $jours++;
            }
        }

        return $jours;
    }
}
