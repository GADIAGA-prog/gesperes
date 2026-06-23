@extends('layouts.app')
@section('title','Performance')
@section('header','Évaluation — Performance')
@section('content')
@include('evaluation._tabs')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $evaluations->total() }} évaluation(s)</p>
    @can('performance.manage')<a href="{{ route('performance.create') }}" class="btn btn-primary">+ Nouvelle évaluation</a>@endcan
</div>
<form method="GET" class="card mb-4 flex gap-2">
    <select name="periode" class="input sm:w-48">
        <option value="">Toutes les périodes</option>
        @foreach($periodes as $p)<option value="{{ $p }}" {{ (string)($filtres['periode']??'')===(string)$p?'selected':'' }}>{{ $p }}</option>@endforeach
    </select>
    <button class="btn btn-primary">Filtrer</button>
</form>
<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Période</th><th class="table-head">Agent</th><th class="table-head">Note</th>
            <th class="table-head">Évaluateur</th><th class="table-head">Statut</th><th class="table-head text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($evaluations as $e)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">{{ $e->periode }}</td>
                <td class="px-4 py-3 text-sm"><a href="{{ route('agents.show',$e->agent_id) }}" class="text-institution-700 hover:underline">{{ $e->agent?->nom_complet }}</a></td>
                <td class="px-4 py-3 text-sm font-medium">{{ $e->note !== null ? rtrim(rtrim(number_format($e->note,2),'0'),'.').' / 20' : '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $e->evaluateur?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm">{{ $e->statut === 'valide' ? 'Validée' : 'Brouillon' }}</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    @can('performance.manage')
                        <a href="{{ route('performance.edit',$e) }}" class="text-institution-600 hover:underline">Modifier</a>
                        <button onclick="if(confirm('Supprimer ?'))document.getElementById('ev-{{ $e->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                        <form id="ev-{{ $e->id }}" method="POST" action="{{ route('performance.destroy',$e) }}" class="hidden">@csrf @method('DELETE')</form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune évaluation.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $evaluations->links() }}</div>
@endsection
