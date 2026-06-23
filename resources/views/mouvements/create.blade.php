@extends('layouts.app')
@section('title', 'Nouveau mouvement')
@section('header', 'Enregistrer un mouvement')

@section('content')
<form method="POST" action="{{ route('mouvements.store') }}" class="card max-w-3xl">
    @csrf
    <div id="mouvement-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-form.select name="agent_id" label="Agent"
            :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.$a->nom_complet])"
            :selected="$agentSel" required />
        <x-form.select name="nouvelle_position_id" label="Nouvelle position administrative" :options="$positions" :selected="old('nouvelle_position_id')" required />
        <x-form.input name="date_effet" label="Date d'effet" type="date" :value="old('date_effet')" required />
        <x-form.input name="date_fin" label="Fin prévue (sortie temporaire)" type="date" :value="old('date_fin')" />
        <x-form.input name="reference_acte" label="Référence de l'acte" :value="old('reference_acte')" />
        <x-form.textarea name="motif" label="Motif / observation" :value="old('motif')" class="sm:col-span-2" />
        <p class="text-xs text-gray-400 sm:col-span-2">
            La position cible détermine la famille du mouvement (Activité, Sortie temporaire, Sortie définitive)
            et met à jour la situation courante de l'agent ; une sortie définitive le retire de l'effectif actif.
        </p>
    </div>
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('mouvements.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof TomSelect === 'undefined') return;
    ['agent_id', 'nouvelle_position_id'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el && ! el.tomselect) new TomSelect(el, { allowEmptyOption: true, create: false });
    });
});
</script>
@endpush
