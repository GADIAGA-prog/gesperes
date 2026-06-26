@extends('layouts.app')
@section('title', 'Fiches de poste')
@section('header', 'Outils GRH — Fiches de poste')

@section('content')
@include('outils-grh._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $fiches->total() }} fiche(s) de poste</p>
    <div class="flex gap-2">
        <a href="{{ route('fiches-poste.cartographie') }}" class="btn btn-secondary">Cartographie par structure</a>
        @can('fiches-poste.manage')
            <a href="{{ route('fiches-poste.create') }}" class="btn btn-primary">+ Nouvelle fiche de poste</a>
        @endcan
    </div>
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Intitulé ou code…" class="input">
    <select name="structure_id" class="input" data-recherche>
        <option value="">Toutes les structures</option>
        @foreach ($structures as $id => $lib)
            <option value="{{ $id }}" {{ (string) ($filtres['structure_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $lib }}</option>
        @endforeach
    </select>
    <select name="famille_professionnelle_id" class="input" data-recherche>
        <option value="">Toutes les familles pro.</option>
        @foreach ($familles as $id => $lib)
            <option value="{{ $id }}" {{ (string) ($filtres['famille_professionnelle_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $lib }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <select name="statut" class="input">
            <option value="">Tous les statuts</option>
            @foreach ($statuts as $statut)
                <option value="{{ $statut->value }}" {{ ($filtres['statut'] ?? '') === $statut->value ? 'selected' : '' }}>{{ $statut->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filtrer</button>
    </div>
</form>
@include('partials.select-recherche')

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Code</th>
                <th class="table-head">Intitulé</th>
                <th class="table-head">Type</th>
                <th class="table-head">Unité administrative</th>
                <th class="table-head">Statut</th>
                <th class="table-head text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($fiches as $fiche)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-sm">{{ $fiche->code ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('fiches-poste.show', $fiche) }}" class="font-medium text-institution-700 hover:underline">{{ $fiche->intitule }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $fiche->type_poste?->label() }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $fiche->structure?->libelle ?? '—' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $fiche->statut?->color() }}">{{ $fiche->statut?->label() }}</span></td>
                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                        <a href="{{ route('fiches-poste.show', $fiche) }}" class="text-gray-500 hover:text-institution-600">Voir</a>
                        <a href="{{ route('fiches-poste.pdf', $fiche) }}" class="ml-2 text-gray-500 hover:text-institution-600">PDF</a>
                        @can('fiches-poste.manage')
                            <a href="{{ route('fiches-poste.edit', $fiche) }}" class="ml-2 text-gray-500 hover:text-institution-600">Modifier</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune fiche de poste. Commencez par renseigner les référentiels (familles professionnelles, emplois-types).</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $fiches->links() }}</div>
@endsection
