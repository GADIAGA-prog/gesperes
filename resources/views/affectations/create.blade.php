@extends('layouts.app')
@section('title', 'Nouvelle affectation')
@section('header', 'Créer une affectation')
@section('content')
@include('mouvements._tabs')

<form method="POST" action="{{ route('affectations.store') }}" class="card max-w-3xl"
      x-data="{
        ancienne: null,
        charger(id) {
            if (! id) { this.ancienne = null; return; }
            fetch(`{{ route('affectations.situation', 'AGENT_ID') }}`.replace('AGENT_ID', id), { headers: { Accept: 'application/json' } })
                .then((r) => r.json()).then((d) => this.ancienne = d).catch(() => this.ancienne = null);
        }
      }">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-form.select name="agent_id" label="Agent"
            :options="$agents->mapWithKeys(fn($a) => [$a->id => $a->matricule.' — '.$a->nom_complet])"
            required @change="charger($event.target.value)" />
        <x-form.input name="date_effet" label="Date d'effet" type="date" required />

        {{-- Ancienne affectation (situation actuelle de l'agent) --}}
        <div class="sm:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-3" x-show="ancienne" x-cloak>
            <p class="text-xs font-semibold uppercase text-gray-500">Ancienne affectation</p>
            <p class="text-sm text-gray-700">Structure : <span class="font-medium" x-text="ancienne?.structure || '—'"></span></p>
            <p class="text-sm text-gray-700">Fonction : <span x-text="ancienne?.fonction || '—'"></span>
                <span class="text-gray-400" x-show="ancienne?.date_affectation"> · depuis le <span x-text="ancienne?.date_affectation"></span></span>
            </p>
        </div>

        {{-- Nouvelle structure : cascade hiérarchique (parent → … → service/poste) --}}
        @include('partials.cascade-structure', [
            'nom' => 'nouvelle_structure_id',
            'config' => $cascade,
            'selected' => old('nouvelle_structure_id'),
            'label' => 'Nouvelle structure',
        ])

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
