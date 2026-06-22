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
            'documents.view', 'documents.upload', 'documents.download',
            'users.view',
            'settings.view',
            'reports.view', 'reports.export',
            'audit.view',
        ],
        RoleName::AGENT_RH->value => [
            'dashboard.view',
            'agents.view', 'agents.create', 'agents.update', 'agents.import', 'agents.export',
            'structures.view',
            'affectations.view', 'affectations.create',
            'documents.view', 'documents.upload', 'documents.download',
            'settings.view',
            'reports.view', 'reports.export',
        ],
        RoleName::RESPONSABLE_STRUCTURE->value => [
            'dashboard.view',
            'agents.view',
            'structures.view',
            'affectations.view',
            'documents.view', 'documents.download',
            'reports.view',
        ],
        RoleName::CONSULTATION->value => [
            'dashboard.view',
            'agents.view',
            'structures.view',
            'affectations.view',
            'documents.view',
            'reports.view',
        ],
        RoleName::AGENT_INDIVIDUEL->value => [
            'dashboard.view',
            'documents.view', 'documents.download',
        ],
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
            } elseif (! empty($attribution)) {
                $role->syncPermissions($attribution);
            }
        }

        $this->command->info('✓ ' . count($toutes) . ' permissions et ' . count(RoleName::cases()) . ' rôles créés.');
    }
}
