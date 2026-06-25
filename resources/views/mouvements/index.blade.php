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

{{-- Tri (clic en-tête) + filtre par colonne, traités côté serveur sur toute la base. --}}
<form method="GET">
    <input type="hidden" name="tri" value="{{ request('tri') }}">
    <input type="hidden" name="sens" value="{{ request('sens') }}">
    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <x-tri.entete cle="date_effet">Date d'effet</x-tri.entete>
                    <x-tri.entete cle="agent">Agent</x-tri.entete>
                    <th class="table-head">Mouvement</th>
                    <th class="table-head">Famille</th>
                    <x-tri.entete cle="date_fin">Fin prévue</x-tri.entete>
                    <x-tri.entete cle="reference">Référence</x-tri.entete>
                </tr>
                {{-- Ligne de filtres par colonne --}}
                <tr class="bg-gray-50/70">
                    <th class="px-3 py-1.5"></th>
                    <th class="px-3 py-1.5 font-normal">
                        <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}"
                               placeholder="agent…"
                               class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400">
                    </th>
                    <th class="px-3 py-1.5"></th>
                    <th class="px-3 py-1.5 font-normal">
                        <select name="famille" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal">
                            <option value="">Toutes</option>
                            @foreach ($familles as $value => $label)
                                <option value="{{ $value }}" @selected(($filtres['famille'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-3 py-1.5"></th>
                    <x-tri.filtre cle="reference" placeholder="réf…" />
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
    <div class="mt-2 flex gap-2">
        <button type="submit" class="btn btn-primary text-sm">Filtrer</button>
        <a href="{{ route('mouvements.index') }}" class="btn btn-secondary text-sm">Réinitialiser</a>
    </div>
</form>

<div class="mt-4">{{ $mouvements->links() }}</div>
@endsection
