@extends('layouts.app')
@section('title', 'Fiches de présence')
@section('header', 'Contrôle présence — Fiches de présence (A / B / C)')
@section('content')
@include('controle-presence._tabs')

@php
    $moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
    $annees = range((int)date('Y'), (int)date('Y') - 4);
@endphp

<p class="text-sm text-gray-500 mb-4">Générez les fiches officielles au format PDF (impression) ou Excel.</p>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- FICHE A --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800">Fiche A</h3>
        <p class="text-xs text-gray-500 mb-3">Situation <strong>journalière</strong> d'une structure.</p>
        <form method="GET" target="_blank" class="space-y-3" id="ficheA">
            <x-form.select name="structure_id" label="Structure" :options="$structures" required />
            <x-form.input name="date" label="Date" type="date" :value="$date" required />
            <div class="flex gap-2 pt-1">
                <button type="submit" formaction="{{ route('fiches.a') }}" name="format" value="pdf" class="btn btn-primary flex-1 justify-center">PDF</button>
                <button type="submit" formaction="{{ route('fiches.a') }}" name="format" value="xlsx" class="btn btn-secondary flex-1 justify-center">Excel</button>
            </div>
        </form>
    </div>

    {{-- FICHE B --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800">Fiche B</h3>
        <p class="text-xs text-gray-500 mb-3">Situation <strong>mensuelle</strong> d'une structure.</p>
        <form method="GET" target="_blank" class="space-y-3" id="ficheB">
            <x-form.select name="structure_id" label="Structure" :options="$structures" required />
            <x-form.select name="mois" label="Mois" :options="$moisNoms" :selected="$mois" required />
            <x-form.select name="annee" label="Année" :options="collect($annees)->mapWithKeys(fn($a)=>[$a=>$a])" :selected="$annee" required />
            <div class="flex gap-2 pt-1">
                <button type="submit" formaction="{{ route('fiches.b') }}" name="format" value="pdf" class="btn btn-primary flex-1 justify-center">PDF</button>
                <button type="submit" formaction="{{ route('fiches.b') }}" name="format" value="xlsx" class="btn btn-secondary flex-1 justify-center">Excel</button>
            </div>
        </form>
    </div>

    {{-- FICHE C --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800">Fiche C</h3>
        <p class="text-xs text-gray-500 mb-3">Situation <strong>trimestrielle</strong> de tout le ministère.</p>
        <form method="GET" target="_blank" class="space-y-3" id="ficheC">
            <x-form.select name="trimestre" label="Trimestre"
                :options="[1=>'1er trimestre',2=>'2e trimestre',3=>'3e trimestre',4=>'4e trimestre']" :selected="$trimestre" required />
            <x-form.select name="annee" label="Année" :options="collect($annees)->mapWithKeys(fn($a)=>[$a=>$a])" :selected="$annee" required />
            <div class="flex gap-2 pt-1">
                <button type="submit" formaction="{{ route('fiches.c') }}" name="format" value="pdf" class="btn btn-primary flex-1 justify-center">PDF</button>
                <button type="submit" formaction="{{ route('fiches.c') }}" name="format" value="xlsx" class="btn btn-secondary flex-1 justify-center">Excel</button>
            </div>
        </form>
    </div>
</div>
@endsection
