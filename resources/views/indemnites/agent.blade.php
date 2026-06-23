@extends('layouts.app')
@section('title', 'Indemnités · ' . $agent->nom_complet)
@section('header', 'Indemnités : ' . $agent->nom_complet)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <a href="{{ route('agents.show', $agent) }}" class="text-sm text-institution-600 hover:underline">← Retour à la fiche agent</a>
    <div class="flex gap-2">
        <a href="{{ route('agents.indemnites.bulletin', $agent) }}" class="btn btn-secondary">Bulletin PDF</a>
        @can('indemnites.manage')
            <form method="POST" action="{{ route('agents.indemnites.figer', $agent) }}">@csrf
                <button type="submit" class="btn btn-primary">Calculer &amp; figer les barèmes</button>
            </form>
        @endcan
    </div>
</div>

<div class="card mb-6">
    <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">Indemnités calculées (barème décret 2014-427)</h3>
        <span class="text-sm">Total estimé : <strong>{{ number_format($totalCalcule, 0, ',', ' ') }} FCFA</strong></span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach ($calculees as $c)
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">{{ $c['indemnite']->libelle }}</p>
                <p class="text-lg font-semibold text-gray-800">{{ number_format($c['montant'], 0, ',', ' ') }} F</p>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mt-3">
        Calcul automatique selon l'emploi, la zone (localité), la catégorie, l'échelle et le caractère enseignant/en classe de l'agent.
        Renseignez ces champs pour fiabiliser le calcul.
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card overflow-x-auto">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-700">Indemnités attribuées</h3>
            <span class="text-sm">Total mensuel actif : <strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></span>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Indemnité</th><th class="table-head">Montant</th>
                <th class="table-head">Période</th><th class="table-head">État</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($attributions as $att)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $att->indemnite?->libelle ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-medium">{{ number_format($att->montant, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $att->date_debut?->format('d/m/Y') ?: '—' }}@if ($att->date_fin) → {{ $att->date_fin->format('d/m/Y') }}@endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="badge {{ $att->actif ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">{{ $att->actif ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('indemnites.manage')
                                <button onclick="if(confirm('Retirer cette indemnité ?')) document.getElementById('del-att-{{ $att->id }}').submit()" class="text-red-500 hover:underline">Retirer</button>
                                <form id="del-att-{{ $att->id }}" method="POST" action="{{ route('indemnites.attributions.destroy', $att) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucune indemnité attribuée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('indemnites.manage')
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Attribuer une indemnité</h3>
        @if ($indemnites->isEmpty())
            <p class="text-sm text-gray-400">Définissez d'abord des indemnités dans le <a href="{{ route('indemnites.index') }}" class="text-institution-600 hover:underline">référentiel</a>.</p>
        @else
        <form method="POST" action="{{ route('agents.indemnites.attribuer', $agent) }}" class="space-y-3">
            @csrf
            <x-form.select name="indemnite_id" label="Indemnité" :options="$indemnites->pluck('libelle','id')" required />
            <x-form.input name="montant" label="Montant (laisser vide = calcul auto)" type="number" step="0.01" />
            <div class="grid grid-cols-2 gap-2">
                <x-form.input name="date_debut" label="Début" type="date" />
                <x-form.input name="date_fin" label="Fin" type="date" />
            </div>
            <x-form.textarea name="observation" label="Observation" rows="2" />
            <button type="submit" class="btn btn-primary w-full justify-center">Attribuer</button>
        </form>
        @endif
    </div>
    @endcan
</div>
@endsection
