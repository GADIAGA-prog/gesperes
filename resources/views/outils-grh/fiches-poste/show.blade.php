@extends('layouts.app')
@section('title', $fiche->intitule)
@section('header', 'Fiche de poste — ' . $fiche->intitule)

@section('content')
@include('outils-grh._tabs')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <div>
        <span class="font-mono text-sm text-gray-500">{{ $fiche->code ?: '— code à générer —' }}</span>
        <span class="badge {{ $fiche->statut?->color() }} ml-2">{{ $fiche->statut?->label() }}</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('fiches-poste.pdf', $fiche) }}" class="btn btn-secondary">Exporter PDF</a>
        @can('fiches-poste.manage')
            <a href="{{ route('fiches-poste.edit', $fiche) }}" class="btn btn-primary">Modifier</a>
        @endcan
    </div>
</div>

@php
    $typesComp = \App\Enums\TypeCompetence::cases();
@endphp

<div class="space-y-6">
    {{-- Identification --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Identification</h3>
        <dl class="grid grid-cols-2 lg:grid-cols-3 gap-y-2 text-sm">
            <dt class="text-gray-500">Intitulé</dt><dd class="lg:col-span-2 font-medium">{{ $fiche->intitule }}</dd>
            <dt class="text-gray-500">Type</dt><dd>{{ $fiche->type_poste?->label() ?? '—' }}</dd>
            <dt class="text-gray-500">Position / mission</dt><dd>{{ $fiche->position_mission?->label() ?? '—' }}</dd>
            <dt class="text-gray-500">Position hiérarchique</dt><dd>{{ $fiche->position_hierarchique?->label() ?? '—' }}</dd>
            <dt class="text-gray-500">Famille professionnelle</dt><dd>{{ $fiche->familleProfessionnelle?->libelle ?? '—' }}</dd>
            <dt class="text-gray-500">Emploi-type</dt><dd>{{ $fiche->emploiType?->libelle ?? '—' }}</dd>
            <dt class="text-gray-500">Famille d'emplois</dt><dd>{{ $fiche->famille_emplois ?? '—' }}</dd>
            <dt class="text-gray-500">Emploi</dt><dd>{{ $fiche->emploi?->libelle ?? '—' }}</dd>
            <dt class="text-gray-500">Catégorie</dt><dd>{{ $fiche->categorie?->code ?? '—' }}</dd>
            <dt class="text-gray-500">Unité administrative</dt><dd class="lg:col-span-2">{{ $fiche->structure?->cheminComplet() ?? '—' }}</dd>
        </dl>
    </div>

    {{-- Mission --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-2">Mission du poste</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $fiche->mission ?: '—' }}</p>
    </div>

    {{-- Activités --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-2">Activités permanentes</h3>
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            @forelse ($fiche->activites as $a)
                <li>{{ $a->libelle }}@if($a->taux_contribution) <span class="text-gray-400">({{ $a->taux_contribution }})</span>@endif</li>
            @empty
                <li class="list-none text-gray-400">—</li>
            @endforelse
        </ul>
    </div>

    {{-- Relations --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Relations du poste</h3>
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-gray-500">Niveau hiérarchique supérieur</dt><dd>{{ $fiche->niveau_hierarchique_superieur ?: '—' }}</dd>
            <dt class="text-gray-500">Niveau hiérarchique inférieur</dt><dd>{{ $fiche->niveau_hierarchique_inferieur ?: '—' }}</dd>
            <dt class="text-gray-500">Relations fonctionnelles internes</dt><dd>{{ $fiche->relations_internes ?: '—' }}</dd>
            <dt class="text-gray-500">Relations fonctionnelles externes</dt><dd>{{ $fiche->relations_externes ?: '—' }}</dd>
        </dl>
    </div>

    {{-- Compétences --}}
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Compétences requises</h3>
        @foreach ($typesComp as $type)
            @php $items = $fiche->competences->filter(fn ($c) => $c->pivot->type === $type->value); @endphp
            @if ($items->isNotEmpty())
                <p class="text-xs font-semibold uppercase text-gray-500 mt-3">{{ $type->label() }}</p>
                <ul class="text-sm text-gray-700">
                    @foreach ($items as $c)
                        <li class="flex justify-between border-b border-gray-50 py-1">
                            <span>{{ $c->libelle }}</span>
                            <span class="text-gray-400">{{ \App\Enums\NiveauCompetencePoste::tryFrom($c->pivot->niveau)?->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach
        @if ($fiche->competences->isEmpty())<p class="text-sm text-gray-400">—</p>@endif
    </div>

    {{-- Profil : dimension + conditions + indicateurs --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Dimension & conditions d'accès</h3>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-gray-500">Moyens généraux</dt><dd>{{ $fiche->moyens_generaux ?: '—' }}</dd>
                <dt class="text-gray-500">Moyens spécifiques</dt><dd>{{ $fiche->moyens_specifiques ?: '—' }}</dd>
                <dt class="text-gray-500">Niveau d'études</dt><dd>{{ $fiche->niveau_etudes ?: '—' }}</dd>
                <dt class="text-gray-500">Domaine</dt><dd>{{ $fiche->domaine ?: '—' }}</dd>
                <dt class="text-gray-500">Spécialité</dt><dd>{{ $fiche->specialite ?: '—' }}</dd>
                <dt class="text-gray-500">Expérience</dt><dd>{{ $fiche->experience_pro ?: '—' }}</dd>
            </dl>
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-2">Indicateurs de performance</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                @forelse ($fiche->indicateurs as $i)
                    <li>{{ $i->libelle }}@if($i->nature) <span class="text-gray-400">— {{ ucfirst($i->nature) }}</span>@endif</li>
                @empty
                    <li class="list-none text-gray-400">—</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="mt-6"><a href="{{ route('fiches-poste.index') }}" class="text-sm text-gray-500 hover:underline">← Retour à la liste</a></div>
@endsection
