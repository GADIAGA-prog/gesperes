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
    public function __construct(
        private GrilleIndiciaireService $grille,
        private IndemniteService $indemnites,
    ) {}

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

        // Indemnités barème (décret 2014-427) calculées depuis les règles.
        // Logement et technicité sont toujours calculables ; astreinte/spécifique
        // dépendent de la zone — à défaut (décentralisé non rattaché), on retombe
        // sur le montant réel attribué à l'agent.
        $resp   = (float) ($agent->fonction?->indemnite_responsabilite ?? 0);
        // Allocation familiale : montant attribué s'il existe, sinon calcul auto
        // (nombre d'enfants) — comme la responsabilité, sans dépendre d'un « figer ».
        $alloc  = $m('ALLOC') ?: $this->indemnites->allocationFamiliale($agent);
        $log    = $this->indemnites->logement($agent);
        $tech   = $this->indemnites->technicite($agent);
        $astr   = $this->indemnites->astreinte($agent) ?? $m('ASTR');
        $spec   = $this->indemnites->specifique($agent) ?? $m('SPEC');
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
