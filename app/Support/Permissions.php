<?php

namespace App\Support;

/**
 * Catalogue central des permissions de GesPerES.
 * Sert de source unique pour le seeder, les policies et les vues.
 */
final class Permissions
{
    public const GROUPS = [
        'Tableau de bord' => [
            'dashboard.view' => 'Consulter le tableau de bord',
        ],
        'Agents' => [
            'agents.view'   => 'Consulter les agents',
            'agents.create' => 'Créer un agent',
            'agents.update' => 'Modifier un agent',
            'agents.delete' => 'Supprimer un agent',
            'agents.import' => 'Importer des agents',
            'agents.export' => 'Exporter des agents',
        ],
        'Structures' => [
            'structures.view'   => 'Consulter les structures',
            'structures.create' => 'Créer une structure',
            'structures.update' => 'Modifier une structure',
            'structures.delete' => 'Supprimer une structure',
        ],
        'Affectations' => [
            'affectations.view'   => 'Consulter les affectations',
            'affectations.create' => 'Créer une affectation',
            'affectations.update' => 'Modifier une affectation',
            'affectations.delete' => 'Supprimer une affectation',
        ],
        'Carrière' => [
            'carriere.view'   => 'Consulter la carrière des agents',
            'carriere.manage' => 'Enregistrer les actes de carrière',
        ],
        'Mouvements' => [
            'mouvements.view'   => 'Consulter les mouvements du personnel',
            'mouvements.manage' => 'Enregistrer les mouvements du personnel',
        ],
        'Indemnités' => [
            'indemnites.view'   => 'Consulter les indemnités',
            'indemnites.manage' => 'Gérer les indemnités et attributions',
        ],
        'Développement RH' => [
            'formations.view'    => 'Consulter les formations',
            'formations.manage'  => 'Gérer les formations',
            'competences.view'   => 'Consulter les compétences',
            'competences.manage' => 'Gérer les compétences',
            'performance.view'   => 'Consulter les évaluations',
            'performance.manage' => 'Gérer les évaluations',
            'gpec.view'          => 'Consulter la GPEC',
        ],
        'Fiches de poste' => [
            'fiches-poste.view'   => 'Consulter les fiches de poste',
            'fiches-poste.manage' => 'Gérer les fiches de poste',
        ],
        'Discipline' => [
            'discipline.view'   => 'Consulter les dossiers disciplinaires',
            'discipline.manage' => 'Gérer les dossiers disciplinaires',
        ],
        'Suivi des dossiers' => [
            'suivi.view'   => 'Consulter le suivi des dossiers',
            'suivi.manage' => 'Gérer le suivi des dossiers',
        ],
        'Documents RH' => [
            'documents.view'     => 'Consulter les documents',
            'documents.upload'   => 'Téléverser un document',
            'documents.download' => 'Télécharger un document',
            'documents.delete'   => 'Supprimer un document',
        ],
        'Utilisateurs' => [
            'users.view'   => 'Consulter les utilisateurs',
            'users.create' => 'Créer un utilisateur',
            'users.update' => 'Modifier un utilisateur',
            'users.delete' => 'Supprimer un utilisateur',
        ],
        'Présence & Congés' => [
            'pointage.view'     => 'Consulter les pointages',
            'pointage.manage'   => 'Saisir / gérer les pointages',
            'conges.view'       => 'Consulter les congés',
            'conges.request'    => 'Demander un congé',
            'conges.validate'   => 'Valider les congés',
            'presence.reports'  => 'Éditer les fiches de présence (A/B/C)',
        ],
        'Budget des structures' => [
            'budget.view'   => 'Consulter le budget et les activités',
            'budget.manage' => 'Gérer le budget et les activités',
        ],
        'Paramétrage' => [
            'settings.view'   => 'Consulter le paramétrage',
            'settings.manage' => 'Gérer les référentiels',
        ],
        'Alertes RH' => [
            'alertes.view' => 'Consulter les alertes RH',
        ],
        'Audit' => [
            'audit.view' => 'Consulter le journal d\'audit',
        ],
        'Rapports' => [
            'reports.view'   => 'Consulter les rapports',
            'reports.export' => 'Exporter les rapports',
        ],
    ];

    /** Liste plate de toutes les permissions. */
    public static function all(): array
    {
        $perms = [];
        foreach (self::GROUPS as $group) {
            foreach ($group as $key => $label) {
                $perms[] = $key;
            }
        }
        return $perms;
    }

    /** Map permission => libellé. */
    public static function labels(): array
    {
        $labels = [];
        foreach (self::GROUPS as $group) {
            foreach ($group as $key => $label) {
                $labels[$key] = $label;
            }
        }
        return $labels;
    }
}
