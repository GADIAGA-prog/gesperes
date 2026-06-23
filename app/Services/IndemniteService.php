<?php

namespace App\Services;

use App\Enums\LieuExercice;
use App\Enums\ModeIndemnite;
use App\Models\Agent;
use App\Models\BaremeAstreinte;
use App\Models\BaremeLogement;
use App\Models\BaremeSpecifique;
use App\Models\BaremeTechnicite;
use App\Models\Indemnite;

/**
 * Moteur de calcul des indemnités à partir du référentiel PARAMÉTRABLE et des
 * barèmes du décret 2014-427 (données GESPER). Aucun taux n'est codé en dur.
 */
class IndemniteService
{
    /** Montant mensuel d'une indemnité pour un agent donné. */
    public function calculer(Agent $agent, Indemnite $indemnite): float
    {
        return match ($indemnite->mode) {
            ModeIndemnite::MONTANT_FIXE => (float) $indemnite->valeur,
            ModeIndemnite::POURCENTAGE  => round((float) $indemnite->valeur / 100 * $this->base($agent), 2),
            ModeIndemnite::BAREME       => $this->bareme($agent, $indemnite),
        };
    }

    /** Calcule toutes les indemnités actives applicables à un agent. */
    public function pourAgent(Agent $agent): array
    {
        return Indemnite::where('actif', true)->orderBy('libelle')->get()
            ->map(fn (Indemnite $i) => [
                'indemnite' => $i,
                'montant'   => $this->calculer($agent, $i),
            ])
            ->all();
    }

    /** Résolution d'un barème selon les caractéristiques de l'agent. */
    private function bareme(Agent $agent, Indemnite $indemnite): float
    {
        $zone = $agent->localite?->zone?->code;
        $estEnseignant = (bool) $agent->emploi?->enseignant;
        $enClasse = $agent->lieu_exercice === LieuExercice::EN_CLASSE;

        $montant = match ($indemnite->bareme) {
            'astreinte' => $agent->emploi && $zone
                ? BaremeAstreinte::where('emploi_code', $agent->emploi->code)->where('zone_code', $zone)->value('montant')
                : null,
            'specifique' => $agent->emploi && $zone
                ? BaremeSpecifique::where('emploi_code', $agent->emploi->code)->where('zone_code', $zone)->value('montant')
                : null,
            'logement' => $agent->categorie
                ? BaremeLogement::where('categorie_code', $agent->categorie->code)
                    ->where('enseignant', $estEnseignant)->where('en_classe', $enClasse)->value('montant')
                : null,
            'technicite' => $agent->echelle
                ? BaremeTechnicite::where('echelle_code', $agent->echelle->code)->value('montant')
                : null,
            default => null,
        };

        return (float) ($montant ?? 0);
    }

    /** Base de calcul pour les indemnités proportionnelles : le salaire indiciaire. */
    private function base(Agent $agent): float
    {
        return (float) ($agent->indice?->salaire_indiciaire ?? 0);
    }
}
