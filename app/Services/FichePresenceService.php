<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Pointage;
use App\Models\Structure;
use Carbon\Carbon;

/**
 * Construit les données des fiches officielles de présence :
 *  - Fiche A : situation journalière par structure.
 *  - Fiche B : situation mensuelle par structure (absences agrégées).
 *  - Fiche C : situation trimestrielle de tout le ministère.
 */
class FichePresenceService
{
    /** Fiche A — situation journalière des agents d'une structure. */
    public function ficheA(int $structureId, string $date): array
    {
        $structure = Structure::find($structureId);

        // Cascade : la structure ET tous ses services/sous-structures (cohérent
        // avec l'écran de pointage).
        $sousArbre = Structure::sousArbreIds($structureId);

        $agents = Agent::whereIn('structure_id', $sousArbre)
            ->with(['emploi', 'fonction'])
            ->orderBy('nom')->orderBy('prenoms')
            ->get();

        $pointages = Pointage::whereIn('structure_id', $sousArbre)
            ->whereDate('date_pointage', $date)
            ->get()->keyBy('agent_id');

        $lignes = [];
        foreach ($agents as $i => $agent) {
            $p = $pointages->get($agent->id);
            $absent = $p ? ! $p->present : false;
            $lignes[] = [
                'n'            => $i + 1,
                'nom'          => trim($agent->nom . ' ' . $agent->prenoms),
                'matricule'    => $agent->matricule,
                'emploi'       => $agent->emploi?->libelle,
                'fonction'     => $agent->fonction?->libelle,
                'pointe'       => (bool) $p,
                'present'      => $p ? $p->present : null,
                'absent'       => $absent,
                'duree_heures' => $absent ? $this->num($p->duree_heures) : null,
                'duree_jours'  => $absent ? $this->num($p->duree_jours) : null,
            ];
        }

        return ['structure' => $structure, 'date' => Carbon::parse($date), 'lignes' => $lignes];
    }

    /** Fiche B — situation mensuelle d'une structure (effectif complet). */
    public function ficheB(int $structureId, int $mois, int $annee): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = $debut->copy()->endOfMonth();

        return [
            'structure' => Structure::find($structureId),
            'mois'      => $mois,
            'annee'     => $annee,
            'periode'   => [$debut, $fin],
            'lignes'    => $this->recapitulatif($structureId, $debut, $fin),
        ];
    }

    /** Fiche C — situation mensuelle de tout le ministère (structure non précisée). */
    public function ficheC(int $mois, int $annee): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = $debut->copy()->endOfMonth();

        return [
            'mois'    => $mois,
            'annee'   => $annee,
            'periode' => [$debut, $fin],
            'lignes'  => $this->recapitulatif(null, $debut, $fin),
        ];
    }

    /** Fiche D — situation trimestrielle (une structure, ou tout le ministère). */
    public function ficheD(?int $structureId, int $trimestre, int $annee): array
    {
        $premierMois = ($trimestre - 1) * 3 + 1;
        $debut = Carbon::create($annee, $premierMois, 1)->startOfMonth();
        $fin = $debut->copy()->addMonths(2)->endOfMonth();

        return [
            'structure' => $structureId ? Structure::find($structureId) : null,
            'trimestre' => $trimestre,
            'annee'     => $annee,
            'periode'   => [$debut, $fin],
            'lignes'    => $this->recapitulatif($structureId, $debut, $fin),
        ];
    }

    /**
     * Récapitulatif de présence sur une période : TOUT l'effectif (la structure
     * et ses services en cascade, ou le ministère entier si null), avec la somme
     * des absences en heures et en jours. Un agent sans absence affiche 0 / 0.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recapitulatif(?int $structureId, Carbon $debut, Carbon $fin): array
    {
        ini_set('memory_limit', '2048M');

        $sousArbre = $structureId ? Structure::sousArbreIds($structureId) : null;

        // Absences (present = false) sur la période, regroupées par agent.
        $absences = Pointage::query()
            ->where('present', false)
            ->whereBetween('date_pointage', [$debut->toDateString(), $fin->toDateString()])
            ->when($sousArbre, fn ($q) => $q->whereIn('structure_id', $sousArbre))
            ->with('motifAbsence')
            ->get()
            ->groupBy('agent_id');

        // Effectif complet du périmètre (chaque agent figure, même sans absence).
        $agents = Agent::query()
            ->when($sousArbre, fn ($q) => $q->whereIn('structure_id', $sousArbre))
            ->with(['emploi:id,libelle', 'fonction:id,libelle', 'structure:id,libelle'])
            ->orderBy('nom')->orderBy('prenoms')
            ->get(['id', 'matricule', 'nom', 'prenoms', 'emploi_id', 'fonction_id', 'structure_id']);

        $lignes = [];
        $n = 1;
        foreach ($agents as $agent) {
            $items = $absences->get($agent->id); // Illuminate\Support\Collection|null

            $lignes[] = [
                'n'            => $n++,
                'nom'          => trim($agent->nom . ' ' . $agent->prenoms),
                'matricule'    => $agent->matricule,
                'emploi'       => $agent->emploi?->libelle,
                'fonction'     => $agent->fonction?->libelle,
                'structure'    => $agent->structure?->libelle,
                'total_heures' => $this->num($items?->sum('duree_heures') ?? 0),
                'total_jours'  => $this->num($items?->sum('duree_jours') ?? 0),
                'motifs'       => $items ? $items->map(fn ($p) => $p->motifAbsence?->libelle ?: 'Injustifiée')->unique()->implode('; ') : '',
                'mesures'      => $items ? $items->pluck('mesure_prise')->filter()->unique()->implode('; ') : '',
                'references'   => $items ? $items->pluck('reference_piece')->filter()->unique()->implode('; ') : '',
            ];
        }

        return $lignes;
    }

    /** Affiche un nombre sans décimales superflues (3.00 → 3, 1.50 → 1.5). */
    private function num($valeur): string
    {
        return rtrim(rtrim(number_format((float) $valeur, 2, '.', ''), '0'), '.') ?: '0';
    }
}
