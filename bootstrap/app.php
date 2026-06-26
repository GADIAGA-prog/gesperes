<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias des middlewares Spatie Permission + espace agent
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'agent.individuel'   => \App\Http\Middleware\EstAgentIndividuel::class,
        ]);

        // Les invités de l'espace agent (PWA) sont dirigés vers LEUR connexion
        // (par matricule), pas vers la connexion du personnel d'administration.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('espace-agent*')
            ? route('espace-agent.connexion')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
