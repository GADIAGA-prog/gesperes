@php $s = $structure ?? null; @endphp
<div id="structure-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="code" label="Code" :value="$s?->code" required />
    <x-form.input name="libelle" label="Libellé" :value="$s?->libelle" required />
    <x-form.select name="type" label="Type" :options="$types" :selected="$s?->type?->value" required />
    <x-form.select name="parent_id" label="Structure parente" :options="$parents" :selected="$s?->parent_id" placeholder="— Aucune (racine) —" />

    <x-form.select name="region_id" label="Région" :options="$regions" :selected="$s?->region_id" />

    {{-- Province/Circonscription & Commune : options injectées en cascade par JS --}}
    <div>
        <label for="province_id" class="label">Province / Circonscription</label>
        <select name="province_id" id="province_id" class="input" data-selected="{{ old('province_id', $s?->province_id) }}">
            <option value="">—</option>
        </select>
        @error('province_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="localite_id" class="label">Commune</label>
        <select name="localite_id" id="localite_id" class="input" data-selected="{{ old('localite_id', $s?->localite_id) }}">
            <option value="">—</option>
        </select>
        @error('localite_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-form.select name="action_id" label="Action budgétaire" :options="$actions ?? []" :selected="$s?->action_id" placeholder="— Aucune —" />

    {{-- Responsable : recherche AJAX (43k agents) — options chargées à la frappe --}}
    <div>
        <label for="responsable_agent_id" class="label">Responsable de la structure</label>
        <select name="responsable_agent_id" id="responsable_agent_id" class="input">
            <option value="">— Aucun —</option>
            @if ($s?->responsable)
                <option value="{{ $s->responsable->id }}" selected>{{ $s->responsable->matricule }} — {{ $s->responsable->nom_complet }}</option>
            @endif
        </select>
        @error('responsable_agent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700 mt-2">
        <input type="checkbox" name="actif" value="1" {{ old('actif', $s?->actif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-institution-600">
        Structure active
    </label>
    <p class="text-xs text-gray-400 sm:col-span-2">
        Le type décrit la nature de la structure ; sa position hiérarchique est déduite de la structure parente.
        La province (ou la circonscription d'éducation pour Kadiogo et Guiriko) puis la commune se filtrent selon la région.
    </p>
</div>

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conteneur = document.getElementById('structure-fields');
    if (! conteneur || typeof TomSelect === 'undefined') return;

    // Saisie intelligente : toutes les listes déroulantes deviennent recherchables.
    const selects = {};
    conteneur.querySelectorAll('select').forEach(function (sel) {
        if (sel.id === 'responsable_agent_id') return; // initialisé séparément (recherche AJAX distante)
        selects[sel.id] = new TomSelect(sel, {
            allowEmptyOption: true,
            create: false,
            sortField: { field: 'text', direction: 'asc' },
        });
    });

    // --- Responsable : recherche distante d'agents (parmi ~43 000) ---
    const urlRechercheAgents = @json(route('structures.responsables.recherche'));
    if (document.getElementById('responsable_agent_id')) {
        new TomSelect('#responsable_agent_id', {
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            allowEmptyOption: true,
            create: false,
            preload: false,
            shouldLoad: (q) => q.length >= 2, // évite une requête à chaque caractère
            load: function (query, callback) {
                fetch(urlRechercheAgents + '?q=' + encodeURIComponent(query), { headers: { Accept: 'application/json' } })
                    .then((r) => r.json())
                    .then((json) => callback(json))
                    .catch(() => callback());
            },
        });
    }

    // --- Cascade géographique : Région → Province/Circonscription → Commune ---
    const provincesParRegion   = @json($provincesParRegion);
    const localitesParProvince = @json($localitesParProvince);

    const regionSel   = document.getElementById('region_id');
    const provinceSel = document.getElementById('province_id');
    const localiteSel = document.getElementById('localite_id');
    const tsRegion    = selects['region_id'];
    const tsProvince  = selects['province_id'];
    const tsLocalite  = selects['localite_id'];
    if (! regionSel || ! tsProvince || ! tsLocalite) return;

    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function remplir(selectEl, ts, items, valeur) {
        let html = '<option value="">—</option>';
        items.forEach((it) => {
            const attr = String(it.id) === String(valeur) ? ' selected' : '';
            html += '<option value="' + it.id + '"' + attr + '>' + escapeHtml(it.libelle) + '</option>';
        });
        selectEl.innerHTML = html;
        ts.sync(); // Tom Select relit les options et la valeur depuis le <select>
    }

    const majProvinces = (valeur) => remplir(provinceSel, tsProvince, provincesParRegion[regionSel.value] || [], valeur);
    const majCommunes  = (valeur) => remplir(localiteSel, tsLocalite, localitesParProvince[provinceSel.value] || [], valeur);

    // Initialisation (édition ou repopulation après erreur de validation).
    majProvinces(provinceSel.dataset.selected || '');
    majCommunes(localiteSel.dataset.selected || '');

    tsRegion.on('change', function () { majProvinces(''); majCommunes(''); });
    tsProvince.on('change', function () { majCommunes(''); });
});
</script>
@endpush
