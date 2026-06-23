@extends('layouts.app')
@section('title', 'Nouvelle activité budgétaire')
@section('header', 'Saisir une activité budgétaire')
@section('content')
<div class="mb-4"><a href="{{ route('budget.index') }}" class="text-sm text-institution-600 hover:underline">← Budget des structures</a></div>

<form method="POST" action="{{ route('budget.store') }}" class="card max-w-4xl">
    @csrf
    @include('budget._form', ['activite' => null])
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('budget.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Créer l'activité</button>
    </div>
</form>
@endsection
