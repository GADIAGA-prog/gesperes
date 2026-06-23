<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Competence;

/**
 * GPEC — Gestion Prévisionnelle des Emplois et des Compétences.
 * Projette les départs à la retraite, les effectifs et les besoins de remplacement
 * par emploi, à partir des données RH existantes (aucune cible budgétaire requise).
 */
class GpecService
{
    /** Départs à la retraite par année civile sur les `annees` prochaines années. */
    public function departsParAnnee(int $annees = 5): array
    {
        $debut = (int) now()->year;
        $resultat = array_fill_keys(range($debut, $debut + $annees - 1), 0);

        $dates = Agent::whereNotNull('date_retraite')
            ->whereDate('date_retraite', '>=', now()->startOfYear())
            ->whereDate('date_retraite', '<=', now()->startOfYear()->addYears($annees))
            ->pluck('date_retraite');

        foreach ($dates as $d) {
            $an = (int) $d->year;
            if (isset($resultat[$an])) {
                $resultat[$an]++;
            }
        }

        return $resultat;
    }

    /** Effectif courant par emploi (libellé => effectif), trié décroissant. */
    public function effectifParEmploi(): array
    {
        return Agent::join('emplois', 'agents.emploi_id', '=', 'emplois.id')
            ->whereNull('agents.deleted_at')
            ->selectRaw('emplois.libelle, COUNT(*) c')
            ->groupBy('emplois.libelle')
            ->orderByDesc('c')
            ->pluck('c', 'emplois.libelle')
            ->all();
    }

    /** Besoins de remplacement : départs retraite à venir par emploi. */
    public function besoinsParEmploi(int $annees = 5): array
    {
        return Agent::join('emplois', 'agents.emploi_id', '=', 'emplois.id')
            ->whereNull('agents.deleted_at')
            ->whereNotNull('date_retraite')
            ->whereDate('date_retraite', '>=', now())
            ->whereDate('date_retraite', '<=', now()->addYears($annees))
            ->selectRaw('emplois.libelle, COUNT(*) c')
            ->groupBy('emplois.libelle')
            ->orderByDesc('c')
            ->pluck('c', 'emplois.libelle')
            ->all();
    }

    /** Cartographie des compétences : nombre d'agents par compétence. */
    public function cartographieCompetences(): array
    {
        return Competence::withCount('agents')->orderByDesc('agents_count')->get()
            ->mapWithKeys(fn ($c) => [$c->libelle => $c->agents_count])->all();
    }
}
