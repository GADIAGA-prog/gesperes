@extends('layouts.app')
@section('title', 'Enveloppe ' . $enveloppe->annee_debut)
@section('header', 'Budget — Enveloppe ' . $enveloppe->annee_debut . '–' . ($enveloppe->annee_debut + 2))

@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $annees = $enveloppe->annees;
    $totaux = $enveloppe->totaux;
@endphp

@section('content')
@include('budget._tabs')
@include('partials.flash')

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('budget.enveloppe.index') }}" class="text-sm text-institution-600 hover:underline">← Toutes les enveloppes</a>
    <div class="flex items-center gap-2">
        <a href="{{ route('budget.enveloppe.ventilation', $enveloppe) }}" class="btn btn-secondary">Ventilation détaillée</a>
    @can('budget.manage')
        <form method="POST" action="{{ route('budget.enveloppe.destroy', $enveloppe) }}" onsubmit="return confirm('Supprimer cette enveloppe ?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Supprimer</button>
        </form>
    @endcan
    </div>
</div>

{{-- Présentation (façon fichier de référence) --}}
<div class="card overflow-x-auto mb-6">
    <p class="text-center font-bold uppercase text-gray-800 mb-4">{{ $enveloppe->intitule }} {{ $annees[0] }}-{{ $annees[2] }}</p>
    <table class="min-w-full border border-gray-300 text-sm">
        <thead>
            <tr class="bg-green-100 text-green-900">
                <th class="border border-gray-300 px-4 py-2 text-left">Dépenses de personnel</th>
                @foreach ($annees as $a)
                    <th class="border border-gray-300 px-4 py-2 text-center w-40">{{ $a }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($enveloppe->lignes as $l)
                <tr>
                    <td class="border border-gray-300 px-4 py-2 italic">{{ $l->libelle }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-right italic">{{ $fmt($l->montant_n1) }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-right italic">{{ $fmt($l->montant_n2) }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-right italic">{{ $fmt($l->montant_n3) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="border border-gray-300 px-4 py-6 text-center text-gray-400">Aucune ligne.</td></tr>
            @endforelse
            <tr class="bg-orange-200 font-bold">
                <td class="border border-gray-300 px-4 py-2">Total</td>
                <td class="border border-gray-300 px-4 py-2 text-right">{{ $fmt($totaux[0]) }}</td>
                <td class="border border-gray-300 px-4 py-2 text-right">{{ $fmt($totaux[1]) }}</td>
                <td class="border border-gray-300 px-4 py-2 text-right">{{ $fmt($totaux[2]) }}</td>
            </tr>
        </tbody>
    </table>
    <p class="text-xs text-gray-500 mt-2">Montants en milliers de FCFA (selon le fichier de référence).</p>
</div>

{{-- Édition --}}
@can('budget.manage')
    <form method="POST" action="{{ route('budget.enveloppe.update', $enveloppe) }}" class="card">
        @csrf @method('PUT')
        <p class="font-semibold text-gray-800 mb-3">Modifier les lignes</p>

        <div class="mb-4">
            <label class="label">Intitulé</label>
            <input type="text" name="intitule" value="{{ $enveloppe->intitule }}" class="input">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-500">
                        <th class="px-2 py-1">Ligne (dépense de personnel)</th>
                        <th class="px-2 py-1 w-36">{{ $annees[0] }}</th>
                        <th class="px-2 py-1 w-36">{{ $annees[1] }}</th>
                        <th class="px-2 py-1 w-36">{{ $annees[2] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rows = $enveloppe->lignes->all(); for ($i = 0; $i < 3; $i++) { $rows[] = null; } @endphp
                    @foreach ($rows as $i => $l)
                        <tr>
                            <td class="px-2 py-1"><input type="text" name="lignes[{{ $i }}][libelle]" value="{{ $l?->libelle }}" class="input" placeholder="Nouvelle ligne…"></td>
                            <td class="px-2 py-1"><input type="number" step="0.01" min="0" name="lignes[{{ $i }}][montant_n1]" value="{{ $l ? (float) $l->montant_n1 : '' }}" class="input text-right"></td>
                            <td class="px-2 py-1"><input type="number" step="0.01" min="0" name="lignes[{{ $i }}][montant_n2]" value="{{ $l ? (float) $l->montant_n2 : '' }}" class="input text-right"></td>
                            <td class="px-2 py-1"><input type="number" step="0.01" min="0" name="lignes[{{ $i }}][montant_n3]" value="{{ $l ? (float) $l->montant_n3 : '' }}" class="input text-right"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-2">Les lignes sans intitulé sont ignorées. Le total est recalculé automatiquement.</p>
        <div class="mt-4"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
    </form>
@endcan
@endsection
