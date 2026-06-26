<?php

namespace App\Http\Controllers\EspaceAgent;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Connexion de l'espace agent par MATRICULE + mot de passe (les agents ne
 * disposent pas d'adresse e-mail de connexion). Distincte de la connexion du
 * personnel d'administration, qui reste par e-mail.
 */
class AgentSessionController extends Controller
{
    public function create(): View
    {
        return view('espace-agent.auth.connexion');
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'matricule' => ['required', 'string'],
            'password'  => ['required', 'string'],
        ], [], ['matricule' => 'matricule', 'password' => 'mot de passe']);

        $this->ensureIsNotRateLimited($request);

        $agent = Agent::whereRaw('UPPER(matricule) = ?', [mb_strtoupper(trim($donnees['matricule']))])->first();
        $user  = $agent?->user;

        if (! $user
            || ! $user->actif
            || ! $user->hasRole(RoleName::AGENT_INDIVIDUEL->value)
            || ! Hash::check($donnees['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'matricule' => 'Matricule ou mot de passe incorrect.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        activity('auth')->causedBy($user)->log('Connexion (espace agent)');

        return redirect()->intended(route('espace-agent.dashboard'));
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $secondes = RateLimiter::availableIn($this->throttleKey($request));
        throw ValidationException::withMessages([
            'matricule' => "Trop de tentatives. Réessayez dans " . ceil($secondes / 60) . " minute(s).",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return 'connexion-agent|' . Str::lower($request->input('matricule')) . '|' . $request->ip();
    }
}
