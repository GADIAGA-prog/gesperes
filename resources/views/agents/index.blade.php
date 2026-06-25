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

    {{-- Recherche multicritère unique en direct : matricule, nom, prénoms, emploi,
         structure (cascade). Filtre au fil de la frappe, sans bouton.
         Conserve les attributs name/value pour rester fonctionnel sans JavaScript. --}}
    <form method="GET" action="{{ route('agents.index') }}" class="card mb-4 flex gap-2"
          @submit.prevent="charger()">
        <input type="text" name="q" x-model="q" @input.debounce.350ms="charger()"
               value="{{ $filtres['q'] ?? '' }}"
               placeholder="Rechercher : matricule, nom, prénoms, emploi, structure…"
               class="input flex-1" autocomplete="off">
        {{-- Indicateur de chargement + fallback sans JS --}}
        <button type="submit" class="btn btn-primary" x-text="chargement ? '…' : 'Filtrer'"></button>
    </form>

    <div id="resultats" @click="naviguer($event)" @change="filtrerSi($event)" x-bind:class="chargement && 'opacity-50 transition'">
        @include('agents._resultats')
    </div>
</div>

@push('scripts')
<script>
function agentsRecherche() {
    return {
        q:       @json($filtres['q'] ?? ''),
        chargement: false,

        // Export : toutes les colonnes cochées par défaut.
        toutesColonnes: @json(array_keys($colonnesExport ?? [])),
        colonnes:       @json(array_keys($colonnesExport ?? [])),

        // Construit la chaîne de paramètres courante : recherche globale + tri
        // (lu de l'URL) + filtres de colonne (lus du tableau affiché).
        params(page) {
            const p = new URLSearchParams();
            if (this.q) p.set('q', this.q);

            const courant = new URL(window.location.href).searchParams;
            if (courant.get('tri'))  p.set('tri', courant.get('tri'));
            if (courant.get('sens')) p.set('sens', courant.get('sens'));

            document.querySelectorAll('#resultats [data-filtre]').forEach((el) => {
                if (el.value) p.set(el.name, el.value);
            });

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

        // Liens interceptés pour rester en AJAX : pagination + tri (en-têtes).
        // On repart des paramètres courants (q + filtres) et on greffe tri/sens/page du lien cliqué.
        naviguer(e) {
            const a = e.target.closest('a');
            if (! a || ! a.href) return;
            if (! a.closest('[data-pagination]') && ! a.closest('thead')) return;
            e.preventDefault();

            const src = new URL(a.href);
            const p = this.params();
            ['tri', 'sens', 'page'].forEach((k) => {
                const v = src.searchParams.get(k);
                v ? p.set(k, v) : p.delete(k);
            });
            this.fetchInto(@json(route('agents.index')) + '?' + p.toString());
        },

        // Filtre de colonne appliqué au "change" (Entrée / sortie de champ) pour
        // éviter de recharger le tableau — et perdre le focus — à chaque frappe.
        filtrerSi(e) {
            if (! e.target.matches('[data-filtre]')) return;
            this.charger();
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
