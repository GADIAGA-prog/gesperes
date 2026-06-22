<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool { return $user->can('users.view'); }
    public function view(User $user, User $model): bool { return $user->can('users.view'); }
    public function create(User $user): bool { return $user->can('users.create'); }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.update');
    }

    /**
     * Interdit l'auto-suppression et la suppression du dernier administrateur.
     */
    public function delete(User $user, User $model): bool
    {
        if (! $user->can('users.delete')) {
            return false;
        }
        if ($user->id === $model->id) {
            return false; // pas d'auto-suppression
        }
        if ($model->estSuperAdmin() && User::role(\App\Enums\RoleName::SUPER_ADMIN->value)->count() <= 1) {
            return false; // pas de suppression du dernier super-admin
        }
        return true;
    }
}
