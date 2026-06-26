@extends('layouts.agent')
@section('title', 'Mes actes')
@section('header', 'Mes actes')
@section('sous-titre', $documents->total() . ' document(s) dans votre dossier')

@section('content')
{{-- Filtre par type --}}
<form method="GET" class="mb-4" x-data>
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
        <select name="type" onchange="this.form.submit()" class="input w-full pl-10">
            <option value="">Tous les types de document</option>
            @foreach ($types as $valeur => $libelle)
                <option value="{{ $valeur }}" {{ ($filtres['type'] ?? '') === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>
</form>

@php
    $couleurType = fn ($t) => match ($t?->value) {
        'arrete', 'decision', 'acte_nomination', 'acte_affectation' => 'bg-institution-50 text-institution-700 ring-institution-100',
        'diplome', 'attestation'                                    => 'bg-administ-500/10 text-administ-600 ring-administ-500/20',
        'contrat', 'cnib'                                           => 'bg-amber-50 text-amber-700 ring-amber-100',
        default                                                     => 'bg-slate-100 text-slate-600 ring-slate-200',
    };
@endphp

@if ($documents->isEmpty())
    <div class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-slate-200/70">
        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        <p class="mt-3 text-sm font-medium text-slate-600">Aucun acte disponible</p>
        <p class="mt-1 text-sm text-slate-400">Vos documents administratifs apparaîtront ici.</p>
    </div>
@else
    <div class="space-y-3">
        @foreach ($documents as $doc)
            <div class="flex items-center gap-3 rounded-2xl bg-white p-3.5 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-institution-200">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl {{ $couleurType($doc->type_document) }} ring-1">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-800">{{ $doc->type_document?->label() ?? 'Document' }}</p>
                    <p class="truncate text-xs text-slate-500">
                        {{ $doc->reference ?: $doc->nom_original }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-400">
                        {{ $doc->date_document?->format('d/m/Y') ?? '—' }} · {{ $doc->taille_lisible }}
                    </p>
                </div>
                <a href="{{ route('espace-agent.actes.telecharger', $doc) }}"
                   class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-institution-50 text-institution-700 transition hover:bg-institution-100"
                   title="Télécharger">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $documents->links() }}</div>
@endif
@endsection
