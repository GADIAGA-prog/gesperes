<?php

namespace App\Support;

use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Emploi;
use App\Models\Fonction;
use App\Models\Indice;
use App\Models\Localite;
use App\Models\PositionAdministrative;
use App\Models\Poste;
use App\Models\Specialite;
use App\Models\TypeEnseignement;
use App\Models\Zone;

/**
 * Registre des référentiels gérés génériquement (CRUD piloté par configuration).
 * Chaque entrée décrit le modèle, le libellé d'affichage et les champs additionnels
 * au-delà des colonnes communes code / libelle / actif.
 */
final class ReferentielRegistry
{
    public static function all(): array
    {
        return [
            'categories' => [
                'model' => Categorie::class,
                'titre' => 'Catégories',
                'singulier' => 'Catégorie',
                'champs' => [],
            ],
            'echelles' => [
                'model' => Echelle::class,
                'titre' => 'Échelles',
                'singulier' => 'Échelle',
                'champs' => [
                    'categorie_id' => ['label' => 'Catégorie', 'type' => 'select', 'source' => Categorie::class],
                ],
            ],
            'classes' => [
                'model' => Classe::class,
                'titre' => 'Classes',
                'singulier' => 'Classe',
                'champs' => [],
            ],
            'echelons' => [
                'model' => Echelon::class,
                'titre' => 'Échelons',
                'singulier' => 'Échelon',
                'champs' => [
                    'rang' => ['label' => 'Rang', 'type' => 'number'],
                ],
            ],
            'indices' => [
                'model' => Indice::class,
                'titre' => 'Indices',
                'singulier' => 'Indice',
                'importable' => true,
                'champs' => [
                    'categorie_id' => ['label' => 'Catégorie', 'type' => 'select', 'source' => Categorie::class],
                    'classe_id' => ['label' => 'Classe', 'type' => 'select', 'source' => Classe::class],
                    'echelon_id' => ['label' => 'Échelon', 'type' => 'select', 'source' => Echelon::class],
                    'valeur' => ['label' => 'Valeur', 'type' => 'number'],
                ],
            ],
            'emplois' => [
                'model' => Emploi::class,
                'titre' => 'Emplois',
                'singulier' => 'Emploi',
                'champs' => [
                    'categorie_id' => ['label' => 'Catégorie', 'type' => 'select', 'source' => Categorie::class],
                    'enseignant' => ['label' => 'Emploi enseignant', 'type' => 'boolean'],
                    'volume_horaire_defaut' => ['label' => 'Volume horaire par défaut', 'type' => 'number'],
                ],
            ],
            'fonctions' => [
                'model' => Fonction::class,
                'titre' => 'Fonctions',
                'singulier' => 'Fonction',
                'champs' => [],
            ],
            'postes' => [
                'model' => Poste::class,
                'titre' => 'Postes',
                'singulier' => 'Poste',
                'champs' => [],
            ],
            'positions' => [
                'model' => PositionAdministrative::class,
                'titre' => 'Positions administratives',
                'singulier' => 'Position administrative',
                'champs' => [
                    'categorie' => ['label' => 'Famille', 'type' => 'enum', 'enum' => \App\Enums\CategoriePosition::class],
                ],
            ],
            'zones' => [
                'model' => Zone::class,
                'titre' => 'Zones',
                'singulier' => 'Zone',
                'champs' => [],
            ],
            'localites' => [
                'model' => Localite::class,
                'titre' => 'Localités',
                'singulier' => 'Localité',
                'champs' => [
                    'zone_id' => ['label' => 'Zone', 'type' => 'select', 'source' => Zone::class],
                    'region' => ['label' => 'Région', 'type' => 'text'],
                    'province' => ['label' => 'Province', 'type' => 'text'],
                    'commune' => ['label' => 'Commune', 'type' => 'text'],
                ],
            ],
            'types-enseignement' => [
                'model' => TypeEnseignement::class,
                'titre' => 'Types d\'enseignement',
                'singulier' => 'Type d\'enseignement',
                'champs' => [],
            ],
            'specialites' => [
                'model' => Specialite::class,
                'titre' => 'Spécialités',
                'singulier' => 'Spécialité',
                'champs' => [
                    'type_enseignement_id' => ['label' => 'Type d\'enseignement', 'type' => 'select', 'source' => TypeEnseignement::class],
                ],
            ],
        ];
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }
}
