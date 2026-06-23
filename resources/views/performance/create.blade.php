@extends('layouts.app')
@section('title','Nouvelle évaluation')
@section('header','Nouvelle évaluation')
@section('content')
<form method="POST" action="{{ route('performance.store') }}" class="card max-w-3xl">@csrf
    @include('performance._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('performance.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
