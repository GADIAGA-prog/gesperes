@extends('layouts.app')
@section('title', 'Affectations')
@section('header', 'Mouvements du personnel')
@section('content')
@include('mouvements._tabs')

<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $affectations->total() }} mouvement(s)</p>
    @can('affectations.create')
        <a href="{{ route('affectations.create') }}" class="btn btn-primary">+ Nouvelle affectation</a>
    @endcan
</div>
<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Date d'effet</th><th class="table-head">Agent</th>
            <th class="table-head">De</th><th class="table-head">Vers</th>
            <th class="table-head">Référence</th><th class="table-head text-right"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($affectations as $aff)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">{{ $aff->date_effet?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm font-medium">{{ $aff->agent?->nom_complet }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $aff->ancienneStructure?->libelle ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $aff->nouvelleStructure?->libelle ?: '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $aff->reference_acte ?: '—' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('affectations.show', $aff) }}" class="text-sm text-institution-600 hover:underline">Détail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune affectation.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $affectations->links() }}</div>
@endsection
