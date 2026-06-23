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

    // Mouvements du personnel.
    'mouvements' => [
        // Nombre de mois avant la fin prévue d'une sortie temporaire pour
        // déclencher l'alerte (rappel de réintégration / fin de position).
        'alerte_sortie_mois_avant' => env('GESPERES_SORTIE_ALERTE_MOIS', 2),
    ],

    // Dossier des fichiers GESPER (barèmes d'indemnités du décret 2014-427).
    'gesper_salaire_path' => env('GESPER_SALAIRE_PATH', base_path('../pour gesperes/GESPER/salaire')),

    // Dossier des barèmes d'indemnités corrigés (logement traité, astreinte VF, spécifique).
    'gesper_indemnite_path' => env('GESPER_INDEMNITE_PATH', base_path('../pour gesperes/indemnité')),

    // Fichier source de la grille indiciaire officielle (echelon_code → indice).
    'gesper_indices_path' => env('GESPER_INDICES_PATH', base_path('../pour gesperes/GESPER/Export/indices.xlsx')),

    // Fichier nominatif « SITUATION DU PERSONNEL » (enrichissement de la base agents).
    'gesper_situation_path' => env('GESPER_SITUATION_PATH', base_path('../pour gesperes/SITUATION DU PERSONNEL DU MESFPT_23-01-2026.xlsx')),

    // Fichier « base liehoun » (montants d'indemnités par agent).
    'gesper_liehoun_path' => env('GESPER_LIEHOUN_PATH', base_path('../pour gesperes/base liehoun.xlsx')),

    // Référentiel MPP GRH (manuel des processus et procédures de la GRH).
    'gesper_mpp_path' => env('GESPER_MPP_PATH', base_path('../pour gesperes/Referentiel_MPP_GRH.xlsx')),

    // Alertes RH : destinataires du digest e-mail (séparés par des virgules dans .env).
    // Vide = pas d'envoi e-mail (les alertes restent disponibles in-app).
    'alertes' => [
        'email_destinataires' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GESPERES_ALERTES_EMAILS', ''))
        ))),
    ],
];
