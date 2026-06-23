@extends('layouts.app')
@section('title','Nouveau plan de formation')
@section('header','Plan de formation — Nouveau plan')
@section('content')
@include('outils-grh._tabs')
<form method="POST" action="{{ route('plan-formation.store') }}" class="card max-w-3xl">@csrf
    @include('plan-formation._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('plan-formation.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Créer le plan</button>
    </div>
</form>
@endsection
