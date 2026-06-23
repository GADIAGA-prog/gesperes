@extends('layouts.app')
@section('title', 'Fiches de poste')
@section('header', 'Outils GRH — Fiches de poste')

@section('content')
@include('outils-grh._tabs')

<div class="card border-l-4 border-amber-400 bg-amber-50">
    <p class="font-semibold text-amber-800">Sous-module en préparation</p>
    <p class="text-sm text-amber-700 mt-1">
        Descriptions de poste (mission, activités, compétences requises, rattachement) par emploi/poste,
        socle des entretiens professionnels et du recrutement.
    </p>
</div>
@endsection
