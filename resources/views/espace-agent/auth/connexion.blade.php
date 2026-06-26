@extends('layouts.agent-auth')
@section('title', 'Connexion')

@section('content')
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-800">Connexion</h2>
    <p class="text-sm text-slate-500">Identifiez-vous avec votre matricule</p>
</div>

<form method="POST" action="{{ route('espace-agent.connexion.store') }}" class="space-y-4">
    @csrf
    <div>
        <label for="matricule" class="label">Matricule</label>
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
            <input id="matricule" type="text" name="matricule" value="{{ old('matricule') }}" required autofocus inputmode="text" class="input pl-10" placeholder="Votre matricule">
        </div>
        @error('matricule')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password" class="label">Mot de passe</label>
        <div class="relative" x-data="{ show:false }">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" class="input px-10" placeholder="••••••••">
            <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.88 9.88"/></svg>
            </button>
        </div>
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" class="rounded border-slate-300 text-institution-600 focus:ring-institution-600">
        Rester connecté
    </label>

    <button type="submit" class="btn-primary w-full py-2.5">Se connecter</button>
</form>

<div class="mt-6 space-y-3 text-center text-sm">
    <p class="text-slate-500">
        Première connexion ?
        <a href="{{ route('espace-agent.inscription') }}" class="font-semibold text-institution-700 hover:underline">Créer mon espace</a>
    </p>
    <a href="{{ route('login') }}" class="block text-xs text-slate-400 hover:underline">Accès personnel d'administration</a>
</div>
@endsection
