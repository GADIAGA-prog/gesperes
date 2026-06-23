@extends('layouts.app')
@section('title', 'Congés')
@section('header', 'Contrôle présence — Congés & autorisations d\'absence')
@section('content')
@include('controle-presence._tabs')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $conges->total() }} demande(s)</p>
    @can('conges.request')
        <a href="{{ route('conges.create', $filtres) }}" class="btn btn-primary">+ Nouvelle demande</a>
    @endcan
</div>

{{-- Filtres --}}
<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <x-form.select name="agent_id" label="Agent"
        :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.trim($a->nom.' '.$a->prenoms)])"
        :selected="$filtres['agent_id'] ?? null" placeholder="Tous les agents" />
    <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$filtres['statut'] ?? null" placeholder="Tous les statuts" />
    <div class="flex items-end gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('conges.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

{{-- Solde de l'agent sélectionné --}}
@if ($solde && $agent)
    <div class="card mb-4">
        <h3 class="font-semibold text-gray-700 mb-3">
            Solde {{ $solde['annee'] }} — {{ trim($agent->nom.' '.$agent->prenoms) }}
        </h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-lg bg-institution-50 p-3">
                <p class="text-xs text-gray-500">Congé annuel</p>
                <p class="text-2xl font-bold text-institution-700">{{ $solde['solde_conge'] }}<span class="text-sm font-normal text-gray-400">/{{ $solde['droit_conge'] }} j</span></p>
                <p class="text-xs text-gray-400">Consommé : {{ $solde['conge_consomme'] }} j</p>
            </div>
            <div class="rounded-lg bg-amber-50 p-3">
                <p class="text-xs text-gray-500">Autorisations</p>
                <p class="text-2xl font-bold text-amber-700">{{ $solde['solde_autorisation'] }}<span class="text-sm font-normal text-gray-400">/{{ $solde['droit_autorisation'] }} j</span></p>
                <p class="text-xs text-gray-400">Consommé : {{ $solde['autorisation_consommee'] }} j</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-xs text-gray-500">Dépassement reporté</p>
                <p class="text-2xl font-bold text-gray-700">{{ $solde['depassement_autorisation'] }} <span class="text-sm font-normal text-gray-400">j</span></p>
                <p class="text-xs text-gray-400">déduit du congé annuel</p>
            </div>
            <div class="rounded-lg bg-red-50 p-3">
                <p class="text-xs text-gray-500">Absences injustifiées</p>
                <p class="text-2xl font-bold text-red-700">{{ rtrim(rtrim(number_format($solde['jours_injustifies'],2,'.',''),'0'),'.') }} <span class="text-sm font-normal text-gray-400">j</span></p>
                <p class="text-xs text-gray-400">Justifiées : {{ rtrim(rtrim(number_format($solde['jours_justifies'],2,'.',''),'0'),'.') }} j</p>
            </div>
        </div>
        @if ($solde['depassement_autorisation'] > 0)
            <p class="mt-3 text-xs text-amber-700 bg-amber-50 rounded px-3 py-2">
                ⚠ Le quota de {{ $solde['droit_autorisation'] }} jours d'autorisation est dépassé : {{ $solde['depassement_autorisation'] }} jour(s) ont été déduits du congé annuel.
            </p>
        @endif
    </div>
@endif

{{-- Liste des demandes --}}
<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Agent</th>
            <th class="table-head">Nature</th>
            <th class="table-head">Période</th>
            <th class="table-head">Jours</th>
            <th class="table-head">Statut</th>
            <th class="table-head text-right"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($conges as $conge)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $conge->agent?->matricule }} — {{ trim($conge->agent?->nom.' '.$conge->agent?->prenoms) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $conge->motifAbsence?->libelle ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $conge->date_debut?->format('d/m/Y') }} → {{ $conge->date_fin?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $conge->nombre_jours }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $conge->statut->badge() }}">{{ $conge->statut->label() }}</span></td>
                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                        @if ($conge->statut === \App\Enums\StatutConge::DEMANDE)
                            @can('conges.validate')
                                <form method="POST" action="{{ route('conges.valider', $conge) }}" class="inline">@csrf
                                    <button class="text-green-600 hover:underline">Valider</button>
                                </form>
                                <form method="POST" action="{{ route('conges.refuser', $conge) }}" class="inline ml-2">@csrf
                                    <button class="text-red-500 hover:underline">Refuser</button>
                                </form>
                            @endcan
                            @can('conges.request')
                                <form method="POST" action="{{ route('conges.annuler', $conge) }}" class="inline ml-2">@csrf
                                    <button class="text-gray-500 hover:underline">Annuler</button>
                                </form>
                            @endcan
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune demande.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $conges->links() }}</div>
@endsection
