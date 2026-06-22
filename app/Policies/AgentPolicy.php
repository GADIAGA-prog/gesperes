<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    public function viewAny(User $user): bool { return $user->can('agents.view'); }
    public function view(User $user, Agent $agent): bool { return $user->can('agents.view'); }
    public function create(User $user): bool { return $user->can('agents.create'); }
    public function update(User $user, Agent $agent): bool { return $user->can('agents.update'); }
    public function delete(User $user, Agent $agent): bool { return $user->can('agents.delete'); }
    public function import(User $user): bool { return $user->can('agents.import'); }
    public function export(User $user): bool { return $user->can('agents.export'); }
}
