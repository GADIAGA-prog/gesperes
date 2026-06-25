@extends('layouts.app')
@section('title', 'Carrière')
@section('header', 'Carrière et mouvement — Carrière')

@section('content')
@include('carriere-mouvement._tabs')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $evenements->total() }} acte(s) enregistré(s)</p>
    @can('carriere.manage')
        <a href="{{ route('carriere.create') }}" class="btn btn-primary">+ Nouvel acte</a>
    @endcan
</div>

{{-- Tri (clic en-tête) + filtre par colonne, traités côté serveur. --}}
<form method="GET">
    <input type="hidden" name="tri" value="{{ request('tri') }}">
    <input type="hidden" name="sens" value="{{ request('sens') }}">
    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <x-tri.entete cle="date_effet">Date d'effet</x-tri.entete>
                    <x-tri.entete cle="agent">Agent</x-tri.entete>
                    <th class="table-head">Type</th>
                    <th class="table-head">Changement</th>
                    <x-tri.entete cle="reference">Référence</x-tri.entete>
                </tr>
                {{-- Ligne de filtres par colonne --}}
                <tr class="bg-gray-50/70">
                    <th class="px-3 py-1.5"></th>
                    <th class="px-3 py-1.5 font-normal">
                        <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="agent…"
                               class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400">
                    </th>
                    <th class="px-3 py-1.5 font-normal">
                        <select name="type" class="w-full rounded border-gray-200 text-xs py-1 px-2 font-normal">
                            <option value="">Tous</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(($filtres['type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <x-tri.filtre cle="description" placeholder="changement…" />
                    <x-tri.filtre cle="reference" placeholder="réf…" />
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($evenements as $e)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $e->date_effet?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('agents.show', $e->agent_id) }}" class="font-medium text-institution-700 hover:underline">
                                {{ $e->agent?->nom_complet ?? '—' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge {{ $e->type?->color() }}">{{ $e->type?->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $e->description ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $e->reference_acte ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun acte de carrière.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-2 flex gap-2">
        <button type="submit" class="btn btn-primary text-sm">Filtrer</button>
        <a href="{{ route('carriere.index') }}" class="btn btn-secondary text-sm">Réinitialiser</a>
    </div>
</form>

<div class="mt-4">{{ $evenements->links() }}</div>
@endsection
