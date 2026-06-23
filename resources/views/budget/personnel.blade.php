@extends('layouts.app')
@section('title', 'Dépenses du personnel')
@section('header', 'Budget — Dépenses du personnel')

@php $fmt = fn ($v) => $v ? number_format($v, 0, ',', ' ') : '—'; @endphp

@section('content')
@include('budget._tabs')

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-5 gap-3">
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Matricule, nom, prénoms…" class="input">
    <select name="structure_id" class="input">
        <option value="">Toutes les structures</option>
        @foreach ($structures as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['structure_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <select name="emploi_id" class="input">
        <option value="">Tous les emplois</option>
        @foreach ($emplois as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['emploi_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <select name="categorie_id" class="input">
        <option value="">Toutes catégories</option>
        @foreach ($categories as $id => $code)
            <option value="{{ $id }}" {{ (string) ($filtres['categorie_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $code }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('budget.personnel') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

<p class="text-sm text-gray-500 mb-3">{{ number_format($agents->total(), 0, ',', ' ') }} agent(s). Montants en FCFA. CARFO = retenue agent (hors total). « Action » : rattachement structure à venir.</p>

<div class="card overflow-x-auto">
    <table class="min-w-full text-xs whitespace-nowrap">
        <thead>
            <tr class="text-left uppercase tracking-wide text-gray-500 border-b border-gray-200">
                <th class="table-head">N°</th>
                <th class="table-head">Mle</th>
                <th class="table-head">Nom</th>
                <th class="table-head">Prénoms</th>
                <th class="table-head">Sexe</th>
                <th class="table-head">Emploi</th>
                <th class="table-head">Poste / Service</th>
                <th class="table-head">Action</th>
                <th class="table-head">Cat-Éch-Cl-Éch.</th>
                <th class="table-head text-right">Indice</th>
                <th class="table-head text-right">Résidence</th>
                <th class="table-head text-right">Solde ind.</th>
                <th class="table-head text-right">Respons.</th>
                <th class="table-head text-right">Alloc. fam.</th>
                <th class="table-head text-right">Logement</th>
                <th class="table-head text-right">Astreinte</th>
                <th class="table-head text-right">Spécifique</th>
                <th class="table-head text-right">Technicité</th>
                <th class="table-head text-right">Autres</th>
                <th class="table-head text-right">CARFO</th>
                <th class="table-head text-right">Total/mois</th>
                <th class="table-head text-right">Total annuel</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($agents as $agent)
                @php $p = $agent->paie; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-500">{{ $agents->firstItem() + $loop->index }}</td>
                    <td class="px-3 py-2 font-mono">{{ $agent->matricule }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('agents.show', $agent->id) }}" class="font-medium text-institution-700 hover:underline">{{ $agent->nom }}</a>
                    </td>
                    <td class="px-3 py-2">{{ $agent->prenoms }}</td>
                    <td class="px-3 py-2">{{ $agent->sexe?->value }}</td>
                    <td class="px-3 py-2">{{ $agent->emploi?->libelle ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $agent->etablissement ?: ($agent->poste?->libelle ?? '—') }}</td>
                    <td class="px-3 py-2" title="{{ $agent->structure?->action?->libelle }}">{{ $agent->structure?->action?->code ?? '—' }}</td>
                    <td class="px-3 py-2 font-mono">{{ $agent->categorie?->code ?? '?' }}-{{ $agent->echelle?->code ?? '?' }}-{{ $agent->classe?->code ?? '?' }}-{{ $agent->echelon?->code ?? '?' }}</td>
                    <td class="px-3 py-2 text-right">{{ $p['indice'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['residence']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['solde']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['responsabilite']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['allocation']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['logement']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['astreinte']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['specifique']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['technicite']) }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($p['autres']) }}</td>
                    <td class="px-3 py-2 text-right text-red-600">{{ $fmt($p['carfo']) }}</td>
                    <td class="px-3 py-2 text-right font-semibold">{{ $fmt($p['total_mois']) }}</td>
                    <td class="px-3 py-2 text-right font-semibold">{{ $fmt($p['total_annuel']) }}</td>
                </tr>
            @empty
                <tr><td colspan="22" class="px-4 py-8 text-center text-gray-400">Aucun agent.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $agents->links() }}</div>
@endsection
