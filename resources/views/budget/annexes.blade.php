@extends('layouts.app')
@section('title', 'Tableaux annexes')
@section('header', 'Budget — Tableau annexe (Dépenses de personnel)')

@section('content')
@include('budget._tabs')

<style>
    table.annexe { width: 100%; border-collapse: collapse; font-size: 11px; }
    table.annexe th, table.annexe td { border: 1px solid #d1d5db; padding: 3px 6px; white-space: nowrap; }
    table.annexe thead th { background: #dcfce7; color: #14532d; text-align: left; }
    table.annexe td.r, table.annexe th.r { text-align: right; }
    table.annexe td.c { text-align: center; color: #9ca3af; padding: 24px; }
    table.annexe tr.grp td { background: #f3f4f6; font-weight: 600; }
    table.annexe tr.tot td { background: #fed7aa; font-weight: 700; }
</style>

<div class="card mb-4 text-sm text-gray-600">
    <strong>Tableau II-1 — Dépenses de personnel — Fonctionnaires et militaires présents en {{ $annees[0] }}</strong> (avant-projet de budget {{ $annees[0] }}-{{ $annees[2] }}).
    Détail <strong>par agent</strong>, regroupé par programme et structure. Colonnes par agent <strong>mensuelles</strong> ; <strong>incidence annuelle</strong> (× 12). CARFO = 13,5 %.
    Provisions : suppléments salariaux 3 % et nouvelles naissances 5 % (lignes Total).
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <select name="programme_id" class="input" data-recherche>
        <option value="">Tous les programmes</option>
        @foreach ($programmes as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['programme_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <select name="structure_id" class="input" data-recherche>
        <option value="">Toutes les structures</option>
        @foreach ($structures as $id => $libelle)
            <option value="{{ $id }}" {{ (string) ($filtres['structure_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $libelle }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary">Calculer</button>
    <div class="flex gap-2">
        <a href="{{ route('budget.annexes.excel', $filtres) }}" class="btn btn-secondary">Excel</a>
        <a href="{{ route('budget.annexes.pdf', $filtres) }}" class="btn btn-secondary">PDF</a>
        <a href="{{ route('budget.annexes') }}" class="btn btn-secondary">Réinit.</a>
    </div>
</form>
@include('partials.select-recherche')

@if (empty($detail))
    <div class="card text-center text-gray-400 py-10">Sélectionnez un programme ou une structure pour générer le tableau (une section par programme et structure).</div>
@else
    @foreach ($detail as $pcode => $prog)
        @foreach ($prog['structures'] as $slib => $s)
            <div class="card overflow-x-auto mb-6">
                @include('budget._annexe_structure', ['pcode' => $pcode, 'plib' => $prog['libelle'], 'slib' => $slib, 'lignes' => $s['lignes'], 'totaux' => $s['totaux'], 'provisions' => $s['provisions'], 'annees' => $annees])
            </div>
        @endforeach
    @endforeach
@endif
@endsection
