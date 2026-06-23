@extends('layouts.app')
@section('title', 'Sorties définitives')
@section('header', 'Situation des sorties définitives')

@section('content')
@include('mouvements._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $agents->total() }} agent(s) en sortie définitive</p>
    @can('mouvements.manage')
        <a href="{{ route('mouvements.create') }}" class="btn btn-primary">+ Nouveau mouvement</a>
    @endcan
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <select name="nature" class="input">
        <option value="">Toutes les natures</option>
        @foreach ($natures as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['nature'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <div class="sm:col-span-2 flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('mouvements.sorties-definitives') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">N°</th>
                <th class="table-head">Mle</th>
                <th class="table-head">Nom</th>
                <th class="table-head">Prénoms</th>
                <th class="table-head">Sexe</th>
                <th class="table-head">Emploi</th>
                <th class="table-head">Nature de la sortie</th>
                <th class="table-head">Date de sortie</th>
                <th class="table-head">Réf. de l'acte</th>
                <th class="table-head">Alerte</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($agents as $agent)
                <tr class="hover:bg-gray-50 {{ $agent->en_alerte ? 'bg-amber-50' : '' }}">
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $agents->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3 text-sm font-mono whitespace-nowrap">{{ $agent->matricule ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('agents.show', $agent->id) }}" class="font-medium text-institution-700 hover:underline">{{ $agent->nom ?? '—' }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $agent->prenoms ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->sexe?->value ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->emploi?->libelle ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge bg-red-100 text-red-700">{{ $agent->nature_sortie ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $agent->date_sortie?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $agent->reference_acte ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                        @if ($agent->en_alerte)
                            <span class="badge bg-amber-100 text-amber-800">⚠ Retraite {{ $agent->date_sortie?->format('Y') }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">Aucun agent en sortie définitive.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $agents->links() }}</div>
@endsection
