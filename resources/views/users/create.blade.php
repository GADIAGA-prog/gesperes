@extends('layouts.app')
@section('title', 'Nouvel utilisateur')
@section('header', 'Créer un utilisateur')
@section('content')
<form method="POST" action="{{ route('users.store') }}" class="card max-w-3xl">
    @csrf
    @include('users._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
