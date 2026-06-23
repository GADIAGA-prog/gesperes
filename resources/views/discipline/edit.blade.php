@extends('layouts.app')
@section('title','Modifier acte disciplinaire')
@section('header','Modifier acte disciplinaire')
@section('content')
<form method="POST" action="{{ route('discipline.update', $dossier) }}" class="card max-w-3xl">@csrf @method('PUT')
    @include('discipline._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Mettre à jour</button>
    </div>
</form>
@endsection
