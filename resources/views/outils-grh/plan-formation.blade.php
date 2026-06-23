@extends('layouts.app')
@section('title', 'Plan de formation')
@section('header', 'Outils GRH — Plan de formation du ministère')

@section('content')
@include('outils-grh._tabs')

<div class="card border-l-4 border-amber-400 bg-amber-50">
    <p class="font-semibold text-amber-800">Sous-module en préparation</p>
    <p class="text-sm text-amber-700 mt-1">
        Plan de formation ministériel : besoins de formation par structure/emploi, sessions planifiées
        et suivi — en lien avec le module Formation existant.
    </p>
</div>
@endsection
