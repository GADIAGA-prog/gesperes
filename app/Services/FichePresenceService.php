<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Pointage;
use App\Models\Structure;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

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

    /** Fiche B — situation mensuelle d'une structure. */
    public function ficheB(int $structureId, int $mois, int $annee): array
    {
        $structure = Structure::find($structureId);
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = $debut->copy()->endOfMonth();

        $lignes = $this->agregerAbsences(
            Pointage::whereIn('structure_id', Structure::sousArbreIds($structureId))
                ->whereBetween('date_pointage', [$debut->toDateString(), $fin->toDateString()])
                ->where('present', false)
        );

        return ['structure' => $structure, 'mois' => $mois, 'annee' => $annee, 'periode' => [$debut, $fin], 'lignes' => $lignes];
    }

    /** Fiche C — situation trimestrielle de tout le ministère. */
    public function ficheC(int $trimestre, int $annee): array
    {
        $premierMois = ($trimestre - 1) * 3 + 1;
        $debut = Carbon::create($annee, $premierMois, 1)->startOfMonth();
        $fin = $debut->copy()->addMonths(2)->endOfMonth();

        $lignes = $this->agregerAbsences(
            Pointage::whereBetween('date_pointage', [$debut->toDateString(), $fin->toDateString()])
                ->where('present', false)
        );

        return ['trimestre' => $trimestre, 'annee' => $annee, 'periode' => [$debut, $fin], 'lignes' => $lignes];
    }

    /** Agrège les absences par agent sur une période (fiches B et C). */
    private function agregerAbsences(Builder $query): array
    {
        $pointages = $query
            ->with(['agent.emploi', 'agent.fonction', 'agent.structure', 'motifAbsence'])
            ->get()
            ->groupBy('agent_id');

        $lignes = [];
        $n = 1;
        foreach ($pointages as $items) {
            $agent = $items->first()->agent;
            if (! $agent) {
                continue;
            }
            $lignes[] = [
                'n'            => $n++,
                'nom'          => trim($agent->nom . ' ' . $agent->prenoms),
                'matricule'    => $agent->matricule,
                'emploi'       => $agent->emploi?->libelle,
                'fonction'     => $agent->fonction?->libelle,
                'structure'    => $agent->structure?->libelle,
                'total_heures' => $this->num($items->sum('duree_heures')),
                'total_jours'  => $this->num($items->sum('duree_jours')),
                'motifs'       => $items->map(fn ($p) => $p->motifAbsence?->libelle ?: 'Injustifiée')->unique()->implode('; '),
                'mesures'      => $items->pluck('mesure_prise')->filter()->unique()->implode('; '),
                'references'   => $items->pluck('reference_piece')->filter()->unique()->implode('; '),
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
