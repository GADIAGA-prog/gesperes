<?php

/*
|--------------------------------------------------------------------------
| Alias d'emplois — harmonisation fichier SITUATION ↔ référentiel emplois
|--------------------------------------------------------------------------
| Libellés présents dans le fichier nominatif mais correspondant (variante de
| graphie / abréviation) à un emploi DÉJÀ existant du référentiel. La clé est le
| libellé exact du fichier ; la valeur est le CODE de l'emploi cible.
| Utilisé lors de l'import des agents pour rattacher au bon emploi sans créer
| de doublon. Tout libellé absent d'ici ET du référentiel est créé.
*/

return [
    "Conseiller d'orientation scolaire et professionelle"                              => 'COSP',
    "Assistant en Gestion des Ressources Humaines"                                     => 'Ast GRHMA',
    "Adjoint en gestion des ressources humaines"                                       => 'AGRHMA',
    "Conseiller en Gestion des Ressources Humaines"                                    => 'CGRHMA',
    "Sous-officiers de police"                                                         => 'SSP',
    "Adjoint en Gestion des Ressources Humaines et Management de l'Administration"      => 'AGRHMA',
    "Inspecteur du Trésor"                                                             => 'Insp Tr',
];
