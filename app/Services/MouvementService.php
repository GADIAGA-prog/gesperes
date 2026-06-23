<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Mouvement;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre un mouvement du personnel : historise le changement de position
 * administrative et met à jour la situation courante de l'agent. La famille de la
 * position cible (Activité / Sortie temporaire / Sortie définitive) détermine
 * automatiquement si l'agent reste compté dans l'effectif actif (cf. Agent::est_actif).
 */
class MouvementService
{
    public function enregistrer(Agent $agent, array $data, ?int $userId = null): Mouvement
    {
        return DB::transaction(function () use ($agent, $data, $userId) {
            $mouvement = Mouvement::create([
                'agent_id'             => $agent->id,
                'ancienne_position_id' => $agent->position_administrative_id,
                'nouvelle_position_id' => $data['nouvelle_position_id'],
                'date_effet'           => $data['date_effet'],
                'date_fin'             => $data['date_fin'] ?? null,
                'reference_acte'       => $data['reference_acte'] ?? null,
                'motif'                => $data['motif'] ?? null,
                'created_by'           => $userId,
            ]);

            // Met à jour la position courante de l'agent (pilote l'effectif actif).
            $agent->update(['position_administrative_id' => $data['nouvelle_position_id']]);

            return $mouvement;
        });
    }

    /**
     * Décore une page d'agents actuellement en sortie temporaire avec les champs
     * calculés de la situation : nature, date de sortie, durée, référence de
     * l'acte et alerte (fin prévue proche ou dépassée).
     *
     * La liste est bâtie sur la position administrative COURANTE de l'agent : un
     * agent réintégré (retour en activité) quitte automatiquement cette liste et
     * ne peut donc jamais figurer en même temps en activité et en sortie.
     */
    public function decorerSortiesTemporaires(LengthAwarePaginator $page): LengthAwarePaginator
    {
        $seuilAlerte = Carbon::now()->addMonths(
            (int) config('gesperes.mouvements.alerte_sortie_mois_avant', 2)
        );

        foreach ($page->items() as $agent) {
            $m = $agent->dernierMouvement;

            $agent->nature_sortie  = $agent->positionAdministrative?->libelle;
            $agent->date_sortie    = $m?->date_effet;
            $agent->date_fin       = $m?->date_fin;
            $agent->reference_acte = $m?->reference_acte;

            $agent->duree_libelle = ($m && $m->date_fin)
                ? $this->dureeLisible($m->date_effet, $m->date_fin)
                : 'Indéterminée';

            // Reprise : un agent en sortie est par définition non repris (sinon il
            // serait repassé en activité et aurait quitté cette liste).
            $agent->date_reprise = null;

            $agent->en_alerte = $m && $m->date_fin
                && $m->date_fin->lessThanOrEqualTo($seuilAlerte);
        }

        return $page;
    }

    /**
     * Décore une page d'agents en sortie définitive (retraite, décès, démission,
     * licenciement…). Pas de date de reprise. L'alerte signale les agents partis
     * à la retraite durant l'année civile en cours.
     */
    public function decorerSortiesDefinitives(LengthAwarePaginator $page): LengthAwarePaginator
    {
        $anneeCourante = (int) Carbon::now()->year;

        foreach ($page->items() as $agent) {
            $m = $agent->dernierMouvement;

            $agent->nature_sortie  = $agent->positionAdministrative?->libelle;
            $agent->date_sortie    = $m?->date_effet;
            $agent->reference_acte = $m?->reference_acte;

            // Retraite de l'année en cours → mise en évidence.
            $agent->en_alerte = $agent->positionAdministrative?->code === 'RETR'
                && $m?->date_effet
                && (int) $m->date_effet->year === $anneeCourante;
        }

        return $page;
    }

    /** Durée lisible en français entre deux dates (ex : « 1 an 6 mois »). */
    private function dureeLisible(CarbonInterface $debut, CarbonInterface $fin): string
    {
        $diff = $debut->diff($fin);

        $parts = [];
        if ($diff->y) {
            $parts[] = $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        }
        if ($diff->m) {
            $parts[] = $diff->m . ' mois';
        }
        if (! $diff->y && ! $diff->m) {
            $parts[] = $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        }

        return implode(' ', $parts) ?: '0 jour';
    }
}
