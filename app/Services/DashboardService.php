<?php

namespace App\Services;

use App\Enums\CategoriePosition;
use App\Enums\Sexe;
use App\Models\Agent;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

/**
 * Agrège les indicateurs du tableau de bord.
 * Les requêtes restent volontairement simples et indexées.
 */
class DashboardService
{
    public function __construct(private RetraiteService $retraite) {}

    public function cartes(): array
    {
        $total = Agent::count();
        $hommes = Agent::where('sexe', Sexe::M->value)->count();
        $femmes = Agent::where('sexe', Sexe::F->value)->count();

        $sortieTemp = $this->parFamillePosition(CategoriePosition::SORTIE_TEMPORAIRE);
        $sortieDef = $this->parFamillePosition(CategoriePosition::SORTIE_DEFINITIVE);
        $actifs = $total - $sortieTemp - $sortieDef;

        return [
            'effectif_total'    => $total,
            'hommes'            => $hommes,
            'femmes'            => $femmes,
            'actifs'            => max(0, $actifs),
            'sorties_temp'      => $sortieTemp,
            'sorties_def'       => $sortieDef,
            'proches_retraite'  => $this->prochesRetraite(),
            'dossiers_incomplets' => Agent::whereIn('statut_dossier', ['brouillon', 'incomplet'])->count(),
            'documents_expires' => Document::whereNotNull('date_expiration')
                                    ->whereDate('date_expiration', '<', now())->count(),
        ];
    }

    public function effectifParSexe(): array
    {
        return [
            'Masculin' => Agent::where('sexe', Sexe::M->value)->count(),
            'Féminin'  => Agent::where('sexe', Sexe::F->value)->count(),
        ];
    }

    public function effectifParRegion(): array
    {
        return Agent::select('region', DB::raw('count(*) as total'))
            ->whereNotNull('region')
            ->groupBy('region')
            ->orderByDesc('total')
            ->pluck('total', 'region')
            ->all();
    }

    public function effectifParEmploi(int $limite = 10): array
    {
        return Agent::join('emplois', 'agents.emploi_id', '=', 'emplois.id')
            ->select('emplois.libelle', DB::raw('count(*) as total'))
            ->groupBy('emplois.libelle')
            ->orderByDesc('total')
            ->limit($limite)
            ->pluck('total', 'libelle')
            ->all();
    }

    public function effectifParCategorie(): array
    {
        return Agent::join('categories', 'agents.categorie_id', '=', 'categories.id')
            ->select('categories.code', DB::raw('count(*) as total'))
            ->groupBy('categories.code')
            ->orderBy('categories.code')
            ->pluck('total', 'categories.code')
            ->all();
    }

    /** Masse salariale estimée (traitement indiciaire + indemnités attribuées). */
    public function masseSalariale(): array
    {
        $point = (float) config('grille.point_annuel', 0);
        $mois = (int) config('grille.mois_par_an', 12) ?: 12;

        $sommeIndices = (int) Agent::join('indices', 'agents.indice_id', '=', 'indices.id')
            ->whereNull('agents.deleted_at')->sum('indices.valeur');

        $traitement = $point ? round($sommeIndices * $point / $mois) : 0;
        $indemnites = (float) \App\Models\AgentIndemnite::where('actif', true)->sum('montant');

        return [
            'traitement'   => $traitement,
            'indemnites'   => $indemnites,
            'total'        => $traitement + $indemnites,
            'total_annuel' => ($traitement + $indemnites) * 12,
        ];
    }

    public function departsRetraiteParAnnee(int $annees = 6): array
    {
        $resultat = [];
        $ageDefaut = (int) config('gesperes.retraite.age_defaut', 60);
        for ($i = 0; $i < $annees; $i++) {
            $annee = now()->year + $i;
            $resultat[$annee] = Agent::whereNotNull('date_retraite')
                ->whereYear('date_retraite', $annee)
                ->count();
        }
        return $resultat;
    }

    public function trancheAge(): array
    {
        $tranches = ['< 30' => 0, '30-39' => 0, '40-49' => 0, '50-59' => 0, '60+' => 0];
        Agent::whereNotNull('date_naissance')->get(['date_naissance'])->each(function ($a) use (&$tranches) {
            $age = $a->date_naissance->age;
            match (true) {
                $age < 30 => $tranches['< 30']++,
                $age < 40 => $tranches['30-39']++,
                $age < 50 => $tranches['40-49']++,
                $age < 60 => $tranches['50-59']++,
                default   => $tranches['60+']++,
            };
        });
        return $tranches;
    }

    private function parFamillePosition(CategoriePosition $famille): int
    {
        return Agent::join('positions_administratives as p', 'agents.position_administrative_id', '=', 'p.id')
            ->where('p.categorie', $famille->value)
            ->count();
    }

    private function prochesRetraite(): int
    {
        $mois = (int) config('gesperes.retraite.alerte_mois_avant', 24);
        return Agent::whereNotNull('date_retraite')
            ->whereBetween('date_retraite', [now(), now()->addMonths($mois)])
            ->count();
    }
}
