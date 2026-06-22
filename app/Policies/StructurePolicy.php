<?php

namespace App\Policies;

use App\Models\Structure;
use App\Models\User;

class StructurePolicy
{
    public function viewAny(User $user): bool { return $user->can('structures.view'); }
    public function view(User $user, Structure $s): bool { return $user->can('structures.view'); }
    public function create(User $user): bool { return $user->can('structures.create'); }
    public function update(User $user, Structure $s): bool { return $user->can('structures.update'); }
    public function delete(User $user, Structure $s): bool { return $user->can('structures.delete'); }
}
