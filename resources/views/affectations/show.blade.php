@extends('layouts.app')
@section('title', 'Détail affectation')
@section('header', 'Détail de l\'affectation')
@section('content')
<div class="card max-w-2xl">
    <dl class="grid grid-cols-2 gap-y-3 text-sm">
        <dt class="text-gray-500">Agent</dt><dd class="font-medium">{{ $affectation->agent?->nom_complet }}</dd>
        <dt class="text-gray-500">Date d'effet</dt><dd>{{ $affectation->date_effet?->format('d/m/Y') }}</dd>
        <dt class="text-gray-500">Ancienne structure</dt><dd>{{ $affectation->ancienneStructure?->libelle ?: '—' }}</dd>
        <dt class="text-gray-500">Nouvelle structure</dt><dd>{{ $affectation->nouvelleStructure?->libelle ?: '—' }}</dd>
        <dt class="text-gray-500">Ancienne fonction</dt><dd>{{ $affectation->ancienneFonction?->libelle ?: '—' }}</dd>
        <dt class="text-gray-500">Nouvelle fonction</dt><dd>{{ $affectation->nouvelleFonction?->libelle ?: '—' }}</dd>
        <dt class="text-gray-500">Référence acte</dt><dd>{{ $affectation->reference_acte ?: '—' }}</dd>
        <dt class="text-gray-500">Motif</dt><dd>{{ $affectation->motif ?: '—' }}</dd>
        <dt class="text-gray-500">Saisi par</dt><dd>{{ $affectation->createur?->name ?: '—' }}</dd>
    </dl>
    <a href="{{ route('affectations.index') }}" class="btn btn-secondary mt-6">← Retour</a>
</div>
@endsection
