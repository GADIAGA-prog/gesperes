@extends('layouts.app')
@section('title', 'Pointage journalier')
@section('header', 'Contrôle présence — Pointage journalier (Fiche A)')
@section('content')
@include('controle-presence._tabs')

{{-- Sélection structure + date --}}
<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$structureId" required data-recherche />
    <x-form.input name="date" label="Date" type="date" :value="$date" required />
    <div class="flex items-end">
        <button type="submit" class="btn btn-primary">Charger</button>
    </div>
</form>
@include('partials.select-recherche')

<p class="-mt-2 mb-4 text-xs text-gray-400">
    La sélection d'une structure charge aussi les agents de tous ses services (cascade).
</p>

@if ($structureId && $agents->isEmpty())
    <div class="card text-center text-gray-400 py-8">Aucun agent rattaché à cette structure.</div>
@elseif ($structureId)
    <form method="POST" action="{{ route('pointages.store') }}">
        @csrf
        <input type="hidden" name="structure_id" value="{{ $structureId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <div class="card overflow-x-auto">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">{{ $structure?->libelle }}</span> ·
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }} ·
                    {{ $agents->count() }} agent(s)
                </p>
                <div class="flex gap-2">
                    @can('presence.reports')
                        <a href="{{ route('fiches.a', ['structure_id' => $structureId, 'date' => $date, 'format' => 'pdf']) }}"
                           target="_blank" class="btn btn-secondary">Fiche A (PDF)</a>
                    @endcan
                    @can('pointage.manage')
                        <button type="submit" class="btn btn-primary">Enregistrer le pointage</button>
                    @endcan
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead><tr class="text-left text-xs uppercase text-gray-500">
                    <th class="table-head">N°</th>
                    <th class="table-head">Agent</th>
                    <th class="table-head">Matricule</th>
                    <th class="table-head">Emploi / Fonction</th>
                    <th class="table-head text-center">Présent</th>
                    <th class="table-head">Motif d'absence</th>
                    <th class="table-head">Durée (j / h)</th>
                    <th class="table-head">Référence pièce</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($agents as $i => $agent)
                        @php $p = $pointages->get($agent->id); $present = $p ? $p->present : true; @endphp
                        <tr x-data="{ present: {{ $present ? 'true' : 'false' }} }" class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-2.5 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5 text-sm font-medium">{{ trim($agent->nom.' '.$agent->prenoms) }}</td>
                            <td class="px-4 py-2.5 text-sm font-mono">{{ $agent->matricule }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-600">{{ $agent->emploi?->libelle ?: '—' }}<br>{{ $agent->fonction?->libelle }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <input type="hidden" name="lignes[{{ $agent->id }}][present]" :value="present ? 1 : 0">
                                <button type="button" @click="present = !present"
                                        class="inline-flex items-center justify-center h-7 w-16 rounded-full text-xs font-semibold transition"
                                        :class="present ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        x-text="present ? 'Présent' : 'Absent'"></button>
                            </td>
                            <td class="px-4 py-2.5">
                                <select name="lignes[{{ $agent->id }}][motif_absence_id]" x-bind:disabled="present"
                                        class="input text-sm" :class="present ? 'opacity-40' : ''">
                                    <option value="">— sans motif (injustifiée) —</option>
                                    @foreach ($motifs as $m)
                                        <option value="{{ $m->id }}" @selected($p && $p->motif_absence_id == $m->id)>{{ $m->libelle }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex gap-1" x-show="!present" x-cloak>
                                    <input type="number" step="0.5" min="0" max="1" name="lignes[{{ $agent->id }}][duree_jours]"
                                           value="{{ $p?->duree_jours ?: '' }}" placeholder="j" class="input text-sm w-16">
                                    <input type="number" step="0.5" min="0" max="24" name="lignes[{{ $agent->id }}][duree_heures]"
                                           value="{{ $p?->duree_heures ?: '' }}" placeholder="h" class="input text-sm w-16">
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="text" name="lignes[{{ $agent->id }}][reference_piece]" x-show="!present" x-cloak
                                       value="{{ $p?->reference_piece }}" placeholder="réf. / mesure" class="input text-sm">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @can('pointage.manage')
                <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">Enregistrer le pointage</button>
                </div>
            @endcan
        </div>
    </form>
@else
    <div class="card text-center text-gray-400 py-8">Sélectionnez une structure et une date, puis cliquez sur « Charger ».</div>
@endif
@endsection
