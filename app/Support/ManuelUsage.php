<?php

namespace App\Support;

/**
 * Manuel d'usage de la plateforme GesPerES. Sert à la fois à la page d'aide
 * et au chatbox (recherche par mots-clés). Chaque rubrique : titre, module,
 * mots-clés et contenu (réponse / guide).
 */
final class ManuelUsage
{
    public static function rubriques(): array
    {
        return [
            [
                'id' => 'tableau-bord', 'module' => 'Tableau de bord', 'titre' => 'Tableau de bord',
                'cles' => 'accueil statistiques effectifs masse salariale graphiques indicateurs pyramide ages retraite categorie',
                'contenu' => "Le tableau de bord présente les indicateurs clés : effectif total, agents actifs, proches de la retraite, dossiers incomplets, répartition par sexe, par région, par catégorie, pyramide des âges, départs à la retraite et masse salariale (traitement, indemnités, total annuel).",
            ],
            [
                'id' => 'effectifs-agents', 'module' => 'Gestion des effectifs', 'titre' => 'Gérer les agents',
                'cles' => 'agent fiche creer ajouter modifier rechercher matricule import export effectif personnel dossier',
                'contenu' => "Dans « Gestion des effectifs » → onglet Agents : recherchez un agent (matricule, nom), créez ou modifiez une fiche. La fiche est organisée en onglets : État civil, Carrière, Affectation (structure + établissement/poste), Enseignement, Famille. L'import/export Excel est disponible selon vos droits.",
            ],
            [
                'id' => 'effectifs-structures', 'module' => 'Gestion des effectifs', 'titre' => 'Organigramme des structures',
                'cles' => 'structure organigramme direction service etablissement hierarchie arborescence rattachement parent',
                'contenu' => "Onglet Structures : visualisez et gérez l'arborescence (ministère → directions → services → établissements). Le lien hiérarchique (parent) et l'action budgétaire de chaque structure se gèrent ici. Les agents sont rattachés à la structure la plus profonde.",
            ],
            [
                'id' => 'carriere', 'module' => 'Carrière et mouvement', 'titre' => 'Actes de carrière',
                'cles' => 'carriere avancement promotion nomination acte titularisation integration reclassement',
                'contenu' => "Module « Carrière et mouvement » → onglet Carrière : enregistrez les actes de carrière (avancements, promotions, nominations…) d'un agent avec leur date d'effet et référence.",
            ],
            [
                'id' => 'mouvements', 'module' => 'Carrière et mouvement', 'titre' => 'Mouvements & affectations',
                'cles' => 'mouvement affectation mutation sortie temporaire definitive disponibilite detachement retraite position administrative',
                'contenu' => "Onglet Mouvements : sous-onglets Affectations, Changements de position, Sorties temporaires (disponibilité, détachement…) et Sorties définitives (retraite, décès…). Une alerte signale les fins de sortie temporaire proches et les retraités de l'année.",
            ],
            [
                'id' => 'presence', 'module' => 'Contrôle présence', 'titre' => 'Pointage, congés et fiches de présence',
                'cles' => 'pointage presence absence conge autorisation fiche journaliere solde demande validation',
                'contenu' => "Module « Contrôle présence » : Pointage (situation journalière de présence), Congés (demandes et autorisations d'absence, soldes), Fiches de présence (A/B/C). Sélectionnez la structure et la date pour pointer.",
            ],
            [
                'id' => 'evaluation', 'module' => 'Évaluation', 'titre' => 'Compétences, performance et discipline',
                'cles' => 'evaluation competence performance notation objectif discipline sanction recours appreciation',
                'contenu' => "Module « Évaluation » : Compétences (référentiel et rattachement aux agents), Performance (évaluations, objectifs, notation) et Discipline (demandes d'explication, sanctions, recours).",
            ],
            [
                'id' => 'outils-grh', 'module' => 'Outils GRH', 'titre' => 'GPEC, fiches de poste, MPP, alertes',
                'cles' => 'gpec prevision emploi competence tpee fiche poste referentiel mpp processus procedure plan formation alerte rh notification',
                'contenu' => "Module « Outils GRH » : GPEC (prévisions), Fiches de poste, TPEE, Référentiels MPP GRH (processus et procédures — sélectionnez un processus pour voir ses procédures et opérations), Plan de formation et Alertes RH (retraites proches, documents expirés).",
            ],
            [
                'id' => 'budget-personnel', 'module' => 'Budget', 'titre' => 'Dépenses du personnel',
                'cles' => 'budget paie salaire solde indiciaire indemnite carfo residence responsabilite logement astreinte technicite allocation total mensuel annuel',
                'contenu' => "Module « Budget » → Dépenses du personnel : état de paie par agent (solde indiciaire, résidence, CARFO 13,5 %, indemnités, allocation) avec filtres par structure, emploi, catégorie. Le total mensuel et annuel sont calculés automatiquement.",
            ],
            [
                'id' => 'budget-annexe', 'module' => 'Budget', 'titre' => 'Tableaux annexes & enveloppe',
                'cles' => 'annexe tableau programme direction provision supplement naissance enveloppe ventilation incidence export excel pdf',
                'contenu' => "Tableaux annexes : détail par agent et par structure, regroupé par programme, avec Total, provisions (suppléments 3 %, naissances 5 %) et Total général ; exports Excel et PDF (A4 paysage). Enveloppe (n+1 à n+3) : saisie de l'enveloppe de référence et ventilation détaillée. Dépenses de fonctionnement : budget et programme d'activités des structures.",
            ],
            [
                'id' => 'configurations', 'module' => 'Configurations', 'titre' => 'Référentiels et indemnités',
                'cles' => 'configuration referentiel nomenclature categorie echelle classe echelon indice emploi fonction poste region province zone localite programme action indemnite bareme',
                'contenu' => "Module « Configurations » : un onglet par groupe de référentiels (Rémunération, Emplois & carrière, Découpage géographique, Enseignement, Budget, Congés & absences) et un onglet Indemnités (catalogue et barèmes). Modifiez ici les nomenclatures de base.",
            ],
            [
                'id' => 'actes-archives', 'module' => 'Actes et archives', 'titre' => 'Actes et archives (documents)',
                'cles' => 'document acte archive piece jointe telecharger televerser upload download dossier numerique zip recherche',
                'contenu' => "Module « Actes et archives » : recherchez, consultez et téléversez les pièces (actes, documents) d'un agent. Les documents sont sur un disque privé ; le téléchargement est sécurisé.",
            ],
            [
                'id' => 'acces', 'module' => 'Gestion accès', 'titre' => 'Utilisateurs, rôles et audit',
                'cles' => 'utilisateur compte role droit permission acces audit journal trace securite mot de passe',
                'contenu' => "Module « Gestion accès » (administrateurs) : comptes Utilisateurs, Rôles & droits (permissions par rôle) et Journal d'audit (traçabilité des actions).",
            ],
            [
                'id' => 'connexion', 'module' => 'Compte', 'titre' => 'Connexion et profil',
                'cles' => 'connexion login mot de passe oublie profil compte deconnexion reinitialiser',
                'contenu' => "Connectez-vous avec votre e-mail et mot de passe. En cas d'oubli, utilisez « Mot de passe oublié ». Votre profil (nom, e-mail, mot de passe) se modifie depuis le menu en haut à droite.",
            ],
        ];
    }
}
