@extends('layouts.app')
@section('title','Besoins de formation')
@section('header','Besoins de formation')
@section('content')
@include('outils-grh._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $besoins->total() }} besoin(s) recueilli(s)</p>
    @can('formations.manage')<a href="{{ route('besoins-formation.create') }}" class="btn btn-primary">+ Nouveau besoin</a>@endcan
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-5 gap-3">
    <select name="annee" class="input">
        <option value="">Toutes années</option>
        @foreach($annees as $a)<option value="{{ $a }}" {{ (string)($filtres['annee']??'')===(string)$a?'selected':'' }}>{{ $a }}</option>@endforeach
    </select>
    <select name="domaine" class="input">
        <option value="">Tous domaines</option>
        @foreach($domaines as $v=>$l)<option value="{{ $v }}" {{ ($filtres['domaine']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
    </select>
    <select name="statut" class="input">
        <option value="">Tous statuts</option>
        @foreach(['exprime'=>'Exprimé','retenu'=>'Retenu','rejete'=>'Rejeté','planifie'=>'Planifié'] as $v=>$l)
            <option value="{{ $v }}" {{ ($filtres['statut']??'')===$v?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="structure_id" class="input">
        <option value="">Toutes structures</option>
        @foreach($structures as $id=>$lib)<option value="{{ $id }}" {{ (string)($filtres['structure_id']??'')===(string)$id?'selected':'' }}>{{ $lib }}</option>@endforeach
    </select>
    <button class="btn btn-primary">Filtrer</button>
</form>

{{-- Consolidation : besoins les plus exprimés (priorisation pour le plan) --}}
@if($consolidation->isNotEmpty())
<div class="card mb-4">
    <h3 class="font-semibold text-gray-800 mb-3">Consolidation — thèmes les plus demandés</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($consolidation as $c)
            <span class="badge bg-institution-50 text-institution-700 border border-institution-200">
                {{ $c->theme_souhaite }} <span class="font-bold ml-1">×{{ $c->total }}</span>
            </span>
        @endforeach
    </div>
</div>
@endif

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Année</th>
            <th class="table-head">Agent</th>
            <th class="table-head">Structure</th>
            <th class="table-head">Thème souhaité</th>
            <th class="table-head">Domaine</th>
            <th class="table-head">Statut</th>
            <th class="table-head text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($besoins as $b)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2">{{ $b->annee_recueil }}</td>
                <td class="px-3 py-2">{{ $b->agent?->nom_complet ?? '—' }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $b->structure?->libelle ?? '—' }}</td>
                <td class="px-3 py-2">{{ $b->theme_souhaite }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $b->domaine?->label() ?? '—' }}</td>
                <td class="px-3 py-2">{{ ucfirst($b->statut) }}</td>
                <td class="px-3 py-2 text-right whitespace-nowrap">
                    @can('formations.manage')
                        <a href="{{ route('besoins-formation.edit',$b) }}" class="text-institution-600 hover:underline">Modifier</a>
                        <button onclick="if(confirm('Supprimer ?'))document.getElementById('del-b-{{ $b->id }}').submit()" class="ml-1 text-red-500 hover:underline">Suppr.</button>
                        <form id="del-b-{{ $b->id }}" method="POST" action="{{ route('besoins-formation.destroy',$b) }}" class="hidden">@csrf @method('DELETE')</form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-3 py-8 text-center text-gray-400">Aucun besoin recueilli.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $besoins->links() }}</div>
@endsection
