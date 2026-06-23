<?php

namespace App\Services;

use App\Models\Agent;

/**
 * Construit la ligne de paie d'un agent pour l'état « Dépenses du personnel » :
 * éléments calculés depuis la grille (solde, résidence, CARFO) et montants réels
 * d'indemnités attribués à l'agent (RESP/ALLOC/LOG/ASTR/SPEC/TECH/AUTRES).
 *
 * L'agent doit être chargé avec : indice + indemnites.indemnite.
 */
class PaiePersonnelService
{
    public function __construct(private GrilleIndiciaireService $grille) {}

    public function ligne(Agent $agent): array
    {
        $indice = $agent->indice?->valeur;

        $ind = $agent->relationLoaded('indemnites')
            ? $agent->indemnites->keyBy(fn ($x) => $x->indemnite?->code)
            : collect();
        $m = fn (string $code) => (float) optional($ind->get($code))->montant;

        $solde     = $indice ? $this->grille->soldeIndiciaire($indice) : 0.0;
        $residence = $indice ? round($this->grille->residence($indice)) : 0.0;
        $carfo     = $indice ? $this->grille->carfo($indice) : 0.0;

        // Indemnité de fonction : pilotée par la fonction de l'agent.
        $resp   = (float) ($agent->fonction?->indemnite_responsabilite ?? 0);
        $alloc  = $m('ALLOC');
        $log    = $m('LOG');
        $astr   = $m('ASTR');
        $spec   = $m('SPEC');
        $tech   = $m('TECH');
        $autres = $m('AUTRES');

        // Total mensuel = solde + résidence + indemnités (émoluments).
        // La CARFO (retenue agent) est affichée à part, hors total.
        $totalMois = $solde + $residence + $resp + $alloc + $log + $astr + $spec + $tech + $autres;

        return [
            'indice'       => $indice,
            'solde'        => $solde,
            'residence'    => $residence,
            'responsabilite' => $resp,
            'allocation'   => $alloc,
            'logement'     => $log,
            'astreinte'    => $astr,
            'specifique'   => $spec,
            'technicite'   => $tech,
            'autres'       => $autres,
            'carfo'        => $carfo,
            'total_mois'   => $totalMois,
            'total_annuel' => $totalMois * 12,
        ];
    }
}
