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
        @can('carriere.manage')
            <a href="{{ route('carriere.create', ['agent' => $agent->id]) }}" class="btn btn-secondary">Acte de carrière</a>
        @endcan
        @can('mouvements.manage')
            <a href="{{ route('mouvements.create', ['agent' => $agent->id]) }}" class="btn btn-secondary">Mouvement</a>
        @endcan
        @can('indemnites.view')
            <a href="{{ route('agents.indemnites.agent', $agent) }}" class="btn btn-secondary">Indemnités</a>
        @endcan
        @can('competences.view')
            <a href="{{ route('agents.competences.agent', $agent) }}" class="btn btn-secondary">Compétences</a>
        @endcan
        <a href="{{ route('agents.pdf', $agent) }}" class="btn btn-secondary">Fiche PDF</a>
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

@can('carriere.view')
    <div class="card mt-6">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Historique de carrière</h3>
            @can('carriere.manage')
                <a href="{{ route('carriere.create', ['agent' => $agent->id]) }}" class="text-sm text-institution-700 hover:underline">+ Ajouter un acte</a>
            @endcan
        </div>
        @forelse ($agent->evenementsCarriere as $e)
            <div class="flex flex-wrap items-start gap-3 py-2 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                <span class="text-sm text-gray-500 w-24 shrink-0">{{ $e->date_effet?->format('d/m/Y') }}</span>
                <span class="badge {{ $e->type?->color() }} shrink-0">{{ $e->type?->label() }}</span>
                <span class="text-sm text-gray-700 flex-1 min-w-[12rem]">
                    {{ $e->description ?: '—' }}
                    @if ($e->reference_acte)
                        <span class="text-gray-400 font-mono text-xs">({{ $e->reference_acte }})</span>
                    @endif
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucun acte de carrière enregistré.</p>
        @endforelse
    </div>
@endcan

@can('mouvements.view')
    @php $coulFam = ['activite'=>'bg-green-100 text-green-700','sortie_temporaire'=>'bg-amber-100 text-amber-800','sortie_definitive'=>'bg-red-100 text-red-700']; @endphp
    <div class="card mt-6">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Mouvements du personnel</h3>
            @can('mouvements.manage')
                <a href="{{ route('mouvements.create', ['agent' => $agent->id]) }}" class="text-sm text-institution-700 hover:underline">+ Ajouter un mouvement</a>
            @endcan
        </div>
        @forelse ($agent->mouvements as $m)
            <div class="flex flex-wrap items-start gap-3 py-2 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                <span class="text-sm text-gray-500 w-24 shrink-0">{{ $m->date_effet?->format('d/m/Y') }}</span>
                <span class="badge {{ $coulFam[$m->famille?->value] ?? 'bg-gray-100 text-gray-700' }} shrink-0">{{ $m->famille?->label() }}</span>
                <span class="text-sm text-gray-700 flex-1 min-w-[12rem]">
                    {{ $m->anciennePosition?->libelle ?? '—' }} → <span class="font-medium">{{ $m->nouvellePosition?->libelle ?? '—' }}</span>
                    @if ($m->reference_acte)<span class="text-gray-400 font-mono text-xs">({{ $m->reference_acte }})</span>@endif
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucun mouvement enregistré.</p>
        @endforelse
    </div>
@endcan

@can('discipline.view')
    <div class="card mt-6">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Discipline</h3>
            @can('discipline.manage')
                <a href="{{ route('discipline.create', ['agent' => $agent->id]) }}" class="text-sm text-institution-700 hover:underline">+ Ajouter un acte</a>
            @endcan
        </div>
        @forelse ($agent->dossiersDisciplinaires as $d)
            <div class="flex flex-wrap items-start gap-3 py-2 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                <span class="text-sm text-gray-500 w-24 shrink-0">{{ $d->date_acte?->format('d/m/Y') }}</span>
                <span class="badge {{ $d->type?->color() }} shrink-0">{{ $d->type?->label() }}</span>
                <span class="text-sm text-gray-700 flex-1 min-w-[12rem]">{{ \Illuminate\Support\Str::limit($d->motif, 90) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucun dossier disciplinaire.</p>
        @endforelse
    </div>
@endcan

@can('performance.view')
    <div class="card mt-6">
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">Évaluations de performance</h3>
            @can('performance.manage')
                <a href="{{ route('performance.create', ['agent' => $agent->id]) }}" class="text-sm text-institution-700 hover:underline">+ Nouvelle évaluation</a>
            @endcan
        </div>
        @forelse ($agent->evaluations as $e)
            <div class="flex flex-wrap items-center gap-3 py-2 {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                <span class="text-sm font-medium w-16 shrink-0">{{ $e->periode }}</span>
                <span class="text-sm text-gray-700 flex-1">{{ $e->note !== null ? rtrim(rtrim(number_format($e->note,2),'0'),'.') . ' / 20' : 'Non noté' }} — {{ \Illuminate\Support\Str::limit($e->appreciation, 70) ?: '—' }}</span>
                <span class="text-xs text-gray-400">{{ $e->statut === 'valide' ? 'Validée' : 'Brouillon' }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune évaluation.</p>
        @endforelse
    </div>
@endcan

@can('competences.view')
    @if ($agent->competences->isNotEmpty())
    <div class="card mt-6">
        <h3 class="font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Compétences</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($agent->competences as $c)
                <span class="badge bg-institution-50 text-institution-700">{{ $c->libelle }}</span>
            @endforeach
        </div>
    </div>
    @endif
@endcan
@endsection
