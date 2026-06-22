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
        'Paramétrage' => [
            'settings.view'   => 'Consulter le paramétrage',
            'settings.manage' => 'Gérer les référentiels',
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
