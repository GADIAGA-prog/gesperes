@extends('layouts.app')
@section('title', 'Activité ' . $activite->code)
@section('header', 'Activité ' . $activite->code)
@section('content')
<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('budget.index') }}" class="text-sm text-institution-600 hover:underline">← Budget des structures</a>
    @can('budget.manage')
    <div class="flex gap-2">
        <a href="{{ route('budget.edit', $activite) }}" class="btn btn-secondary">Modifier</a>
        <button onclick="if(confirm('Supprimer cette activité et ses lignes ?')) document.getElementById('del-act').submit()" class="btn btn-secondary text-red-600">Supprimer</button>
        <form id="del-act" method="POST" action="{{ route('budget.destroy', $activite) }}" class="hidden">@csrf @method('DELETE')</form>
    </div>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Identité --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-1">{{ $activite->libelle }}</h3>
            <p class="text-sm text-gray-500 mb-4">Exercice {{ $activite->exercice }} · Code {{ $activite->code }}</p>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-gray-500">Programme</dt><dd class="font-medium">{{ $activite->action?->programme?->code }} — {{ $activite->action?->programme?->libelle }}</dd></div>
                <div><dt class="text-gray-500">Action</dt><dd class="font-medium">{{ $activite->action?->code }} — {{ $activite->action?->libelle }}</dd></div>
                <div><dt class="text-gray-500">Structure</dt><dd class="font-medium">{{ $activite->structure?->libelle ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Chapitre</dt><dd class="font-medium">{{ $activite->code_chapitre }} {{ $activite->libelle_chapitre ? '('.$activite->libelle_chapitre.')' : '' }}</dd></div>
                @if ($activite->objectif_strategique)<div class="col-span-2"><dt class="text-gray-500">Objectif stratégique</dt><dd>{{ $activite->objectif_strategique }}</dd></div>@endif
                @if ($activite->objectif_operationnel)<div class="col-span-2"><dt class="text-gray-500">Objectif opérationnel</dt><dd>{{ $activite->objectif_operationnel }}</dd></div>@endif
            </dl>
        </div>

        {{-- Lignes budgétaires --}}
        <div class="card overflow-x-auto">
            <h3 class="font-semibold text-gray-700 mb-3">Lignes budgétaires (AE / CP)</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead><tr class="text-left text-xs uppercase text-gray-500">
                    <th class="table-head">Article</th><th class="table-head">Paragraphe</th>
                    <th class="table-head">Catégorie</th>
                    <th class="table-head text-right">AE</th><th class="table-head text-right">CP</th>
                    @can('budget.manage')<th class="table-head"></th>@endcan
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($activite->lignes as $l)
                        <tr>
                            <td class="px-4 py-2 text-sm font-mono">{{ $l->code_article }}</td>
                            <td class="px-4 py-2 text-sm font-mono">{{ $l->code_paragraphe }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($l->libelle_categorie, 45) }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($l->montant_ae, 0, ',', ' ') }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($l->montant_cp, 0, ',', ' ') }}</td>
                            @can('budget.manage')
                            <td class="px-2 py-2 text-right">
                                <form method="POST" action="{{ route('budget.lignes.destroy', [$activite, $l]) }}">@csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 text-xs">✕</button>
                                </form>
                            </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Aucune ligne budgétaire.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="font-semibold border-t-2 border-gray-200">
                    <td colspan="3" class="px-4 py-2 text-sm text-right">Totaux</td>
                    <td class="px-4 py-2 text-sm text-right text-amber-700">{{ number_format($controle['total_ae'], 0, ',', ' ') }}</td>
                    <td class="px-4 py-2 text-sm text-right text-green-700">{{ number_format($controle['total_cp'], 0, ',', ' ') }}</td>
                    @can('budget.manage')<td></td>@endcan
                </tr></tfoot>
            </table>

            @can('budget.manage')
            <form method="POST" action="{{ route('budget.lignes.store', $activite) }}" class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-6 gap-2 items-end">
                @csrf
                <input name="code_article" placeholder="Article" class="input text-sm">
                <input name="code_paragraphe" placeholder="Paragr." class="input text-sm">
                <input name="libelle_categorie" placeholder="Catégorie" class="input text-sm sm:col-span-2">
                <input name="montant_ae" type="number" step="0.01" min="0" placeholder="AE" value="0" class="input text-sm" required>
                <input name="montant_cp" type="number" step="0.01" min="0" placeholder="CP" value="0" class="input text-sm" required>
                <button type="submit" class="btn btn-primary sm:col-span-6 justify-center">+ Ajouter une ligne</button>
            </form>
            @endcan
        </div>
    </div>

    {{-- Programme d'activité --}}
    <div class="space-y-6">
        {{-- Contrôle budgétaire --}}
        <div class="card border-l-4 {{ $controle['coherent'] ? 'border-green-400' : 'border-amber-400' }}">
            <h3 class="font-semibold text-gray-700 mb-2">Contrôle budgétaire</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span class="text-gray-500">Total AE</span><span class="font-semibold text-amber-700">{{ number_format($controle['total_ae'], 0, ',', ' ') }} F</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Total CP</span><span class="font-semibold text-green-700">{{ number_format($controle['total_cp'], 0, ',', ' ') }} F</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Somme trimestres</span>
                    <span class="font-semibold {{ $controle['trimestres_ok'] ? 'text-gray-700' : 'text-red-600' }}">{{ rtrim(rtrim(number_format($controle['somme_trimestres'], 2, ',', ''), '0'), ',') }}</span>
                </div>
            </div>
            <div class="mt-3">
                @if ($controle['coherent'])
                    <p class="text-xs text-green-700 bg-green-50 rounded px-2 py-1">✓ Données budgétaires cohérentes.</p>
                @else
                    @foreach ($controle['messages'] as $m)
                        <p class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1 mb-1">⚠ {{ $m }}</p>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Programme d'activité</h3>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-gray-500">Indicateur</dt><dd>{{ $activite->indicateur ?: '—' }}</dd></div>
                <div class="flex gap-4">
                    <div><dt class="text-gray-500">Valeur initiale</dt><dd class="font-medium">{{ $activite->valeur_initiale ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Cible</dt><dd class="font-medium">{{ $activite->cible ?: '—' }}</dd></div>
                </div>
                <div><dt class="text-gray-500">Localité</dt><dd>{{ $activite->localite ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Montant planifié</dt><dd class="text-lg font-bold text-institution-700">{{ number_format($activite->montant, 0, ',', ' ') }} F</dd></div>
            </dl>
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Ventilation trimestrielle</h3>
            <div class="grid grid-cols-4 gap-2 text-center">
                @foreach (['trimestre_1'=>'T1','trimestre_2'=>'T2','trimestre_3'=>'T3','trimestre_4'=>'T4'] as $champ => $lib)
                    <div class="rounded-lg bg-gray-50 p-2">
                        <p class="text-[10px] text-gray-500">{{ $lib }}</p>
                        <p class="text-sm font-semibold">{{ rtrim(rtrim(number_format($activite->$champ, 2, '.', ''), '0'), '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
