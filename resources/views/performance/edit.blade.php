@extends('layouts.app')
@section('title','Modifier évaluation')
@section('header','Modifier évaluation')
@section('content')
<form method="POST" action="{{ route('performance.update', $evaluation) }}" class="card max-w-3xl">@csrf @method('PUT')
    @include('performance._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('performance.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Mettre à jour</button>
    </div>
</form>
@endsection
