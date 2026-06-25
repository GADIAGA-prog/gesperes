@extends('layouts.app')
@section('title', 'Agents')
@section('header', 'Gestion des effectifs — Agents')

@section('content')
@include('gestion-effectifs._tabs')
<div x-data="agentsRecherche()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-end gap-2 mb-4">
        @can('agents.import')
            <a href="{{ route('agents.import.form') }}" class="btn btn-secondary">Importer</a>
        @endcan
        @can('agents.export')
            <div class="relative" x-data="{ ouvert: false }">
                <button type="button" class="btn btn-secondary" @click="ouvert = ! ouvert">Exporter ▾</button>
                <div x-show="ouvert" x-cloak @click.outside="ouvert = false"
                     class="absolute right-0 z-30 mt-1 w-80 rounded-lg border border-gray-200 bg-white p-3 text-left shadow-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-700">Colonnes à exporter</span>
                        <span class="text-xs">
                            <button type="button" class="text-institution-600 hover:underline" @click="colonnes = [...toutesColonnes]">Tout</button>
                            <button type="button" class="text-gray-500 hover:underline ml-2" @click="colonnes = []">Aucune</button>
                        </span>
                    </div>
                    <div class="max-h-60 overflow-y-auto grid grid-cols-1 gap-1 pr-1">
                        @foreach ($colonnesExport as $cle => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" value="{{ $cle }}" x-model="colonnes" class="rounded border-gray-300 text-institution-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-400" x-text="colonnes.length + ' colonne(s) sélectionnée(s)'"></p>
                    <div class="mt-2 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <a x-bind:href="urlExport('{{ route('agents.export.pdf') }}')" class="btn btn-secondary text-sm">PDF</a>
                        <a x-bind:href="urlExportExcel('{{ route('agents.export') }}')"
                           x-bind:class="! colonnes.length && 'pointer-events-none opacity-50'" class="btn btn-primary text-sm">Excel (CSV)</a>
                    </div>
                </div>
            </div>
        @endcan
        @can('agents.create')
            <a href="{{ route('agents.create') }}" class="btn btn-primary">+ Nouvel agent</a>
        @endcan
    </div>

    {{-- Recherche multicritère en direct : filtre au fil de la frappe, sans bouton.
         Conserve les attributs name/value pour rester fonctionnel sans JavaScript. --}}
    <form method="GET" action="{{ route('agents.index') }}" class="card mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3"
          @submit.prevent="charger()">
        <input type="text" name="q" x-model="q" @input.debounce.350ms="charger()"
               value="{{ $filtres['q'] ?? '' }}" placeholder="Matricule, nom, prénoms…" class="input sm:col-span-2" autocomplete="off">
        <select name="region" x-model="region" @change="charger()" class="input">
            <option value="">Toutes les régions</option>
            @foreach ($regions as $region)
                <option value="{{ $region }}" {{ ($filtres['region'] ?? '') === $region ? 'selected' : '' }}>{{ $region }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="statut_dossier" x-model="statut" @change="charger()" class="input">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $statut)
                    <option value="{{ $statut->value }}" {{ ($filtres['statut_dossier'] ?? '') === $statut->value ? 'selected' : '' }}>{{ $statut->label() }}</option>
                @endforeach
            </select>
            {{-- Indicateur de chargement + fallback sans JS --}}
            <button type="submit" class="btn btn-primary" x-text="chargement ? '…' : 'Filtrer'"></button>
        </div>
    </form>

    <div id="resultats" @click="paginer($event)" x-bind:class="chargement && 'opacity-50 transition'">
        @include('agents._resultats')
    </div>
</div>

@push('scripts')
<script>
function agentsRecherche() {
    return {
        q:       @json($filtres['q'] ?? ''),
        region:  @json($filtres['region'] ?? ''),
        statut:  @json($filtres['statut_dossier'] ?? ''),
        chargement: false,

        // Export : toutes les colonnes cochées par défaut.
        toutesColonnes: @json(array_keys($colonnesExport ?? [])),
        colonnes:       @json(array_keys($colonnesExport ?? [])),

        // Construit la chaîne de paramètres courante (sans page).
        params(page) {
            const p = new URLSearchParams();
            if (this.q)      p.set('q', this.q);
            if (this.region) p.set('region', this.region);
            if (this.statut) p.set('statut_dossier', this.statut);
            if (page && page > 1) p.set('page', page);
            return p;
        },

        // Liens d'export : reprennent les filtres en cours.
        urlExport(base) {
            const qs = this.params().toString();
            return qs ? base + '?' + qs : base;
        },

        // Export Excel : filtres + colonnes choisies.
        urlExportExcel(base) {
            const p = this.params();
            this.colonnes.forEach((c) => p.append('colonnes[]', c));
            const qs = p.toString();
            return qs ? base + '?' + qs : base;
        },

        charger(page = 1) {
            const url = @json(route('agents.index')) + '?' + this.params(page).toString();
            this.fetchInto(url);
        },

        // Pagination interceptée pour rester en AJAX (uniquement les liens de pagination).
        paginer(e) {
            const a = e.target.closest('a');
            if (! a || ! a.closest('[data-pagination]')) return;
            e.preventDefault();
            if (a.href) this.fetchInto(a.href);
        },

        fetchInto(url) {
            this.chargement = true;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.text())
                .then((html) => {
                    document.getElementById('resultats').innerHTML = html;
                    history.replaceState(null, '', url);
                })
                .catch(() => {})
                .finally(() => { this.chargement = false; });
        },
    };
}
</script>
@endpush
@endsection
