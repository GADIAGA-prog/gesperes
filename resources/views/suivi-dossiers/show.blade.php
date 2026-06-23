@extends('layouts.app')
@section('title','Dossier '.$dossier->reference_bordereau)
@section('header','Dossier — '.$dossier->reference_bordereau)
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        <span class="badge {{ $dossier->etape?->color() }}">{{ $dossier->etape?->label() }}</span>
        <span class="badge {{ $dossier->statut?->color() }}">{{ $dossier->statut?->label() }}</span>
        @if($dossier->en_retard)<span class="badge bg-red-100 text-red-700">Délai dépassé</span>@endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('suivi-dossiers.index') }}" class="btn btn-secondary">Retour</a>
        @can('suivi.manage')
            <a href="{{ route('suivi-dossiers.edit',$dossier) }}" class="btn btn-secondary">Modifier</a>
            <button onclick="if(confirm('Supprimer ce dossier ?'))document.getElementById('del-dossier').submit()" class="btn btn-danger">Supprimer</button>
            <form id="del-dossier" method="POST" action="{{ route('suivi-dossiers.destroy',$dossier) }}" class="hidden">@csrf @method('DELETE')</form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Fiche --}}
    <div class="card lg:col-span-2">
        <h3 class="font-semibold text-gray-800 mb-3">Informations</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div><dt class="text-gray-400">Référence bordereau</dt><dd class="font-medium">{{ $dossier->reference_bordereau }}</dd></div>
            <div><dt class="text-gray-400">Nature</dt><dd>{{ $dossier->nature?->libelle ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-400">Objet</dt><dd>{{ $dossier->objet ?? '—' }}</dd></div>
            <div><dt class="text-gray-400">Structure concernée</dt><dd>{{ $dossier->structure?->libelle ?? '—' }}</dd></div>
            <div><dt class="text-gray-400">Se situe actuellement</dt><dd>{{ $dossier->serviceActuel?->libelle ?? '—' }}@if($dossier->agentActuel) — {{ $dossier->agentActuel->nom_complet }}@endif</dd></div>
            <div><dt class="text-gray-400">Date de réception</dt><dd>{{ $dossier->date_reception?->format('d/m/Y') }}</dd></div>
            <div><dt class="text-gray-400">Délai de traitement</dt><dd>{{ $dossier->delai_jours }} jour(s)</dd></div>
            <div><dt class="text-gray-400">Échéance</dt><dd>{{ $dossier->date_limite?->format('d/m/Y') ?? '—' }}
                @if($dossier->en_retard)<span class="text-red-600 font-medium">(dépassée)</span>
                @elseif($dossier->jours_restants !== null && !$dossier->statut?->estTermine())<span class="text-green-600">(J-{{ $dossier->jours_restants }})</span>@endif
            </dd></div>
            <div><dt class="text-gray-400">Date de traitement</dt><dd>{{ $dossier->date_traitement?->format('d/m/Y') ?? '—' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-gray-400">Observation</dt><dd>{{ $dossier->observation ?? '—' }}</dd></div>
        </dl>
    </div>

    {{-- Actions de circuit --}}
    @can('suivi.manage')
    <div class="space-y-4">
        @unless($dossier->statut?->estTermine())
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-3">Transmettre / faire avancer</h3>
            <form method="POST" action="{{ route('suivi-dossiers.transmettre',$dossier) }}" class="space-y-3">@csrf
                <x-form.select name="etape" label="Nouvelle étape" :options="$etapes" :selected="old('etape',$dossier->etape?->value)" required />
                <x-form.select name="service_id" label="Service destinataire" :options="$services" :selected="old('service_id',$dossier->service_actuel_id)" />
                <x-form.select name="agent_id" label="Agent destinataire" :options="$agents" :selected="old('agent_id',$dossier->agent_actuel_id)" />
                <x-form.input name="date_mouvement" label="Date" type="date" :value="old('date_mouvement', now()->toDateString())" required />
                <x-form.textarea name="commentaire" label="Commentaire" :value="old('commentaire')" rows="2" />
                <button class="btn btn-primary w-full">Enregistrer le mouvement</button>
            </form>
        </div>

        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-3">Clôturer le dossier</h3>
            <form method="POST" action="{{ route('suivi-dossiers.cloturer',$dossier) }}" class="space-y-3">@csrf
                <x-form.input name="date_traitement" label="Date de traitement" type="date" :value="old('date_traitement', now()->toDateString())" />
                <x-form.textarea name="commentaire" label="Commentaire" :value="old('commentaire')" rows="2" />
                <button class="btn btn-secondary w-full">Marquer comme traité / clôturer</button>
            </form>
        </div>
        @else
        <div class="card text-sm text-gray-500">Dossier clôturé le {{ $dossier->date_traitement?->format('d/m/Y') }}.</div>
        @endunless
    </div>
    @endcan
</div>

{{-- Historique du circuit --}}
<div class="card mt-4">
    <h3 class="font-semibold text-gray-800 mb-3">Historique du circuit</h3>
    <ol class="relative border-l border-gray-200 ml-2">
        @forelse($dossier->etapes as $e)
            <li class="ml-4 pb-4">
                <span class="absolute -left-1.5 w-3 h-3 rounded-full bg-institution-500"></span>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge {{ $e->etape?->color() }}">{{ $e->etape?->label() }}</span>
                    <span class="text-xs text-gray-400">{{ $e->date_mouvement?->format('d/m/Y') }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $e->service?->libelle ?? '—' }}@if($e->agent) — {{ $e->agent->nom_complet }}@endif
                </p>
                @if($e->commentaire)<p class="text-sm text-gray-500 italic">{{ $e->commentaire }}</p>@endif
                @if($e->createur)<p class="text-xs text-gray-400">par {{ $e->createur->name }}</p>@endif
            </li>
        @empty
            <li class="ml-4 text-sm text-gray-400">Aucun mouvement enregistré.</li>
        @endforelse
    </ol>
</div>
@endsection
