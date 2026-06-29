<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Crée toutes les permissions et les 7 rôles définis dans GesPerES.
 * Le Super Admin obtient toutes les permissions via Gate::before (voir AuthServiceProvider).
 */
class RoleSeeder extends Seeder
{
    /** Permissions attribuées par rôle (sauf super-admin, qui a tout via Gate::before). */
    private array $attribution = [
        RoleName::ADMIN_NATIONAL->value => '*', // toutes les permissions explicitement
        RoleName::ADMIN_REGIONAL->value => [
            'dashboard.view',
            'agents.view', 'agents.create', 'agents.update', 'agents.export',
            'structures.view', 'structures.create', 'structures.update',
            'affectations.view', 'affectations.create',
            'carriere.view', 'carriere.manage',
            'mouvements.view', 'mouvements.manage',
            'indemnites.view', 'indemnites.manage',
            'formations.view', 'formations.manage',
            'competences.view', 'competences.manage',
            'performance.view', 'performance.manage',
            'discipline.view', 'discipline.manage',
            'gpec.view', 'tpee.manage',
            'fiches-poste.view', 'fiches-poste.manage',
            'suivi.view', 'suivi.manage',
            'documents.view', 'documents.upload', 'documents.download',
            'users.view',
            'settings.view',
            'reports.view', 'reports.export',
            'pointage.view', 'conges.view', 'conges.validate', 'presence.reports',
            'budget.view', 'budget.manage',
            'alertes.view',
            'audit.view',
        ],
        RoleName::AGENT_RH->value => [
            'dashboard.view',
            'agents.view', 'agents.create', 'agents.update', 'agents.import', 'agents.export',
            'structures.view',
            'affectations.view', 'affectations.create',
            'carriere.view', 'carriere.manage',
            'mouvements.view', 'mouvements.manage',
            'indemnites.view', 'indemnites.manage',
            'formations.view', 'formations.manage',
            'competences.view', 'competences.manage',
            'performance.view', 'performance.manage',
            'discipline.view', 'discipline.manage',
            'gpec.view', 'tpee.manage',
            'fiches-poste.view', 'fiches-poste.manage',
            'suivi.view', 'suivi.manage',
            'documents.view', 'documents.upload', 'documents.download',
            'settings.view',
            'reports.view', 'reports.export',
            'pointage.view', 'pointage.manage', 'conges.view', 'conges.request', 'conges.validate', 'presence.reports',
            'alertes.view',
        ],
        RoleName::RESPONSABLE_STRUCTURE->value => [
            'dashboard.view',
            'agents.view',
            'structures.view',
            'affectations.view',
            'carriere.view',
            'mouvements.view',
            'indemnites.view',
            'formations.view',
            'competences.view',
            'performance.view',
            'discipline.view',
            'gpec.view',
            'fiches-poste.view',
            'suivi.view', 'suivi.manage',
            'documents.view', 'documents.download',
            'reports.view',
            'pointage.view', 'pointage.manage', 'conges.view', 'presence.reports',
            'alertes.view',
        ],
        RoleName::CONSULTATION->value => [
            'dashboard.view',
            'agents.view',
            'structures.view',
            'affectations.view',
            'carriere.view',
            'mouvements.view',
            'indemnites.view',
            'formations.view',
            'competences.view',
            'performance.view',
            'discipline.view',
            'gpec.view',
            'fiches-poste.view',
            'suivi.view',
            'documents.view',
            'reports.view',
            'pointage.view', 'conges.view',
            'alertes.view',
        ],
        // Aucune permission d'administration : l'espace agent (self-service) est
        // cloisonné par le middleware `agent.individuel` et le périmètre par
        // user_id. Lui accorder dashboard.view/documents.* exposerait le back-office.
        RoleName::AGENT_INDIVIDUEL->value => [],
    ];

    public function run(): void
    {
        // 1. Créer toutes les permissions
        $toutes = Permissions::all();
        foreach ($toutes as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Créer les rôles
        foreach (RoleName::cases() as $roleEnum) {
            $role = Role::firstOrCreate(['name' => $roleEnum->value, 'guard_name' => 'web']);

            $attribution = $this->attribution[$roleEnum->value] ?? [];

            if ($attribution === '*') {
                $role->syncPermissions($toutes);
            } else {
                // Un tableau (même vide) synchronise EXACTEMENT ces permissions :
                // l'agent individuel se retrouve ainsi sans aucune permission admin.
                $role->syncPermissions($attribution);
            }
        }

        $this->command->info('✓ ' . count($toutes) . ' permissions et ' . count(RoleName::cases()) . ' rôles créés.');
    }
}
