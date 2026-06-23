<?php

namespace Database\Seeders;

use App\Models\Indemnite;
use Illuminate\Database\Seeder;

/**
 * Catalogue des indemnités tel qu'EFFECTIVEMENT exploité par la plateforme,
 * sur la base des fichiers Excel et du raisonnement de paramétrage (grille
 * Bon_annexe, base liehoun, barèmes corrigés) — et non du seul décret 2014-427.
 *
 * Les références indiquent la source réelle de chaque indemnité.
 */
class IndemniteCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // [code, libellé, mode, barème, valeur, référence]
        $catalogue = [
            ['IR',     'Indemnité de résidence',            'pourcentage', null,         10, 'Grille indiciaire (Bon_annexe) : 10 % du solde indiciaire'],
            ['CM',     'Charge militaire',                  'montant_fixe', null,         0, 'Paramilitaires (montant par agent)'],
            ['RESP',   'Indemnité de responsabilité (de fonction)', 'montant_fixe', null,  0, 'Par fonction (référentiel Fonctions) — observée dans la base liehoun'],
            ['LOG',    'Indemnité de logement',             'bareme',      'logement',    0, 'Barème Logement_traité (catégorie × enseignant × en-classe)'],
            ['ASTR',   'Indemnité d\'astreinte',            'bareme',      'astreinte',   0, 'Barème Astreinte_VF (emploi × zone)'],
            ['SPEC',   'Indemnité spécifique harmonisée',   'bareme',      'specifique',  0, 'Barème Spécifique harmonisé (emploi × zone)'],
            ['TECH',   'Indemnité de technicité',           'bareme',      'technicite',  0, 'Barème Technicités (échelle)'],
            ['ALLOC',  'Allocation familiale',              'montant_fixe', null,         0, 'Règle R9 : nombre d\'enfants × montant par enfant'],
            ['AUTRES', 'Autres indemnités',                 'montant_fixe', null,         0, 'Saisie libre (montant par agent)'],
        ];

        $codes = [];
        foreach ($catalogue as [$code, $libelle, $mode, $bareme, $valeur, $reference]) {
            Indemnite::updateOrCreate(['code' => $code], [
                'libelle'         => $libelle,
                'mode'            => $mode,
                'bareme'          => $bareme,
                'valeur'          => $valeur,
                'reference_texte' => $reference,
                'actif'           => true,
            ]);
            $codes[] = $code;
        }

        // Retire d'éventuelles entrées obsolètes hors de ce catalogue.
        Indemnite::whereNotIn('code', $codes)->delete();

        $this->command->info('✓ Catalogue des indemnités : ' . count($catalogue) . ' entrées (sources : grille, liehoun, barèmes corrigés).');
    }
}
