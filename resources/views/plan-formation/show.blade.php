@extends('layouts.app')
@section('title', $plan->intitule)
@section('header', 'Plan de formation — ' . $plan->intitule)
@section('content')
@include('outils-grh._tabs')

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        <span class="badge {{ $plan->statut?->color() }}">{{ $plan->statut?->label() }}</span>
        <span class="text-sm text-gray-500">Période {{ $plan->periode }}</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('plan-formation.index') }}" class="btn btn-secondary">Retour</a>
        <a href="{{ route('plan-formation.pdf', $plan) }}" class="btn btn-secondary">PDF</a>
        @can('formations.manage')
            <a href="{{ route('plan-formation.edit', $plan) }}" class="btn btn-secondary">Modifier</a>
            <button onclick="if(confirm('Supprimer ce plan et ses programmes ?'))document.getElementById('del-plan').submit()" class="btn btn-danger">Supprimer</button>
            <form id="del-plan" method="POST" action="{{ route('plan-formation.destroy', $plan) }}" class="hidden">@csrf @method('DELETE')</form>
        @endcan
    </div>
</div>

{{-- Vision / finalité / objectifs --}}
@if($plan->vision || $plan->finalite || $plan->objectifs)
<div class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
    <div><p class="text-gray-400 mb-1">Vision</p><p>{{ $plan->vision ?? '—' }}</p></div>
    <div><p class="text-gray-400 mb-1">Finalité</p><p class="whitespace-pre-line">{{ $plan->finalite ?? '—' }}</p></div>
    <div><p class="text-gray-400 mb-1">Objectifs</p><p class="whitespace-pre-line">{{ $plan->objectifs ?? '—' }}</p></div>
</div>
@endif

{{-- Ajout d'un programme annuel --}}
@can('formations.manage')
<form method="POST" action="{{ route('plan-formation.programmes.store', $plan) }}" class="card mb-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">@csrf
    <x-form.input name="annee" label="Année" type="number" min="2000" max="2100" :value="old('annee', $plan->annee_fin)" required />
    <x-form.input name="objectif_strategique" label="Objectif stratégique" :value="old('objectif_strategique')" class="sm:col-span-2" />
    <x-form.input name="budget_previsionnel" label="Budget prévisionnel" type="number" min="0" step="0.01" :value="old('budget_previsionnel', 0)" required />
    <input type="hidden" name="statut" value="brouillon">
    <button class="btn btn-primary">+ Programme annuel</button>
</form>
@endcan

{{-- Programmes annuels --}}
@forelse($plan->programmes as $programme)
    @php $t = $synthese[$programme->id]; @endphp
    <div class="card mb-4" x-data="{ editing: null }">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 mb-3">
            <div>
                <h3 class="font-semibold text-institution-800">Programme {{ $programme->annee }}</h3>
                @if($programme->objectif_strategique)<p class="text-sm text-gray-500">{{ $programme->objectif_strategique }}</p>@endif
            </div>
            <div class="flex items-center gap-4 text-sm">
                <div class="text-right">
                    <p class="text-gray-400">Budget prévisionnel</p>
                    <p class="font-medium">{{ number_format($programme->budget_previsionnel, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400">Coût des actions</p>
                    <p class="font-medium {{ $t['cout'] > $programme->budget_previsionnel && $programme->budget_previsionnel > 0 ? 'text-red-600' : '' }}">{{ number_format($t['cout'], 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400">Agents / Jours</p>
                    <p class="font-medium">{{ number_format($t['agents'], 0, ',', ' ') }} / {{ $t['jours'] }}</p>
                </div>
                @can('formations.manage')
                <button onclick="if(confirm('Supprimer ce programme et ses actions ?'))document.getElementById('del-prog-{{ $programme->id }}').submit()" class="text-red-500 hover:underline">Suppr.</button>
                <form id="del-prog-{{ $programme->id }}" method="POST" action="{{ route('plan-formation.programmes.destroy', [$plan, $programme]) }}" class="hidden">@csrf @method('DELETE')</form>
                @endcan
            </div>
        </div>

        {{-- Tableau synthétique des actions --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead><tr class="text-left text-xs uppercase text-gray-500">
                    <th class="table-head">N°</th>
                    <th class="table-head">Action / Thème</th>
                    <th class="table-head">Modalité</th>
                    <th class="table-head">Stratégie</th>
                    <th class="table-head">Public cible</th>
                    <th class="table-head text-right">Jours</th>
                    <th class="table-head text-right">Agents</th>
                    <th class="table-head text-right">Coût</th>
                    <th class="table-head">Réalisation</th>
                    <th class="table-head">Statut</th>
                    @can('formations.manage')<th class="table-head text-right">Actions</th>@endcan
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($programme->actions as $action)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">{{ $action->numero_ordre }}</td>
                        <td class="px-3 py-2">
                            <p class="font-medium">{{ $action->action }}</p>
                            @if($action->theme_module)<p class="text-xs text-gray-400">{{ $action->theme_module }}</p>@endif
                        </td>
                        <td class="px-3 py-2">{{ $action->type_modalite?->label() }}</td>
                        <td class="px-3 py-2">@if($action->strategie)<span class="badge {{ $action->strategie->color() }}">{{ $action->strategie->label() }}</span>@endif</td>
                        <td class="px-3 py-2 text-xs text-gray-600">{{ $action->public_cible_label ?: '—' }}</td>
                        <td class="px-3 py-2 text-right">{{ $action->nombre_jours }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($action->nombre_agents, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">{{ number_format($action->cout, 0, ',', ' ') }}</td>
                        <td class="px-3 py-2">
                            <span class="text-xs">{{ $action->agents_formes ?? 0 }}/{{ $action->nombre_agents }}</span>
                            <span class="badge {{ ($action->taux_realisation ?? 0) >= 100 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $action->taux_realisation ?? 0 }}%</span>
                        </td>
                        <td class="px-3 py-2"><span class="badge {{ $action->statut?->color() }}">{{ $action->statut?->label() }}</span></td>
                        @can('formations.manage')
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            <button type="button" @click="editing = (editing === {{ $action->id }} ? null : {{ $action->id }})" class="text-institution-600 hover:underline">Modifier</button>
                            <button type="button" onclick="if(confirm('Supprimer cette action ?'))document.getElementById('del-act-{{ $action->id }}').submit()" class="ml-1 text-red-500 hover:underline">Suppr.</button>
                            <form id="del-act-{{ $action->id }}" method="POST" action="{{ route('actions-formation.destroy', $action) }}" class="hidden">@csrf @method('DELETE')</form>
                        </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-3 py-6 text-center text-gray-400">Aucune action planifiée pour ce programme.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @can('formations.manage')
        {{-- Zone d'édition / ajout (formulaires hors tableau pour un HTML valide) --}}
        <div class="mt-3">
            <button type="button" x-show="editing === null" @click="editing = 'new'" class="btn btn-secondary">+ Ajouter une action</button>

            <div x-show="editing === 'new'" x-cloak class="mt-3">
                @include('plan-formation._action_form', ['programme' => $programme, 'action' => null, 'enums' => $enums])
            </div>

            @foreach($programme->actions as $action)
                <div x-show="editing === {{ $action->id }}" x-cloak class="mt-3">
                    @include('plan-formation._action_form', ['programme' => $programme, 'action' => $action, 'enums' => $enums])
                </div>
            @endforeach
        </div>
        @endcan
    </div>
@empty
    <div class="card text-center text-gray-400">Aucun programme annuel. Ajoutez-en un ci-dessus.</div>
@endforelse
@endsection
