<?php

namespace App\Http\Controllers\EspaceAgent;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\EspaceAgent\InscriptionAgentRequest;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Inscription self-service d'un agent, sans email : il prouve son identité par
 * la concordance de trois informations de son dossier — matricule, numéro de
 * téléphone et date de naissance — puis définit immédiatement son mot de passe.
 *
 * L'agent se connecte ensuite avec son matricule (l'adresse e-mail du compte est
 * synthétique et n'est jamais utilisée comme identifiant).
 */
class InscriptionController extends Controller
{
    public function create(): View
    {
        return view('espace-agent.auth.inscription');
    }

    public function store(InscriptionAgentRequest $request): RedirectResponse
    {
        $this->limiterCadence($request->ip());

        $agent = $this->retrouverAgent(
            matricule: $request->input('matricule'),
            telephone: $request->input('telephone'),
            dateNaissance: $request->date('date_naissance')?->format('Y-m-d'),
        );

        // Erreur générique : ne révèle pas quel champ est en cause.
        if (! $agent) {
            throw ValidationException::withMessages([
                'matricule' => "Les informations saisies ne correspondent à aucun dossier agent éligible. Vérifiez votre matricule, votre téléphone et votre date de naissance.",
            ]);
        }

        if ($agent->user && $agent->user->actif) {
            throw ValidationException::withMessages([
                'matricule' => "Un compte actif existe déjà pour cet agent. Connectez-vous avec votre matricule.",
            ]);
        }

        $user = $this->activerCompte($agent, $request->input('password'));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('espace-agent.dashboard')
            ->with('success', 'Votre espace agent est activé. Bienvenue !');
    }

    /**
     * Recherche l'agent dont matricule + téléphone + date de naissance
     * concordent et qui n'a pas encore de compte actif. La comparaison du
     * téléphone est faite sur les chiffres uniquement (tolérance aux espaces et
     * à l'indicatif +226).
     */
    private function retrouverAgent(string $matricule, string $telephone, ?string $dateNaissance): ?Agent
    {
        if (! $dateNaissance) {
            return null;
        }

        $telSaisi = $this->chiffres($telephone);

        return Agent::query()
            ->whereRaw('UPPER(matricule) = ?', [mb_strtoupper(trim($matricule))])
            ->whereDate('date_naissance', $dateNaissance)
            ->get()
            ->first(function (Agent $a) use ($telSaisi) {
                $telDossier = $this->chiffres((string) $a->telephone);
                if ($telDossier === '' || $telSaisi === '') {
                    return false;
                }
                // Égalité stricte, ou concordance des 8 derniers chiffres (indicatif).
                return $telDossier === $telSaisi
                    || (strlen($telDossier) >= 8 && strlen($telSaisi) >= 8
                        && substr($telDossier, -8) === substr($telSaisi, -8));
            });
    }

    /** Crée (ou réactive) le compte agent avec un email synthétique unique. */
    private function activerCompte(Agent $agent, string $motDePasse): User
    {
        return DB::transaction(function () use ($agent, $motDePasse) {
            $user = $agent->user;

            if (! $user) {
                $user = User::create([
                    'name'              => $agent->nom_complet,
                    'email'             => 'agent-' . mb_strtolower($agent->matricule) . '@gesperes.local',
                    'password'          => Hash::make($motDePasse),
                    'actif'             => true,
                    'email_verified_at' => now(),
                ]);
                $agent->update(['user_id' => $user->id]);
            } else {
                $user->forceFill([
                    'password'          => Hash::make($motDePasse),
                    'actif'             => true,
                    'email_verified_at' => now(),
                ])->save();
            }

            if (! $user->hasRole(RoleName::AGENT_INDIVIDUEL->value)) {
                $user->assignRole(RoleName::AGENT_INDIVIDUEL->value);
            }

            return $user;
        });
    }

    private function chiffres(string $valeur): string
    {
        return preg_replace('/\D+/', '', $valeur) ?? '';
    }

    private function limiterCadence(?string $ip): void
    {
        $cle = 'inscription-agent|' . $ip;

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            $secondes = RateLimiter::availableIn($cle);
            throw ValidationException::withMessages([
                'matricule' => "Trop de tentatives. Réessayez dans " . ceil($secondes / 60) . " minute(s).",
            ]);
        }

        RateLimiter::hit($cle, 600); // 5 tentatives / 10 minutes
    }
}
