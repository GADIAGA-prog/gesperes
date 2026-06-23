@extends('layouts.app')
@section('title','Modifier le dossier')
@section('header','Suivi des dossiers — Modifier')
@section('content')
<form method="POST" action="{{ route('suivi-dossiers.update', $dossier) }}" class="card max-w-4xl">@csrf @method('PUT')
    @include('suivi-dossiers._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('suivi-dossiers.show', $dossier) }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
