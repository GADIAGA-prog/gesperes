@extends('layouts.app')
@section('title', 'Agents')
@section('header', 'Gestion des effectifs — Agents')

@section('content')
@include('gestion-effectifs._tabs')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $agents->total() }} agent(s) enregistré(s)</p>
    <div class="flex gap-2">
        @can('agents.import')
            <a href="{{ route('agents.import.form') }}" class="btn btn-secondary">Importer</a>
        @endcan
        @can('agents.export')
            <a href="{{ route('agents.export', request()->query()) }}" class="btn btn-secondary">Exporter Excel</a>
            <a href="{{ route('agents.export.pdf', request()->query()) }}" class="btn btn-secondary">Exporter PDF</a>
        @endcan
        @can('agents.create')
            <a href="{{ route('agents.create') }}" class="btn btn-primary">+ Nouvel agent</a>
        @endcan
    </div>
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Matricule, nom, prénoms…" class="input sm:col-span-2">
    <select name="region" class="input">
        <option value="">Toutes les régions</option>
        @foreach ($regions as $region)
            <option value="{{ $region }}" {{ ($filtres['region'] ?? '') === $region ? 'selected' : '' }}>{{ $region }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <select name="statut_dossier" class="input">
            <option value="">Tous les statuts</option>
            @foreach ($statuts as $statut)
                <option value="{{ $statut->value }}" {{ ($filtres['statut_dossier'] ?? '') === $statut->value ? 'selected' : '' }}>{{ $statut->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filtrer</button>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Matricule</th>
                <th class="table-head">Nom & prénoms</th>
                <th class="table-head">Emploi</th>
                <th class="table-head">Structure</th>
                <th class="table-head">Région</th>
                <th class="table-head">Dossier</th>
                <th class="table-head text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($agents as $agent)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-sm">{{ $agent->matricule }}{{ $agent->cle }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('agents.show', $agent) }}" class="font-medium text-institution-700 hover:underline">{{ $agent->nom_complet }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->emploi?->libelle ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->structure?->libelle ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $agent->region ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $agent->statut_dossier?->color() }}">{{ $agent->statut_dossier?->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('agents.show', $agent) }}" class="text-gray-500 hover:text-institution-600">Voir</a>
                        @can('agents.update')
                            <a href="{{ route('agents.edit', $agent) }}" class="ml-2 text-gray-500 hover:text-institution-600">Modifier</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun agent trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $agents->links() }}</div>
@endsection
