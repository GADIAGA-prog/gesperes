<?php

/*
|--------------------------------------------------------------------------
| Paramètres métier GesPerES
|--------------------------------------------------------------------------
| ⚠️ VALEURS À CONFIRMER AVEC LE MÉTIER (RH / textes réglementaires).
| Elles sont centralisées ici pour ne JAMAIS coder une règle en dur.
*/

return [
    // Âge légal de départ à la retraite (en années).
    // Peut varier selon la catégorie : surcharger via 'retraite.par_categorie'.
    'retraite' => [
        'age_defaut' => env('GESPERES_AGE_RETRAITE', 60),
        // Exemple de surcharge par code catégorie (à valider) :
        // 'par_categorie' => ['A' => 63, 'B' => 60, 'C' => 58, 'D' => 55, 'E' => 55],
        'par_categorie' => [],
        // Nombre de mois avant la retraite pour déclencher l'alerte "proche retraite".
        'alerte_mois_avant' => env('GESPERES_RETRAITE_ALERTE_MOIS', 24),
    ],

    // Allocation familiale (montant indicatif, NON contractuel).
    'allocation_familiale' => [
        'montant_par_enfant' => env('GESPERES_ALLOC_PAR_ENFANT', 2000), // FCFA / enfant / mois
        'nombre_max_enfants' => env('GESPERES_ALLOC_MAX_ENFANTS', 6),
    ],

    // Volume horaire hebdomadaire dû par défaut pour un enseignant
    // si l'emploi ne précise rien (à valider).
    'volume_horaire_defaut' => env('GESPERES_VHD', 18),
];
