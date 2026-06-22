<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Models\Agent;
use App\Models\Document;
use App\Models\Structure;
use App\Models\User;
use App\Policies\AgentPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\StructurePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Agent::class     => AgentPolicy::class,
        Structure::class => StructurePolicy::class,
        Document::class  => DocumentPolicy::class,
        User::class      => UserPolicy::class,
    ];

    public function boot(): void
    {
        // Le Super Admin reçoit toutes les permissions.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(RoleName::SUPER_ADMIN->value) ? true : null;
        });
    }
}
