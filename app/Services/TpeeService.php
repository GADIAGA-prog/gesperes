<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Emploi;
use App\Models\PrevisionEffectif;
use App\Models\Structure;

/**
 * TPEE — Tableau Prévisionnel des Effectifs et des Emplois.
 *
 * Construit, par emploi et sur un horizon pluriannuel, la projection :
 *   Effectif début → − départs (retraite, dérivés de date_retraite)
 *                  + entrées prévues (saisies) = Effectif prévisionnel,
 * puis l'écart par rapport à l'effectif cible (saisi). L'effectif de fin d'une
 * année devient l'effectif de début de la suivante.
 *
 * Aucune règle inventée : départs et effectifs proviennent des données agents ;
 * entrées et cibles sont des hypothèses saisies (PrevisionEffectif).
 */
class TpeeService
{
    public function tableau(int $annees = 3, ?int $structureId = null, ?string $q = null): array
    {
        $debut = (int) now()->year;
        $fin = $debut + $annees - 1;
        $listeAnnees = range($debut, $fin);
        $sousArbre = $structureId ? Structure::sousArbreIds($structureId) : null;

        // Effectif courant par emploi (dans le périmètre).
        $effectifs = Agent::query()
            ->whereNotNull('emploi_id')
            ->when($sousArbre, fn ($qq) => $qq->whereIn('structure_id', $sousArbre))
            ->selectRaw('emploi_id, COUNT(*) c')
            ->groupBy('emploi_id')
            ->pluck('c', 'emploi_id');

        // Départs retraite par emploi et par année (dérivés).
        $departs = [];
        Agent::query()
            ->whereNotNull('emploi_id')
            ->whereNotNull('date_retraite')
            ->when($sousArbre, fn ($qq) => $qq->whereIn('structure_id', $sousArbre))
            ->whereBetween('date_retraite', ["{$debut}-01-01", "{$fin}-12-31"])
            ->get(['emploi_id', 'date_retraite'])
            ->each(function ($a) use (&$departs) {
                $an = (int) $a->date_retraite->year;
                $departs[$a->emploi_id][$an] = ($departs[$a->emploi_id][$an] ?? 0) + 1;
            });

        // Hypothèses saisies (entrées, cible) pour la portée choisie.
        $prev = [];
        PrevisionEffectif::query()
            ->whereIn('annee', $listeAnnees)
            ->when($structureId,
                fn ($qq) => $qq->where('structure_id', $structureId),
                fn ($qq) => $qq->whereNull('structure_id'))
            ->get()
            ->each(function ($p) use (&$prev) {
                $prev[$p->emploi_id][$p->annee] = $p;
            });

        // Emplois à afficher : ceux ayant un effectif, des départs ou une prévision.
        $ids = collect($effectifs->keys())
            ->merge(array_keys($departs))
            ->merge(array_keys($prev))
            ->unique()->filter()->values();

        $emplois = Emploi::whereIn('id', $ids)
            ->when($q, fn ($qq, $v) => $qq->where('libelle', 'like', "%{$v}%"))
            ->orderBy('libelle')
            ->get(['id', 'libelle', 'code']);

        $lignes = [];
        $totaux = array_fill_keys($listeAnnees, ['dep' => 0, 'ent' => 0, 'fin' => 0, 'cible' => 0, 'ecart' => 0]);
        $totalEffectif = 0;

        foreach ($emplois as $e) {
            $courant = (int) ($effectifs[$e->id] ?? 0);
            $totalEffectif += $courant;
            $cols = [];

            foreach ($listeAnnees as $an) {
                $dep = (int) ($departs[$e->id][$an] ?? 0);
                $p = $prev[$e->id][$an] ?? null;
                $ent = (int) ($p?->entrees_prevues ?? 0);
                $cible = $p?->effectif_cible; // int|null
                $finAnnee = max(0, $courant - $dep + $ent);
                $ecart = $cible !== null ? $cible - $finAnnee : null;

                $cols[$an] = ['dep' => $dep, 'ent' => $ent, 'fin' => $finAnnee, 'cible' => $cible, 'ecart' => $ecart];

                $totaux[$an]['dep'] += $dep;
                $totaux[$an]['ent'] += $ent;
                $totaux[$an]['fin'] += $finAnnee;
                if ($cible !== null) {
                    $totaux[$an]['cible'] += $cible;
                    $totaux[$an]['ecart'] += $ecart;
                }

                $courant = $finAnnee;
            }

            $lignes[] = ['emploi' => $e, 'effectif' => (int) ($effectifs[$e->id] ?? 0), 'annees' => $cols];
        }

        return [
            'annees'         => $listeAnnees,
            'lignes'         => $lignes,
            'totaux'         => $totaux,
            'total_effectif' => $totalEffectif,
        ];
    }
}
