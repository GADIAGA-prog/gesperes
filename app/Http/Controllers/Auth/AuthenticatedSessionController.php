<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        activity('auth')->causedBy($user)->log('Connexion');

        // Un agent individuel (self-service) rejoint son espace personnel ;
        // le personnel d'administration rejoint le tableau de bord RH.
        if ($user->hasRole(RoleName::AGENT_INDIVIDUEL->value)) {
            return redirect()->intended(route('espace-agent.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        activity('auth')->causedBy(Auth::user())->log('Déconnexion');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
