@extends('layouts.app')
@section('title', 'Ventilation ' . $enveloppe->annee_debut)
@section('header', 'Ventilation de l\'enveloppe ' . $enveloppe->annee_debut . '–' . ($enveloppe->annee_debut + 2))

@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $annees = $enveloppe->annees;
    $v = $ventilation;
@endphp

@section('content')
@include('budget._tabs')

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('budget.enveloppe.show', $enveloppe) }}" class="text-sm text-institution-600 hover:underline">← Enveloppe</a>
</div>

<div class="card mb-4 text-sm text-gray-600">
    Répartition de la ligne <strong>« Salaire du personnel en activité »</strong> de l'enveloppe, au prorata de la masse
    salariale calculée (Dépenses du personnel), par <strong>action</strong> et <strong>paragraphe</strong>
    (661 traitements · 663 primes/indemnités · 664 CARFO · 666 prestations). Montants annuels, unité de l'enveloppe.
</div>

<div class="card overflow-x-auto">
    <table class="min-w-full border border-gray-300 text-xs">
        <thead>
            <tr class="bg-green-100 text-green-900">
                <th class="border border-gray-300 px-3 py-2 text-left">Programme</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Action</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Paragraphe</th>
                @foreach ($annees as $a)
                    <th class="border border-gray-300 px-3 py-2 text-right w-32">{{ $a }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($v['lignes'] as $l)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-300 px-3 py-1.5 whitespace-nowrap">{{ $l['programme_code'] }}</td>
                    <td class="border border-gray-300 px-3 py-1.5" title="{{ $l['action_libelle'] }}">{{ $l['action_code'] }}</td>
                    <td class="border border-gray-300 px-3 py-1.5">{{ $l['paragraphe'] }} — {{ $l['paragraphe_libelle'] }}</td>
                    @foreach ($l['montants'] as $m)
                        <td class="border border-gray-300 px-3 py-1.5 text-right">{{ $fmt($m) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="6" class="border border-gray-300 px-4 py-8 text-center text-gray-400">Aucune donnée (masse salariale nulle ou enveloppe sans ligne « salaire en activité »).</td></tr>
            @endforelse
            <tr class="bg-orange-200 font-bold">
                <td class="border border-gray-300 px-3 py-2" colspan="3">Total (= Salaire du personnel en activité)</td>
                @foreach ($v['totaux'] as $t)
                    <td class="border border-gray-300 px-3 py-2 text-right">{{ $fmt($t) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>
@endsection
