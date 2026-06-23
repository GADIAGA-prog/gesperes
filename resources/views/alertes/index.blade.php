@extends('layouts.app')
@section('title', 'Alertes RH')
@section('header', 'Outils GRH — Alertes RH')

@section('content')
@include('outils-grh._tabs')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $nonLues }} alerte(s) non lue(s)</p>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('alertes.generer') }}">@csrf<button class="btn btn-primary">Générer les alertes</button></form>
        @if ($nonLues > 0)
            <form method="POST" action="{{ route('alertes.tout-lu') }}">@csrf<button class="btn btn-secondary">Tout marquer lu</button></form>
        @endif
    </div>
</div>

@if ($notifications->isNotEmpty())
<div class="card mb-6">
    <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Notifications</h3>
    <div class="divide-y divide-gray-50">
        @foreach ($notifications as $notif)
            @php $coul = ['danger'=>'bg-red-100 text-red-700','warning'=>'bg-amber-100 text-amber-800','info'=>'bg-blue-100 text-blue-700']; @endphp
            <div class="flex items-center gap-3 py-2 {{ $notif->lu ? 'opacity-50' : '' }}">
                <span class="badge {{ $coul[$notif->niveau] ?? 'bg-gray-100 text-gray-700' }} shrink-0">{{ $notif->titre }}</span>
                <span class="text-sm text-gray-700 flex-1">{{ $notif->message }}</span>
                <span class="text-xs text-gray-400 shrink-0">{{ $notif->created_at?->diffForHumans() }}</span>
                @unless ($notif->lu)
                    <form method="POST" action="{{ route('alertes.lu', $notif) }}">@csrf<button class="text-xs text-institution-600 hover:underline">Marquer lu</button></form>
                @endunless
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card border-l-4 border-amber-400">
        <p class="text-sm text-gray-500">Proches de la retraite</p>
        <p class="text-2xl font-bold text-gray-800">{{ $retraites->count() }}</p>
        <p class="text-xs text-gray-400">≤ {{ $moisRetraite }} mois</p>
    </div>
    <div class="card border-l-4 border-red-500">
        <p class="text-sm text-gray-500">Documents expirés</p>
        <p class="text-2xl font-bold text-gray-800">{{ $docsExpires->count() }}</p>
    </div>
    <div class="card border-l-4 border-orange-400">
        <p class="text-sm text-gray-500">Documents bientôt expirés</p>
        <p class="text-2xl font-bold text-gray-800">{{ $docsBientot->count() }}</p>
        <p class="text-xs text-gray-400">≤ {{ $joursDoc }} jours</p>
    </div>
</div>

<div class="card mb-6">
    <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Agents proches de la retraite</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Agent</th><th class="table-head">Catégorie</th>
                <th class="table-head">Structure</th><th class="table-head">Date de retraite</th><th class="table-head">Échéance</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($retraites as $a)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm"><a href="{{ route('agents.show', $a) }}" class="text-institution-700 hover:underline">{{ $a->nom_complet }}</a></td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $a->categorie?->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $a->structure?->libelle ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm font-medium">{{ $a->date_retraite?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $a->date_retraite?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Aucun agent proche de la retraite.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach (['Documents expirés' => $docsExpires, 'Documents bientôt expirés' => $docsBientot] as $titre => $liste)
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">{{ $titre }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead><tr class="text-left text-xs uppercase text-gray-500">
                        <th class="table-head">Agent</th><th class="table-head">Type</th><th class="table-head">Expiration</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($liste as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm"><a href="{{ route('agents.documents.index', $doc->agent_id) }}" class="text-institution-700 hover:underline">{{ $doc->agent?->nom_complet ?? '—' }}</a></td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->type_document?->label() }}</td>
                                <td class="px-4 py-3 text-sm {{ $doc->est_expire ? 'text-red-600 font-medium' : 'text-orange-600' }}">{{ $doc->date_expiration?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Aucun document.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
