@extends('layouts.app')
@section('title', 'Structures')
@section('header', 'Gestion des effectifs — Organigramme des structures')

@section('content')
@include('gestion-effectifs._tabs')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $structures->count() }} structure(s)</p>
    @can('structures.create')
        <a href="{{ route('structures.create') }}" class="btn btn-primary">+ Nouvelle structure</a>
    @endcan
</div>

{{-- Recherche : libellé, code ou responsable. Active = liste plate ; vide = organigramme. --}}
<form method="GET" action="{{ route('structures.index') }}" class="card mb-4 flex gap-2">
    <input type="text" name="q" value="{{ $q }}" autocomplete="off"
           placeholder="Rechercher une structure : libellé, code, responsable…" class="input flex-1">
    <button type="submit" class="btn btn-primary">Rechercher</button>
    @if ($q !== '')
        <a href="{{ route('structures.index') }}" class="btn btn-secondary">Réinitialiser</a>
    @endif
</form>

@if ($q !== '')
    {{-- ===== Résultats de recherche (liste plate) ===== --}}
    <p class="text-sm text-gray-500 mb-3">{{ $resultats->count() }} résultat(s) pour « {{ $q }} »{{ $resultats->count() === 100 ? ' (100 premiers)' : '' }}</p>
    <div class="card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <th class="table-head">Type</th>
                    <th class="table-head">Structure</th>
                    <th class="table-head">Rattachement</th>
                    <th class="table-head">Responsable</th>
                    <th class="table-head text-right">Agents</th>
                    <th class="table-head text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($resultats as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><span class="inline-flex h-6 items-center rounded bg-institution-50 px-2 text-[11px] font-medium text-institution-700">{{ $s->type?->label() }}</span></td>
                        <td class="px-4 py-3">
                            <a href="{{ route('structures.show', $s) }}" class="font-medium text-institution-700 hover:underline">{{ $s->libelle }}</a>
                            <span class="ml-1 text-xs text-gray-400 font-mono">{{ $s->code }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $s->parent?->cheminComplet() ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $s->responsable?->nom_complet ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600">{{ $s->agents_count }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ route('structures.show', $s) }}" class="text-gray-500 hover:text-institution-600">Voir</a>
                            @can('structures.update')<a href="{{ route('structures.edit', $s) }}" class="ml-2 text-gray-500 hover:text-institution-600">Modifier</a>@endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune structure trouvée pour « {{ $q }} ».</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    {{-- ===== Organigramme (arborescence) ===== --}}
    <div class="card">
        @if ($racines->isEmpty())
            <p class="text-center text-gray-400 py-8">Aucune structure enregistrée.</p>
        @else
            <ul class="space-y-1">
                @foreach ($racines as $racine)
                    @include('structures._noeud', ['noeud' => $racine, 'tous' => $structures, 'niveau' => 0])
                @endforeach
            </ul>
        @endif
    </div>
@endif
@endsection
