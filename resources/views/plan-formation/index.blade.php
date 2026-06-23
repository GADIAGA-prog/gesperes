@extends('layouts.app')
@section('title','Plans de formation')
@section('header','Plan de formation')
@section('content')
@include('outils-grh._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $plans->total() }} plan(s) de formation</p>
    @can('formations.manage')<a href="{{ route('plan-formation.create') }}" class="btn btn-primary">+ Nouveau plan</a>@endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @forelse($plans as $plan)
        @php
            $coutTotal = $plan->programmes->flatMap->actions->sum('cout');
            $agents    = $plan->programmes->flatMap->actions->sum('nombre_agents');
        @endphp
        <a href="{{ route('plan-formation.show', $plan) }}" class="card hover:shadow-md transition block">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-institution-800">{{ $plan->intitule }}</p>
                    <p class="text-sm text-gray-500">Période {{ $plan->periode }} · {{ $plan->programmes_count }} programme(s)</p>
                </div>
                <span class="badge {{ $plan->statut?->color() }}">{{ $plan->statut?->label() }}</span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-400">Coût prévisionnel</span><br><span class="font-medium">{{ number_format($coutTotal, 0, ',', ' ') }} FCFA</span></div>
                <div><span class="text-gray-400">Agents à former</span><br><span class="font-medium">{{ number_format($agents, 0, ',', ' ') }}</span></div>
            </div>
        </a>
    @empty
        <div class="card text-center text-gray-400 lg:col-span-2">Aucun plan de formation. Créez-en un pour commencer.</div>
    @endforelse
</div>
<div class="mt-4">{{ $plans->links() }}</div>
@endsection
