@extends('layouts.app')
@section('title', 'Modifier l\'activité ' . $activite->code)
@section('header', 'Modifier l\'activité ' . $activite->code)
@section('content')
<div class="mb-4"><a href="{{ route('budget.show', $activite) }}" class="text-sm text-institution-600 hover:underline">← Retour à l'activité</a></div>

<form method="POST" action="{{ route('budget.update', $activite) }}" class="card max-w-4xl">
    @csrf @method('PUT')
    @include('budget._form', ['activite' => $activite])
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('budget.show', $activite) }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
