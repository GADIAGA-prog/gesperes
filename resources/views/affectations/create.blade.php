@extends('layouts.app')
@section('title', 'Nouvelle affectation')
@section('header', 'Créer une affectation')
@section('content')
@include('mouvements._tabs')

<form method="POST" action="{{ route('affectations.store') }}" class="card max-w-3xl">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-form.select name="agent_id" label="Agent" :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.$a->nom_complet])" required />
        <x-form.input name="date_effet" label="Date d'effet" type="date" required />
        <x-form.select name="nouvelle_structure_id" label="Nouvelle structure" :options="$structures" required />
        <x-form.select name="nouvelle_fonction_id" label="Nouvelle fonction" :options="$fonctions" />
        <x-form.input name="reference_acte" label="Référence de l'acte" />
        <x-form.textarea name="motif" label="Motif" class="sm:col-span-2" />
    </div>
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('affectations.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
