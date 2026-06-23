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

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <select name="type" class="input">
        <option value="">Tous les types</option>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" {{ ($filtres['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="sm:col-span-2 flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('carriere.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Date d'effet</th>
                <th class="table-head">Agent</th>
                <th class="table-head">Type</th>
                <th class="table-head">Changement</th>
                <th class="table-head">Référence</th>
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

<div class="mt-4">{{ $evenements->links() }}</div>
@endsection
