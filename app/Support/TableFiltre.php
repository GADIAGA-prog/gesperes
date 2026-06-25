<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filtre serveur des tableaux, colonne par colonne (sous l'en-tête).
 *
 * Les colonnes filtrables sont déclarées par le contrôleur (liste blanche).
 * Chaque cible est soit un nom de colonne SQL (filtre « LIKE %valeur% »), soit
 * une closure fn(Builder $q, string $valeur) pour les filtres complexes
 * (relations, correspondance exacte, cascade de structures…).
 *
 * Paramètres lus : ?f[<clé>]=<valeur>
 */
class TableFiltre
{
    /**
     * @param  array<string, string|Closure>  $filtrables  clé publique => colonne SQL ou closure
     */
    public static function appliquer(Builder $query, Request $request, array $filtrables): Builder
    {
        $valeurs = (array) $request->input('f', []);

        foreach ($filtrables as $cle => $cible) {
            $valeur = trim((string) ($valeurs[$cle] ?? ''));
            if ($valeur === '') {
                continue;
            }

            if ($cible instanceof Closure) {
                $cible($query, $valeur);
            } else {
                $query->where($cible, 'like', "%{$valeur}%");
            }
        }

        return $query;
    }
}
