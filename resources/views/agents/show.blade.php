@extends('layouts.app')
@section('title', $agent->nom_complet)
@section('header', $agent->nom_complet)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-3">
        <div class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-institution-100 text-institution-700 text-xl font-bold">
            {{ strtoupper(substr($agent->nom, 0, 1)) }}
        </div>
        <div>
            <p class="text-lg font-bold text-gray-800">{{ $agent->nom_complet }}</p>
            <p class="text-sm text-gray-500 font-mono">{{ $agent->matricule }}{{ $agent->cle }}</p>
        </div>
        <span class="badge {{ $agent->statut_dossier?->color() }}">{{ $agent->statut_dossier?->label() }}</span>
    </div>
    <div class="flex gap-2">
        @can('agents.update')
            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-primary">Modifier</a>
        @endcan
        @can('documents.view')
            <a href="{{ route('agents.documents.index', $agent) }}" class="btn btn-secondary">Documents</a>
        @endcan
    </div>
</div>

@php
    $blocs = [
        'État civil' => [
            'Sexe' => $agent->sexe?->label(),
            'Date de naissance' => $agent->date_naissance?->format('d/m/Y'),
            'Âge' => $agent->age ? $agent->age . ' ans' : null,
            'Nationalité' => $agent->nationalite,
            'Téléphone' => $agent->telephone,
            'E-mail' => $agent->email,
            'Adresse' => $agent->adresse,
        ],
        'Carrière' => [
            'Emploi' => $agent->emploi?->libelle,
            'Fonction' => $agent->fonction?->libelle,
            'Poste' => $agent->poste?->libelle,
            'Catégorie' => $agent->categorie?->code,
            'Échelle' => $agent->echelle?->libelle,
            'Classe' => $agent->classe?->libelle,
            'Échelon' => $agent->echelon?->libelle,
            'Indice' => $agent->indice?->valeur,
            'Position' => $agent->positionAdministrative?->libelle,
            'Date intégration' => $agent->date_integration?->format('d/m/Y'),
            'Date retraite' => $agent->date_retraite?->format('d/m/Y'),
        ],
        'Affectation' => [
            'Structure' => $agent->structure?->libelle,
            'Région' => $agent->region,
            'Province' => $agent->province,
            'Commune' => $agent->commune,
            'Établissement' => $agent->etablissement,
            'Localité' => $agent->localite?->libelle,
            'Date affectation' => $agent->date_affectation?->format('d/m/Y'),
        ],
        'Enseignement' => [
            'Type' => $agent->typeEnseignement?->libelle,
            'Spécialité' => $agent->specialite?->libelle,
            'Lieu d\'exercice' => $agent->lieu_exercice?->label(),
            'Volume horaire dû' => $agent->volume_horaire_du,
            'Volume horaire assuré' => $agent->volume_horaire_assure,
        ],
        'Famille' => [
            'Situation' => $agent->situation_matrimoniale?->label(),
            'Nombre d\'enfants' => $agent->nombre_enfants,
            'Personnes à charge' => $agent->personnes_a_charge,
            'Allocation familiale' => $agent->allocation_familiale ? number_format($agent->allocation_familiale, 0, ',', ' ') . ' FCFA' : null,
        ],
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach ($blocs as $titre => $champs)
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">{{ $titre }}</h3>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                @foreach ($champs as $label => $valeur)
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="text-gray-800 font-medium">{{ $valeur ?: '—' }}</dd>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>

@if ($agent->observations)
    <div class="card mt-6">
        <h3 class="font-semibold text-gray-700 mb-2">Observations</h3>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $agent->observations }}</p>
    </div>
@endif
@endsection
