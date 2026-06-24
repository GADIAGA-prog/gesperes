<?php

namespace App\Support;

use App\Models\Categorie;
use App\Models\Classe;
use App\Models\Echelle;
use App\Models\Echelon;
use App\Models\Emploi;
use App\Models\EmploiType;
use App\Models\FamilleProfessionnelle;
use App\Models\Fonction;
use App\Models\Indice;
use App\Models\Localite;
use App\Models\MotifAbsence;
use App\Models\Action;
use App\Models\PositionAdministrative;
use App\Models\Poste;
use App\Models\Programme;
use App\Models\Province;
use App\Models\Region;
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
                'champs' => [],
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
                'champs' => [],
            ],
            'indices' => [
                'model' => Indice::class,
                'titre' => 'Indices',
                'singulier' => 'Indice',
                'importable' => true,
                'champs' => [
                    'categorie_id' => ['label' => 'Catégorie', 'type' => 'select', 'source' => Categorie::class],
                    'echelle_id' => ['label' => 'Échelle', 'type' => 'select', 'source' => Echelle::class],
                    'classe_id' => ['label' => 'Classe', 'type' => 'select', 'source' => Classe::class],
                    'echelon_id' => ['label' => 'Échelon', 'type' => 'select', 'source' => Echelon::class],
                    'valeur' => ['label' => 'Indice', 'type' => 'number'],
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
                'champs' => [
                    'indemnite_responsabilite' => ['label' => 'Indemnité de responsabilité (FCFA/mois)', 'type' => 'number'],
                ],
            ],
            'postes' => [
                'model' => Poste::class,
                'titre' => 'Postes',
                'singulier' => 'Poste',
                'champs' => [],
            ],
            'familles-professionnelles' => [
                'model' => FamilleProfessionnelle::class,
                'titre' => 'Familles professionnelles',
                'singulier' => 'Famille professionnelle',
                'champs' => [
                    'metier' => ['label' => 'Métier de rattachement', 'type' => 'text'],
                ],
            ],
            'emplois-types' => [
                'model' => EmploiType::class,
                'titre' => 'Emplois-types',
                'singulier' => 'Emploi-type',
                'champs' => [
                    'famille_professionnelle_id' => ['label' => 'Famille professionnelle', 'type' => 'select', 'source' => FamilleProfessionnelle::class],
                ],
            ],
            'positions' => [
                'model' => PositionAdministrative::class,
                'titre' => 'Positions administratives',
                'singulier' => 'Position administrative',
                'champs' => [
                    'categorie' => ['label' => 'Famille', 'type' => 'enum', 'enum' => \App\Enums\CategoriePosition::class],
                ],
            ],
            'regions' => [
                'model' => Region::class,
                'titre' => 'Régions',
                'singulier' => 'Région',
                'champs' => [],
            ],
            'provinces' => [
                'model' => Province::class,
                'titre' => 'Provinces',
                'singulier' => 'Province',
                'champs' => [
                    'region_id' => ['label' => 'Région', 'type' => 'select', 'source' => Region::class],
                    'chef_lieu' => ['label' => 'Chef-lieu', 'type' => 'text'],
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
                    'province_id' => ['label' => 'Province', 'type' => 'select', 'source' => Province::class],
                    'zone_id' => ['label' => 'Zone', 'type' => 'select', 'source' => Zone::class],
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
            'programmes' => [
                'model' => Programme::class,
                'titre' => 'Programmes budgétaires',
                'singulier' => 'Programme',
                'champs' => [],
            ],
            'actions' => [
                'model' => Action::class,
                'titre' => 'Actions',
                'singulier' => 'Action',
                'champs' => [
                    'programme_id' => ['label' => 'Programme', 'type' => 'select', 'source' => Programme::class],
                ],
            ],
            'motifs-absence' => [
                'model' => MotifAbsence::class,
                'titre' => 'Motifs d\'absence',
                'singulier' => 'Motif d\'absence',
                'champs' => [
                    'categorie' => ['label' => 'Nature', 'type' => 'enum', 'enum' => \App\Enums\CategorieAbsence::class],
                ],
            ],
        ];
    }

    public static function get(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Référentiels organisés en groupes cohérents pour l'affichage.
     * Renvoie [libellé du groupe => [slug => config, …]] dans un ordre stable.
     */
    public static function groupes(): array
    {
        $plan = [
            'remuneration' => ['label' => 'Rémunération (grille indiciaire)', 'slugs' => ['categories', 'echelles', 'classes', 'echelons', 'indices']],
            'emplois'      => ['label' => 'Emplois & carrière', 'slugs' => ['emplois', 'fonctions', 'postes', 'positions', 'familles-professionnelles', 'emplois-types']],
            'geographie'   => ['label' => 'Découpage géographique', 'slugs' => ['regions', 'provinces', 'zones', 'localites']],
            'enseignement' => ['label' => 'Enseignement', 'slugs' => ['types-enseignement', 'specialites']],
            'budget'       => ['label' => 'Budget (nomenclature programmatique)', 'slugs' => ['programmes', 'actions']],
            'absences'     => ['label' => 'Congés & absences', 'slugs' => ['motifs-absence']],
        ];

        $all = self::all();
        $groupes = [];

        foreach ($plan as $cle => $def) {
            $items = [];
            foreach ($def['slugs'] as $slug) {
                if (isset($all[$slug])) {
                    $items[$slug] = $all[$slug];
                }
            }
            if ($items) {
                $groupes[$cle] = ['label' => $def['label'], 'items' => $items];
            }
        }

        // Filet de sécurité : référentiels non classés.
        $classes = array_merge(...array_column($plan, 'slugs'));
        $autres = array_filter($all, fn ($config, $slug) => ! in_array($slug, $classes, true), ARRAY_FILTER_USE_BOTH);
        if ($autres) {
            $groupes['autres'] = ['label' => 'Autres', 'items' => $autres];
        }

        return $groupes;
    }
}
