@extends('layouts.agent-auth')
@section('title', 'Créer mon espace')

@section('content')
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-800">Créer mon espace</h2>
    <p class="text-sm text-slate-500">Vérifiez votre identité avec 3 informations de votre dossier, puis choisissez un mot de passe.</p>
</div>

<form method="POST" action="{{ route('espace-agent.inscription.store') }}" class="space-y-4">
    @csrf

    <div>
        <label for="matricule" class="label">Matricule</label>
        <input id="matricule" type="text" name="matricule" value="{{ old('matricule') }}" required autofocus class="input" placeholder="Votre matricule">
        @error('matricule')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="telephone" class="label">Numéro de téléphone</label>
        <input id="telephone" type="tel" name="telephone" value="{{ old('telephone') }}" required inputmode="tel" class="input" placeholder="Ex. 70 00 00 00">
        @error('telephone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="date_naissance" class="label">Date de naissance</label>
        <input id="date_naissance" type="date" name="date_naissance" value="{{ old('date_naissance') }}" required class="input">
        @error('date_naissance')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="!mt-6 border-t border-slate-200 pt-5">
        <label for="password" class="label">Choisir un mot de passe</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="input" placeholder="••••••••">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="label">Confirmer le mot de passe</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input" placeholder="••••••••">
    </div>

    <button type="submit" class="btn-primary w-full py-2.5">Activer mon espace</button>
</form>

<p class="mt-6 text-center text-sm text-slate-500">
    Vous avez déjà un compte ?
    <a href="{{ route('espace-agent.connexion') }}" class="font-semibold text-institution-700 hover:underline">Se connecter</a>
</p>
@endsection
