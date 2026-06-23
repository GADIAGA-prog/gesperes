@extends('layouts.app')
@section('title', 'Nouvel acte de carrière')
@section('header', 'Enregistrer un acte de carrière')

@section('content')
<form method="POST" action="{{ route('carriere.store') }}" class="card max-w-3xl"
      x-data="{ type: '{{ old('type') }}' }">
    @csrf

    <div id="carriere-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-form.select name="agent_id" label="Agent"
            :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.$a->nom_complet])"
            :selected="$agentSel" required />
        <x-form.select name="type" label="Type d'acte" :options="$types" :selected="old('type')" required x-model="type" />
        <x-form.input name="date_effet" label="Date d'effet" type="date" :value="old('date_effet')" required />
        <div class="hidden sm:block"></div>

        {{-- Avancement d'échelon --}}
        <template x-if="type === 'avancement_echelon'">
            <x-form.select name="nouvel_echelon_id" label="Nouvel échelon" :options="$echelons" :selected="old('nouvel_echelon_id')" />
        </template>

        {{-- Changement de classe --}}
        <template x-if="type === 'avancement_classe'">
            <div class="contents">
                <x-form.select name="nouvelle_classe_id" label="Nouvelle classe" :options="$classes" :selected="old('nouvelle_classe_id')" />
                <x-form.select name="nouvel_echelon_id" label="Nouvel échelon" :options="$echelons" :selected="old('nouvel_echelon_id')" placeholder="— Inchangé —" />
            </div>
        </template>

        {{-- Promotion (catégorie / échelle) --}}
        <template x-if="type === 'promotion'">
            <div class="contents">
                <x-form.select name="nouvelle_categorie_id" label="Nouvelle catégorie" :options="$categories" :selected="old('nouvelle_categorie_id')" />
                <x-form.select name="nouvelle_echelle_id" label="Nouvelle échelle" :options="$echelles" :selected="old('nouvelle_echelle_id')" />
                <x-form.select name="nouvelle_classe_id" label="Nouvelle classe" :options="$classes" :selected="old('nouvelle_classe_id')" placeholder="— Inchangée —" />
                <x-form.select name="nouvel_echelon_id" label="Nouvel échelon" :options="$echelons" :selected="old('nouvel_echelon_id')" placeholder="— Inchangé —" />
            </div>
        </template>

        {{-- Nomination (fonction / poste) --}}
        <template x-if="type === 'nomination'">
            <div class="contents">
                <x-form.select name="nouvelle_fonction_id" label="Nouvelle fonction" :options="$fonctions" :selected="old('nouvelle_fonction_id')" />
                <x-form.select name="nouveau_poste_id" label="Nouveau poste" :options="$postes" :selected="old('nouveau_poste_id')" placeholder="— Aucun —" />
            </div>
        </template>

        {{-- Changement de position administrative --}}
        <template x-if="type === 'changement_position'">
            <x-form.select name="nouvelle_position_id" label="Nouvelle position administrative" :options="$positions" :selected="old('nouvelle_position_id')" />
        </template>

        <x-form.input name="reference_acte" label="Référence de l'acte" :value="old('reference_acte')" />
        <x-form.textarea name="observation" label="Observation" :value="old('observation')" class="sm:col-span-2" />

        <p class="text-xs text-gray-400 sm:col-span-2">
            L'indice est recalculé automatiquement depuis la grille (catégorie × échelle × classe × échelon),
            et la date de retraite est réévaluée si la catégorie change. La situation courante de l'agent est mise à jour.
        </p>
    </div>

    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('carriere.index') }}" class="btn btn-secondary">Annuler</a>
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
    // Saisie intelligente sur la liste (longue) des agents.
    const agent = document.getElementById('agent_id');
    if (agent && ! agent.tomselect) new TomSelect(agent, { allowEmptyOption: true, create: false });
});
</script>
@endpush
