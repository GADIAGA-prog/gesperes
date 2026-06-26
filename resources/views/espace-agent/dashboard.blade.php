@extends('layouts.agent')
@section('title', 'Tableau de bord')
@section('header', 'Bonjour ' . ($agent->prenoms ? \Illuminate\Support\Str::of($agent->prenoms)->explode(' ')->first() : ''))
@section('sous-titre', 'Bienvenue dans votre espace personnel')

@section('content')
{{-- Carte identité --}}
<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
    <div class="bg-gradient-to-br from-institution-50 to-white p-5">
        <div class="flex items-start gap-4">
            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-institution-700 text-xl font-bold text-white shadow-md">
                {{ strtoupper(mb_substr($agent->prenoms, 0, 1) . mb_substr($agent->nom, 0, 1)) }}
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-lg font-bold text-slate-800">{{ $agent->nom_complet }}</h2>
                <p class="text-sm text-slate-500">{{ $agent->emploi?->libelle ?? 'Agent' }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                        <svg class="h-3.5 w-3.5 text-institution-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                        {{ $agent->matricule }}
                    </span>
                    @if ($agent->est_actif)
                        <span class="inline-flex items-center gap-1 rounded-full bg-administ-500/10 px-2.5 py-1 text-xs font-medium text-administ-600 ring-1 ring-administ-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-administ-500"></span> En activité
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tuiles d'accès rapide --}}
<div class="mt-4 grid grid-cols-2 gap-3">
    <a href="{{ route('espace-agent.actes') }}" class="group rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-institution-200">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-institution-50 text-institution-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </span>
        <p class="mt-3 text-2xl font-bold text-slate-800">{{ $nbActes }}</p>
        <p class="text-sm text-slate-500">Mes actes</p>
    </a>
    <a href="{{ route('espace-agent.notifications') }}" class="group rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70 transition hover:ring-institution-200">
        <span class="relative grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        </span>
        <p class="mt-3 text-2xl font-bold text-slate-800">{{ $nbNotifsNonLues }}</p>
        <p class="text-sm text-slate-500">Non lues</p>
    </a>
</div>

{{-- Mon poste --}}
<div class="mt-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
        <h3 class="text-sm font-semibold text-slate-800">Mon poste</h3>
        <a href="{{ route('espace-agent.profil') }}" class="text-xs font-medium text-institution-600 hover:underline">Tout voir →</a>
    </div>
    <dl class="divide-y divide-slate-50">
        @php
            $infos = [
                ['Fonction', $agent->fonction?->libelle],
                ['Structure', $agent->structure?->libelle],
                ['Position administrative', $agent->positionAdministrative?->libelle],
                ['Retraite prévue', $agent->date_retraite?->format('d/m/Y')],
            ];
        @endphp
        @foreach ($infos as [$label, $valeur])
            <div class="flex items-center justify-between gap-4 px-5 py-3">
                <dt class="text-sm text-slate-500">{{ $label }}</dt>
                <dd class="text-right text-sm font-medium text-slate-800">{{ $valeur ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>

{{-- Dernières notifications --}}
<div class="mt-4 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
        <h3 class="text-sm font-semibold text-slate-800">Activité récente</h3>
        <a href="{{ route('espace-agent.notifications') }}" class="text-xs font-medium text-institution-600 hover:underline">Tout voir →</a>
    </div>
    <div class="divide-y divide-slate-50">
        @forelse ($dernieresNotifs as $notif)
            <div class="flex items-start gap-3 px-5 py-3.5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notif->lu ? 'bg-slate-300' : 'bg-institution-600' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-800">{{ $notif->titre }}</p>
                    <p class="text-sm text-slate-500">{{ $notif->message }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $notif->created_at?->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <p class="mt-2 text-sm text-slate-400">Aucune activité pour le moment</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
