@extends('layouts.app')
@section('title', 'Mon profil')
@section('header', 'Mon profil')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-4xl">

    {{-- Informations du compte --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-4">Informations du compte</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')
            <x-form.input name="name" label="Nom complet" :value="auth()->user()->name" required />
            <x-form.input name="email" label="Adresse e-mail" type="email" :value="auth()->user()->email" required />
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>

    {{-- Modification du mot de passe --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-4">Modifier le mot de passe</h3>
        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <x-form.input name="current_password" label="Mot de passe actuel" type="password" required />
            <x-form.input name="password" label="Nouveau mot de passe" type="password" required />
            <x-form.input name="password_confirmation" label="Confirmation" type="password" required />
            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
        </form>
    </div>

</div>
@endsection
