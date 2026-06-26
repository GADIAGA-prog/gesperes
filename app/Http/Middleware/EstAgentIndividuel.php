<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloisonne l'espace agent (self-service) : seul un compte « agent individuel »
 * effectivement rattaché à un dossier agent peut y accéder. La règle est basée
 * sur le RÔLE (et non une permission Spatie) afin que le Gate::before du
 * super-admin ne l'ouvre pas par inadvertance.
 */
class EstAgentIndividuel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole(RoleName::AGENT_INDIVIDUEL->value)
                && $user->agent()->exists(),
            403,
            "Accès réservé aux agents disposant d'un compte personnel rattaché."
        );

        return $next($request);
    }
}
