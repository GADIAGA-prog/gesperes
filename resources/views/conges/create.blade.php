@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('header', 'Demande de congé / autorisation d\'absence')
@section('content')
<form method="POST" action="{{ route('conges.store') }}" class="card max-w-3xl">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-form.select name="agent_id" label="Agent"
            :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.trim($a->nom.' '.$a->prenoms)])"
            :selected="$agentPreselect" required class="sm:col-span-2" />

        <x-form.select name="motif_absence_id" label="Nature"
            :options="$motifs->mapWithKeys(fn($m)=>[$m->id => $m->libelle.' ('.$m->categorie->label().')'])" required />
        <x-form.input name="nombre_jours" label="Nombre de jours (auto si vide)" type="number" />

        <x-form.input name="date_debut" label="Du" type="date" required />
        <x-form.input name="date_fin" label="Au" type="date" required />

        <x-form.input name="reference_decision" label="Référence décision / acte" class="sm:col-span-2" />
        <x-form.textarea name="motif" label="Motif / observation" class="sm:col-span-2" />
    </div>

    <p class="mt-4 text-xs text-gray-500">
        Droits annuels : {{ \App\Services\SoldeCongeService::DROIT_CONGE_ANNUEL }} jours de congé,
        {{ \App\Services\SoldeCongeService::DROIT_AUTORISATION }} jours d'autorisation d'absence.
        Au-delà des autorisations, le dépassement est déduit du congé annuel.
        Si le nombre de jours est laissé vide, il est calculé automatiquement (jours ouvrés).
    </p>

    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('conges.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer la demande</button>
    </div>
</form>
@endsection
