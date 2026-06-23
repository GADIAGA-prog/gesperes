@extends('layouts.app')
@section('title','Nouveau dossier')
@section('header','Suivi des dossiers — Nouveau dossier')
@section('content')
<form method="POST" action="{{ route('suivi-dossiers.store') }}" class="card max-w-4xl">@csrf
    @include('suivi-dossiers._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('suivi-dossiers.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
