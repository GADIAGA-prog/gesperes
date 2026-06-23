@extends('layouts.app')
@section('title', 'TPEE')
@section('header', 'Outils GRH — TPEE')

@section('content')
@include('outils-grh._tabs')

<div class="card border-l-4 border-amber-400 bg-amber-50">
    <p class="font-semibold text-amber-800">Sous-module en préparation</p>
    <p class="text-sm text-amber-700 mt-1">
        Tableau Prévisionnel des Emplois et des Effectifs : effectifs par emploi/structure, projections
        de départs (retraite) et besoins de remplacement — en lien avec la GPEC.
    </p>
</div>
@endsection
