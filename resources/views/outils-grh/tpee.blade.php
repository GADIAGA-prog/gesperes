@extends('layouts.app')
@section('title', 'TPEE')
@section('header', 'Outils GRH — Tableau Prévisionnel des Effectifs et des Emplois')

@section('content')
@include('outils-grh._tabs')

@php $annees = $tableau['annees']; @endphp

{{-- Filtres --}}
<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <select name="structure_id" class="input sm:col-span-2" data-recherche>
        <option value="">National (toutes structures)</option>
        @foreach ($structures as $id => $lib)
            <option value="{{ $id }}" {{ (string) ($filtres['structure_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $lib }}</option>
        @endforeach
    </select>
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Filtrer par emploi…" class="input">
    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <a href="{{ route('outils-grh.tpee.pdf', array_filter($filtres)) }}" class="btn btn-secondary">PDF</a>
    </div>
</form>
@include('partials.select-recherche')

<p class="text-sm text-gray-500 mb-3">
    Effectif {{ now()->year }} : <strong>{{ number_format($tableau['total_effectif'], 0, ',', ' ') }}</strong> agent(s) —
    projection sur {{ count($annees) }} ans. Les <em>départs</em> (retraite) et l'effectif sont calculés automatiquement ;
    saisissez les <em>entrées</em> (recrutements prévus) et l'<em>effectif cible</em>.
</p>

<form method="POST" action="{{ route('outils-grh.tpee.store') }}">
    @csrf
    <input type="hidden" name="structure_id" value="{{ $filtres['structure_id'] ?? '' }}">
    <input type="hidden" name="q" value="{{ $filtres['q'] ?? '' }}">

    <div class="card overflow-x-auto">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-700">Projection par emploi</h3>
            @can('tpee.manage')
                <button type="submit" class="btn btn-primary">Enregistrer les prévisions</button>
            @endcan
        </div>

        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="text-xs uppercase text-gray-500">
                    <th class="table-head sticky left-0 bg-gray-50 text-left" rowspan="2">Emploi</th>
                    <th class="table-head text-right" rowspan="2">Effectif<br>{{ now()->year }}</th>
                    @foreach ($annees as $an)
                        <th class="table-head text-center border-l border-gray-200" colspan="5">{{ $an }}</th>
                    @endforeach
                </tr>
                <tr class="text-[11px] uppercase text-gray-400">
                    @foreach ($annees as $an)
                        <th class="table-head text-right border-l border-gray-200" title="Départs à la retraite">Départs</th>
                        <th class="table-head text-right" title="Entrées prévues (recrutements)">Entrées</th>
                        <th class="table-head text-right" title="Effectif prévisionnel de fin d'année">Prév.</th>
                        <th class="table-head text-right" title="Effectif cible">Cible</th>
                        <th class="table-head text-right" title="Écart = cible − effectif prévisionnel">Écart</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tableau['lignes'] as $ligne)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 sticky left-0 bg-white font-medium text-gray-800">{{ $ligne['emploi']->libelle }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ $ligne['effectif'] }}</td>
                        @foreach ($annees as $an)
                            @php $c = $ligne['annees'][$an]; $eid = $ligne['emploi']->id; @endphp
                            <td class="px-2 py-1 text-right border-l border-gray-100 text-gray-600">{{ $c['dep'] ?: '—' }}</td>
                            <td class="px-1 py-1 text-right">
                                @can('tpee.manage')
                                    <input type="number" min="0" name="lignes[{{ $eid }}][{{ $an }}][entrees]"
                                           value="{{ $c['ent'] ?: '' }}" class="input w-16 px-1.5 py-1 text-right text-sm" placeholder="0">
                                @else
                                    {{ $c['ent'] ?: '—' }}
                                @endcan
                            </td>
                            <td class="px-2 py-1 text-right font-medium text-institution-700">{{ $c['fin'] }}</td>
                            <td class="px-1 py-1 text-right">
                                @can('tpee.manage')
                                    <input type="number" min="0" name="lignes[{{ $eid }}][{{ $an }}][cible]"
                                           value="{{ $c['cible'] ?? '' }}" class="input w-16 px-1.5 py-1 text-right text-sm" placeholder="—">
                                @else
                                    {{ $c['cible'] ?? '—' }}
                                @endcan
                            </td>
                            <td class="px-2 py-1 text-right font-semibold {{ is_null($c['ecart']) ? 'text-gray-300' : ($c['ecart'] > 0 ? 'text-red-600' : ($c['ecart'] < 0 ? 'text-amber-600' : 'text-green-600')) }}">
                                {{ is_null($c['ecart']) ? '—' : ($c['ecart'] > 0 ? '+' . $c['ecart'] : $c['ecart']) }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 2 + count($annees) * 5 }}" class="px-4 py-8 text-center text-gray-400">Aucun emploi à projeter dans ce périmètre.</td></tr>
                @endforelse
            </tbody>
            @if (! empty($tableau['lignes']))
                <tfoot>
                    <tr class="bg-gray-50 font-semibold text-gray-700">
                        <td class="px-3 py-2 sticky left-0 bg-gray-50">Total</td>
                        <td class="px-3 py-2 text-right">{{ $tableau['total_effectif'] }}</td>
                        @foreach ($annees as $an)
                            @php $t = $tableau['totaux'][$an]; @endphp
                            <td class="px-2 py-2 text-right border-l border-gray-200">{{ $t['dep'] }}</td>
                            <td class="px-2 py-2 text-right">{{ $t['ent'] }}</td>
                            <td class="px-2 py-2 text-right text-institution-700">{{ $t['fin'] }}</td>
                            <td class="px-2 py-2 text-right">{{ $t['cible'] ?: '—' }}</td>
                            <td class="px-2 py-2 text-right {{ $t['ecart'] > 0 ? 'text-red-600' : ($t['ecart'] < 0 ? 'text-amber-600' : '') }}">{{ $t['ecart'] ?: '—' }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">
        Écart = effectif cible − effectif prévisionnel. Un écart <span class="text-red-600 font-medium">positif</span> = besoin de recrutement ;
        <span class="text-amber-600 font-medium">négatif</span> = sureffectif prévisionnel.
    </p>
</form>
@endsection
