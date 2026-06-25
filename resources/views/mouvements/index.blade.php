@extends('layouts.app')
@section('title', 'Mouvements')
@section('header', 'Mouvements du personnel')

@php
    $coul = [
        'activite' => 'bg-green-100 text-green-700',
        'sortie_temporaire' => 'bg-amber-100 text-amber-800',
        'sortie_definitive' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
@include('mouvements._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $mouvements->total() }} mouvement(s)</p>
    @can('mouvements.manage')
        <a href="{{ route('mouvements.create') }}" class="btn btn-primary">+ Nouveau mouvement</a>
    @endcan
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}"
           placeholder="Rechercher un agent : matricule, nom, prénoms, emploi, structure…" class="input">
    <select name="famille" class="input">
        <option value="">Toutes les familles</option>
        @foreach ($familles as $value => $label)
            <option value="{{ $value }}" {{ ($filtres['famille'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('mouvements.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Date d'effet</th>
                <th class="table-head">Agent</th>
                <th class="table-head">Mouvement</th>
                <th class="table-head">Famille</th>
                <th class="table-head">Fin prévue</th>
                <th class="table-head">Référence</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($mouvements as $m)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $m->date_effet?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('agents.show', $m->agent_id) }}" class="font-medium text-institution-700 hover:underline">{{ $m->agent?->nom_complet ?? '—' }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $m->anciennePosition?->libelle ?? '—' }} <span class="text-gray-400">→</span> <span class="font-medium text-gray-800">{{ $m->nouvellePosition?->libelle ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $coul[$m->famille?->value] ?? 'bg-gray-100 text-gray-700' }}">{{ $m->famille?->label() ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $m->date_fin?->format('d/m/Y') ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $m->reference_acte ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun mouvement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $mouvements->links() }}</div>
@endsection
