@extends('layouts.app')
@section('title', 'Modifier la fiche de poste')
@section('header', 'Outils GRH — Modifier : ' . $fiche->intitule)

@section('content')
@include('outils-grh._tabs')

<form method="POST" action="{{ route('fiches-poste.update', $fiche) }}">
    @csrf
    @method('PUT')
    @include('outils-grh.fiches-poste._form')
    <div class="flex justify-between gap-2 mt-6">
        <button type="submit" form="form-suppression-fiche" class="btn btn-danger"
                onclick="return confirm('Supprimer définitivement cette fiche de poste ?')">Supprimer</button>
        <div class="flex gap-2">
            <a href="{{ route('fiches-poste.show', $fiche) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </div>
</form>

<form id="form-suppression-fiche" method="POST" action="{{ route('fiches-poste.destroy', $fiche) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection
