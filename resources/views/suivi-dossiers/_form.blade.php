@php
    $d = $dossier ?? null;

    // Valeurs sélectionnées (édition ou repopulation après erreur de validation).
    $structureSelId = $d?->structure_id ?? ($structureSel ?? old('structure_id'));
    $serviceSelId   = $d?->service_actuel_id ?? old('service_actuel_id');
    $agentSelId     = $d?->agent_actuel_id ?? old('agent_actuel_id');

    // « Structure concernée » = directions ; on garantit l'option courante même
    // si le dossier pointe historiquement vers un service.
    $structureOptions = $directions;
    if ($structureSelId && ! $directions->has((int) $structureSelId)) {
        if ($n = $arbreStructures->firstWhere('id', (int) $structureSelId)) {
            $structureOptions = $directions->toBase()->put($n->id, $n->libelle);
        }
    }

    $serviceSelLib = $serviceSelId ? optional($arbreStructures->firstWhere('id', (int) $serviceSelId))->libelle : null;
    $agentSel = $d?->agentActuel ?? ($agentSelId ? \App\Models\Agent::find($agentSelId) : null);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="reference_bordereau" label="Référence du bordereau" :value="$d?->reference_bordereau ?? old('reference_bordereau')" required />

    <x-form.select name="nature_id" label="Nature du dossier"
        :options="$natures->mapWithKeys(fn($n)=>[$n->id => $n->libelle])"
        :selected="$d?->nature_id ?? old('nature_id')"
        data-delais="{{ $natures->mapWithKeys(fn($n)=>[$n->id => $n->delai_defaut_jours])->toJson() }}" />

    <x-form.input name="objet" label="Objet" :value="$d?->objet ?? old('objet')" class="sm:col-span-2" placeholder="Objet / intitulé du dossier" />

    <x-form.select name="structure_id" label="Structure concernée" :options="$structureOptions" :selected="$structureSelId" required />

    <x-form.select name="etape" label="Étape du dossier" :options="$etapes" :selected="$d?->etape?->value ?? old('etape','reception')" required />

    {{-- Service : filtré en cascade selon la structure (saisie intelligente). --}}
    <div>
        <label for="service_actuel_id" class="label">Service où se situe le dossier</label>
        <select name="service_actuel_id" id="service_actuel_id" class="input">
            <option value="">—</option>
            @if ($serviceSelId && $serviceSelLib)
                <option value="{{ $serviceSelId }}" selected>{{ $serviceSelLib }}</option>
            @endif
        </select>
    </div>

    {{-- Agent en charge : recherche AJAX limitée au sous-arbre de la structure. --}}
    <div>
        <label for="agent_actuel_id" class="label">Agent en charge</label>
        <select name="agent_actuel_id" id="agent_actuel_id" class="input">
            <option value="">—</option>
            @if ($agentSel)
                <option value="{{ $agentSel->id }}" selected>{{ $agentSel->matricule }} — {{ $agentSel->nom_complet }}</option>
            @endif
        </select>
    </div>

    <x-form.input name="date_reception" label="Date de réception" type="date" :value="$d?->date_reception?->toDateString() ?? old('date_reception')" required />

    <x-form.input name="delai_jours" label="Délai de traitement (jours)" type="number" min="0" :value="$d?->delai_jours ?? old('delai_jours', 0)" required />

    <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$d?->statut?->value ?? old('statut','en_cours')" required />

    <x-form.input name="date_traitement" label="Date de traitement (si clôturé)" type="date" :value="$d?->date_traitement?->toDateString() ?? old('date_traitement')" />

    <x-form.textarea name="observation" label="Observation" :value="$d?->observation ?? old('observation')" class="sm:col-span-2" />
</div>

@include('partials.select-recherche') {{-- charge la librairie TomSelect (CSS + JS) --}}

@push('scripts')
<script>
// Pré-remplit le délai avec le délai par défaut de la nature choisie.
document.addEventListener('DOMContentLoaded', function () {
    const nature = document.getElementById('nature_id');
    const delai  = document.getElementById('delai_jours');
    if (nature && delai) {
        const delais = JSON.parse(nature.getAttribute('data-delais') || '{}');
        nature.addEventListener('change', function () {
            const v = delais[this.value];
            if (v !== null && v !== undefined && (!delai.value || delai.value === '0')) delai.value = v;
        });
    }
});

// Cascade Structure → Service → Agent + saisie intelligente.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof TomSelect === 'undefined') return;

    const arbre = @json($arbreStructures);
    const enfants = {};
    arbre.forEach(s => { const p = s.parent_id ?? 0; (enfants[p] = enfants[p] || []).push(s); });
    function descendants(rootId) {
        const out = [], pile = [...(enfants[rootId] || [])];
        while (pile.length) { const n = pile.pop(); out.push(n); (enfants[n.id] || []).forEach(c => pile.push(c)); }
        return out;
    }

    const optsBase = { allowEmptyOption: true, create: false, sortField: { field: 'text', direction: 'asc' } };
    const tsStruct  = new TomSelect('#structure_id', optsBase);
    const tsService = new TomSelect('#service_actuel_id', optsBase);

    const agentsUrl = @json(route('suivi-dossiers.agents'));
    const tsAgent = new TomSelect('#agent_actuel_id', {
        valueField: 'id', labelField: 'text', searchField: 'text',
        allowEmptyOption: true, create: false, preload: false,
        load: function (query, callback) {
            const sid = tsStruct.getValue();
            fetch(agentsUrl + '?structure_id=' + encodeURIComponent(sid || '') + '&q=' + encodeURIComponent(query || ''),
                  { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).then(callback).catch(() => callback());
        },
    });

    function remplirServices(rootId, garder) {
        tsService.clearOptions();
        if (rootId) descendants(rootId).forEach(s => tsService.addOption({ value: String(s.id), text: s.libelle }));
        tsService.refreshOptions(false);
        if (garder) tsService.setValue(garder, true);
    }

    if (tsStruct.getValue()) remplirServices(tsStruct.getValue(), @json((string) ($serviceSelId ?? '')));

    tsStruct.on('change', function (v) {
        remplirServices(v, null);
        tsService.clear();
        tsAgent.clearOptions();
        tsAgent.clear();
    });
});
</script>
@endpush
