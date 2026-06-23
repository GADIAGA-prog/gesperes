@extends('layouts.app')
@section('title','Discipline')
@section('header','Évaluation — Dossiers disciplinaires')
@section('content')
@include('evaluation._tabs')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $dossiers->total() }} dossier(s)</p>
    @can('discipline.manage')<a href="{{ route('discipline.create') }}" class="btn btn-primary">+ Nouvel acte</a>@endcan
</div>
<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <select name="type" class="input">
        <option value="">Tous les types</option>
        @foreach($types as $v=>$l)<option value="{{ $v }}" {{ ($filtres['type']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
    </select>
    <select name="statut" class="input">
        <option value="">Tous les statuts</option>
        <option value="ouvert" {{ ($filtres['statut']??'')==='ouvert'?'selected':'' }}>Ouvert</option>
        <option value="clos" {{ ($filtres['statut']??'')==='clos'?'selected':'' }}>Clos</option>
    </select>
    <button class="btn btn-primary">Filtrer</button>
</form>
<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Date</th><th class="table-head">Agent</th><th class="table-head">Type</th>
            <th class="table-head">Motif</th><th class="table-head">Statut</th><th class="table-head text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($dossiers as $d)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $d->date_acte?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm"><a href="{{ route('agents.show',$d->agent_id) }}" class="text-institution-700 hover:underline">{{ $d->agent?->nom_complet }}</a></td>
                <td class="px-4 py-3"><span class="badge {{ $d->type?->color() }}">{{ $d->type?->label() }}</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($d->motif, 60) }}</td>
                <td class="px-4 py-3 text-sm">{{ ucfirst($d->statut) }}</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    @can('discipline.manage')
                        <a href="{{ route('discipline.edit',$d) }}" class="text-institution-600 hover:underline">Modifier</a>
                        <button onclick="if(confirm('Supprimer ?'))document.getElementById('dd-{{ $d->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                        <form id="dd-{{ $d->id }}" method="POST" action="{{ route('discipline.destroy',$d) }}" class="hidden">@csrf @method('DELETE')</form>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun dossier.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $dossiers->links() }}</div>
@endsection
