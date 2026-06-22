<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('users.view');

        $roles = Role::withCount('users')->with('permissions')->orderBy('name')->get();

        return view('roles.index', ['roles' => $roles]);
    }

    public function edit(Role $role): View
    {
        $this->authorize('users.update');

        return view('roles.edit', [
            'role'        => $role,
            'groupes'     => Permissions::GROUPS,
            'attribuees'  => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('users.update');

        $data = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // Le super-admin conserve toujours toutes les permissions
        if ($role->name === \App\Enums\RoleName::SUPER_ADMIN->value) {
            return back()->with('error', 'Le rôle Super Admin dispose de tous les droits et ne peut être restreint.');
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', "Droits du rôle « {$role->name} » mis à jour.");
    }
}
