@extends('layouts.app')
@section('title', 'Nouvel agent')
@section('header', 'Créer un agent')

@section('content')
<form method="POST" action="{{ route('agents.store') }}" class="card">
    @csrf
    @include('agents._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('agents.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
