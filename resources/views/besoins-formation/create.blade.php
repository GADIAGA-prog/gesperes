@extends('layouts.app')
@section('title','Nouveau besoin de formation')
@section('header','Besoins de formation — Nouveau recueil')
@section('content')
@include('outils-grh._tabs')
<form method="POST" action="{{ route('besoins-formation.store') }}" class="card max-w-4xl">@csrf
    @include('besoins-formation._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('besoins-formation.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
