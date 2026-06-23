@extends('layouts.app')
@section('title', 'Budget des structures')
@section('header', 'Budget du personnel & de fonctionnement')
@section('content')
@include('budget._tabs')

<h2 class="text-lg font-semibold text-gray-800 mb-3">Dépenses de fonctionnement</h2>

<div class="flex items-center justify-between gap-2 mb-4">
    <div>
        @if (($totaux['anomalies'] ?? 0) > 0)
            <span class="badge bg-amber-100 text-amber-800">⚠ {{ $totaux['anomalies'] }} activité(s) à vérifier</span>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('budget.par', ['exercice' => $filtres['exercice'] ?? now()->year, 'structure_id' => $filtres['structure_id'] ?? null]) }}"
           target="_blank" class="btn btn-secondary">PAR en PDF</a>
        @can('budget.manage')
            <a href="{{ route('budget.import.form') }}" class="btn btn-secondary">⬆ Importer un PDF-PAR</a>
            <a href="{{ route('budget.create') }}" class="btn btn-primary">+ Nouvelle activité</a>
        @endcan
    </div>
</div>

{{-- Totaux --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="card"><p class="text-xs text-gray-500">Activités</p><p class="text-2xl font-bold text-institution-700">{{ number_format($totaux['activites'], 0, ',', ' ') }}</p></div>
    <div class="card"><p class="text-xs text-gray-500">Montant planifié</p><p class="text-xl font-bold text-gray-800">{{ number_format($totaux['montant'], 0, ',', ' ') }} F</p></div>
    <div class="card"><p class="text-xs text-gray-500">Total AE</p><p class="text-xl font-bold text-amber-700">{{ number_format($totaux['ae'], 0, ',', ' ') }} F</p></div>
    <div class="card"><p class="text-xs text-gray-500">Total CP</p><p class="text-xl font-bold text-green-700">{{ number_format($totaux['cp'], 0, ',', ' ') }} F</p></div>
</div>

{{-- Filtres --}}
<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <x-form.select name="exercice" label="Exercice" :options="$exercices->mapWithKeys(fn($e)=>[$e=>$e])" :selected="$filtres['exercice'] ?? null" placeholder="Tous" />
    <x-form.select name="programme_id" label="Programme" :options="$programmes" :selected="$filtres['programme_id'] ?? null" placeholder="Tous" />
    <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$filtres['structure_id'] ?? null" placeholder="Toutes" />
    <div class="flex items-end gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('budget.index') }}" class="btn btn-secondary">Réinit.</a>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Code</th>
            <th class="table-head">Activité</th>
            <th class="table-head">Programme / Action</th>
            <th class="table-head">Structure</th>
            <th class="table-head text-right">Montant</th>
            <th class="table-head text-right">AE</th>
            <th class="table-head text-right">CP</th>
            <th class="table-head text-right"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($activites as $a)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 text-sm font-mono">{{ $a->code }}</td>
                    <td class="px-4 py-2.5 text-sm">{{ \Illuminate\Support\Str::limit($a->libelle, 50) }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600">{{ $a->action?->programme?->code }} · {{ $a->action?->code }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600">{{ $a->structure?->libelle ?: $a->libelle_chapitre ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-sm text-right">{{ number_format($a->montant, 0, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-sm text-right text-amber-700">{{ number_format($a->total_ae, 0, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-sm text-right text-green-700">{{ number_format($a->total_cp, 0, ',', ' ') }}</td>
                    <td class="px-4 py-2.5 text-right"><a href="{{ route('budget.show', $a) }}" class="text-sm text-institution-600 hover:underline">Détail</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucune activité budgétaire.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $activites->links() }}</div>
@endsection
