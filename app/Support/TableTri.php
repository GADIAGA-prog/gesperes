<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Tri serveur des tableaux par clic sur l'en-tête de colonne.
 *
 * Les colonnes triables sont déclarées par le contrôleur (liste blanche : aucune
 * injection possible sur le nom de colonne). Chaque cible est soit un nom de
 * colonne SQL, soit une closure fn(Builder $q, string $sens) pour les tris
 * complexes (relations, sous-requêtes…).
 *
 * Paramètres lus : ?tri=<clé>&sens=asc|desc
 */
class TableTri
{
    /**
     * @param  array<string, string|Closure>  $triables  clé publique => colonne SQL ou closure
     */
    public static function appliquer(
        Builder $query,
        Request $request,
        array $triables,
        ?string $colonneDefaut = null,
        string $sensDefaut = 'asc',
    ): Builder {
        $tri = (string) $request->input('tri', '');
        $sens = $request->input('sens') === 'desc' ? 'desc' : 'asc';

        if ($tri !== '' && isset($triables[$tri])) {
            $cible = $triables[$tri];
            if ($cible instanceof Closure) {
                $cible($query, $sens);
            } else {
                $query->orderBy($cible, $sens);
            }

            return $query;
        }

        if ($colonneDefaut !== null) {
            $query->orderBy($colonneDefaut, $sensDefaut);
        }

        return $query;
    }
}
