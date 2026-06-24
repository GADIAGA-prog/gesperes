@extends('layouts.app')
@section('title', 'Nouvelle fiche de poste')
@section('header', 'Outils GRH — Nouvelle fiche de poste')

@section('content')
@include('outils-grh._tabs')

<form method="POST" action="{{ route('fiches-poste.store') }}">
    @csrf
    @include('outils-grh.fiches-poste._form')
    <div class="flex justify-end gap-2 mt-6">
        <a href="{{ route('fiches-poste.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer la fiche</button>
    </div>
</form>
@endsection
