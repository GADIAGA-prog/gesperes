<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Calcul du salaire indiciaire
    |--------------------------------------------------------------------------
    |
    | Salaire indiciaire mensuel = indice × point_annuel / mois_par_an.
    | (Formule en vigueur : indice × 2331 / 12.)
    | Les valeurs sont surchargeables via l'environnement.
    |
    */

    'point_annuel' => env('POINT_INDICE_ANNUEL', 2331),
    'mois_par_an'  => env('MOIS_PAR_AN', 12),

    /*
    |--------------------------------------------------------------------------
    | Éléments dérivés de l'indice (fichier « Bon_annexe » — feuille barème)
    |--------------------------------------------------------------------------
    | Tous les éléments ci-dessous se déduisent du solde indiciaire. Les taux
    | proviennent des formules du barème officiel et restent paramétrables.
    */

    // CARFO (cotisation retraite) = 13,5 % du solde indiciaire (taux unique).
    'carfo_taux' => env('GRILLE_CARFO_TAUX', 0.135),

    // Provisions du tableau annexe budgétaire.
    'provisions' => [
        'supplements' => 0.03, // suppléments salariaux (3 %)
        'naissances'  => 0.05, // nouvelles naissances (5 %)
    ],

    // Indemnité de résidence = solde indiciaire / 10 (soit 10 %).
    'residence_taux' => env('GRILLE_RESIDENCE_TAUX', 0.10),

    // Abattement appliqué au solde pour la base imposable (20 %).
    'abattement_taux' => env('GRILLE_ABATTEMENT_TAUX', 0.20),

    // Base imposable tronquée au multiple inférieur de :
    'base_imposable_arrondi' => 100,

    /*
    | Barème IUTS (impôt) : tranches évaluées de la plus haute à la plus basse.
    | Pour la première tranche où base > seuil : impôt = (base − seuil) × taux + fixe.
    */
    'iuts_tranches' => [
        ['seuil' => 250000, 'taux' => 0.250, 'fixe' => 39430],
        ['seuil' => 170000, 'taux' => 0.217, 'fixe' => 22070],
        ['seuil' => 120000, 'taux' => 0.184, 'fixe' => 12870],
        ['seuil' => 80000,  'taux' => 0.157, 'fixe' => 6590],
        ['seuil' => 50000,  'taux' => 0.139, 'fixe' => 2420],
        ['seuil' => 30000,  'taux' => 0.121, 'fixe' => 0],
    ],

    // Facteur de réduction de l'IUTS selon le nombre de personnes à charge (0 à 7).
    'charges_facteurs' => [1.0, 0.92, 0.90, 0.88, 0.86, 0.84, 0.82, 0.80],

];
