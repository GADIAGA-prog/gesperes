@extends('layouts.agent')
@section('title', 'Mes informations')
@section('header', 'Mes informations')
@section('sous-titre', 'Données de votre dossier administratif')

@section('content')
@php
    $sections = [
        [
            'titre' => 'État civil & famille',
            'icon'  => '<path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>',
            'lignes' => [
                ['Matricule', $agent->matricule],
                ['Nom', $agent->nom],
                ['Prénoms', $agent->prenoms],
                ['Sexe', $agent->sexe?->label()],
                ['Date de naissance', $agent->date_naissance?->format('d/m/Y')],
                ['Situation matrimoniale', $agent->situation_matrimoniale?->label()],
                ["Nombre d'enfants", $agent->nombre_enfants],
                ['Personnes à charge', $agent->personnes_a_charge],
                ['Téléphone', $agent->telephone],
                ['E-mail', $agent->email],
                ['Adresse', $agent->adresse],
            ],
        ],
        [
            'titre' => 'Carrière',
            'icon'  => '<path d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>',
            'lignes' => [
                ['Statut', $agent->statut],
                ['Emploi', $agent->emploi?->libelle],
                ['Fonction', $agent->fonction?->libelle],
                ['Catégorie', $agent->categorie?->libelle],
                ['Échelle', $agent->echelle?->libelle],
                ['Classe', $agent->classe?->libelle],
                ['Échelon', $agent->echelon?->libelle],
                ['Indice', $agent->indice?->valeur],
                ['Position administrative', $agent->positionAdministrative?->libelle],
                ["Date d'intégration", $agent->date_integration?->format('d/m/Y')],
                ['Date de nomination', $agent->date_nomination?->format('d/m/Y')],
                ['Retraite prévue', $agent->date_retraite?->format('d/m/Y')],
            ],
        ],
        [
            'titre' => 'Affectation',
            'icon'  => '<path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>',
            'lignes' => [
                ['Structure', $agent->structure?->libelle],
                ['Région', $agent->region],
                ['Province', $agent->province],
                ['Commune', $agent->commune],
                ['Établissement', $agent->etablissement],
                ["Date d'affectation", $agent->date_affectation?->format('d/m/Y')],
                ["Lieu d'exercice", $agent->lieu_exercice?->label()],
                ["Type d'enseignement", $agent->typeEnseignement?->libelle],
                ['Spécialité', $agent->specialite?->libelle],
            ],
        ],
    ];
@endphp

<div class="space-y-4">
    @foreach ($sections as $s)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
            <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-3.5">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-institution-50 text-institution-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
                </span>
                <h3 class="text-sm font-semibold text-slate-800">{{ $s['titre'] }}</h3>
            </div>
            <dl class="grid grid-cols-1 gap-px bg-slate-100 sm:grid-cols-2">
                @foreach ($s['lignes'] as [$label, $valeur])
                    <div class="bg-white px-5 py-3">
                        <dt class="text-xs text-slate-400">{{ $label }}</dt>
                        <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ ($valeur === null || $valeur === '') ? '—' : $valeur }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach

    <div class="flex items-start gap-2 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700 ring-1 ring-amber-100">
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        Une information est inexacte ? Rapprochez-vous de votre service du personnel pour la faire corriger.
    </div>
</div>
@endsection
