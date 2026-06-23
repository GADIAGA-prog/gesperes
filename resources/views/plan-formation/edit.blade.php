@extends('layouts.app')
@section('title','Modifier le plan')
@section('header','Plan de formation — Modifier')
@section('content')
@include('outils-grh._tabs')
<form method="POST" action="{{ route('plan-formation.update', $plan) }}" class="card max-w-3xl">@csrf @method('PUT')
    @include('plan-formation._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('plan-formation.show', $plan) }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
