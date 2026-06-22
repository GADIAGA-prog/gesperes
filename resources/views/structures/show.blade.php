@extends('layouts.app')
@section('title', $structure->libelle)
@section('header', $structure->libelle)
@section('content')
<div class="mb-4 text-sm text-gray-500">{{ $structure->cheminComplet() }}</div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Informations</h3>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-gray-500">Code</dt><dd class="font-mono">{{ $structure->code }}</dd>
            <dt class="text-gray-500">Type</dt><dd>{{ $structure->type?->label() }}</dd>
            <dt class="text-gray-500">Région</dt><dd>{{ $structure->region ?: '—' }}</dd>
            <dt class="text-gray-500">Province</dt><dd>{{ $structure->province ?: '—' }}</dd>
            <dt class="text-gray-500">Responsable</dt><dd>{{ $structure->responsable?->nom_complet ?: '—' }}</dd>
            <dt class="text-gray-500">Agents rattachés</dt><dd>{{ $structure->agents_count }}</dd>
        </dl>
        @can('structures.update')
            <a href="{{ route('structures.edit', $structure) }}" class="btn btn-primary mt-4">Modifier</a>
        @endcan
    </div>
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Sous-structures ({{ $structure->enfants->count() }})</h3>
        @forelse ($structure->enfants as $enfant)
            <a href="{{ route('structures.show', $enfant) }}" class="block py-1.5 text-sm text-institution-700 hover:underline">{{ $enfant->libelle }}</a>
        @empty
            <p class="text-sm text-gray-400">Aucune sous-structure.</p>
        @endforelse
    </div>
</div>
@endsection
