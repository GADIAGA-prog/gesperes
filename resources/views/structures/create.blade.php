@extends('layouts.app')
@section('title', 'Nouvelle structure')
@section('header', 'Créer une structure')
@section('content')
<form method="POST" action="{{ route('structures.store') }}" class="card max-w-3xl">
    @csrf
    @include('structures._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('structures.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
