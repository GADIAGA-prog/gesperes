@extends('layouts.app')
@section('title', 'Cartographie des postes')
@section('header', 'Outils GRH — Cartographie des postes par structure')

@section('content')
@include('outils-grh._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $total }} poste(s) décrit(s) — répertoire par structure (guide §V)</p>
    <a href="{{ route('fiches-poste.index') }}" class="btn btn-secondary text-sm">← Liste des fiches</a>
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <select name="structure_id" class="input sm:col-span-3" data-recherche>
        <option value="">Toutes les structures</option>
        @foreach ($structures as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['structure_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Filtrer</button>
</form>
@include('partials.select-recherche')

@forelse ($groupes as $chemin => $fiches)
    <div class="card mb-4">
        <h3 class="font-semibold text-gray-700 mb-3 flex items-center justify-between">
            <span>{{ $chemin }}</span>
            <span class="text-xs font-normal text-gray-400">{{ $fiches->count() }} poste(s)</span>
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="table-head">Code</th>
                        <th class="table-head">Intitulé du poste</th>
                        <th class="table-head">Type</th>
                        <th class="table-head">Emploi requis</th>
                        <th class="table-head text-right">Titulaires</th>
                        <th class="table-head text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($fiches as $f)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-xs">{{ $f->code ?: '—' }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('fiches-poste.show', $f) }}" class="text-institution-700 hover:underline">{{ $f->intitule }}</a>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $f->type_poste?->label() }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $f->emploi?->libelle ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $f->titulaires_count }}</td>
                            <td class="px-4 py-2 text-right"><span class="badge {{ $f->statut?->color() }}">{{ $f->statut?->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card text-center text-gray-400 py-8">Aucune fiche de poste à cartographier.</div>
@endforelse
@endsection
