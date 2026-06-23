@php $a = $agent ?? null; @endphp

<div id="agent-fields" x-data="{ tab: 'etat' }">
    <div class="flex flex-wrap gap-2 border-b border-gray-200 mb-5">
        @foreach (['etat' => 'État civil', 'carriere' => 'Carrière', 'affectation' => 'Affectation', 'enseignement' => 'Enseignement', 'famille' => 'Famille'] as $key => $libelle)
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500'"
                    class="px-3 py-2 -mb-px border-b-2 text-sm font-medium">{{ $libelle }}</button>
        @endforeach
    </div>

    {{-- État civil --}}
    <div x-show="tab === 'etat'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.input name="matricule" label="Matricule" :value="$a?->matricule" required />
        <x-form.input name="cle" label="Clé" :value="$a?->cle" />
        <x-form.select name="sexe" label="Sexe" :options="$sexes" :selected="$a?->sexe?->value" required />
        <x-form.input name="nom" label="Nom" :value="$a?->nom" required />
        <x-form.input name="prenoms" label="Prénom(s)" :value="$a?->prenoms" required />
        <x-form.input name="date_naissance" label="Date de naissance" type="date" :value="$a?->date_naissance?->toDateString()" />
        <x-form.input name="nationalite" label="Nationalité" :value="$a?->nationalite ?? 'Burkinabè'" />
        <x-form.input name="telephone" label="Téléphone" :value="$a?->telephone" />
        <x-form.input name="email" label="E-mail" type="email" :value="$a?->email" />
        <x-form.textarea name="adresse" label="Adresse" :value="$a?->adresse" class="sm:col-span-2 lg:col-span-3" />
    </div>

    {{-- Carrière --}}
    <div x-show="tab === 'carriere'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.select name="emploi_id" label="Emploi" :options="$emplois->pluck('libelle', 'id')" :selected="$a?->emploi_id" />
        <x-form.select name="fonction_id" label="Fonction" :options="$fonctions" :selected="$a?->fonction_id" />
        <x-form.select name="poste_id" label="Poste" :options="$postes" :selected="$a?->poste_id" />
        <x-form.select name="categorie_id" label="Catégorie" :options="$categories" :selected="$a?->categorie_id" />
        <x-form.select name="echelle_id" label="Échelle" :options="$echelles" :selected="$a?->echelle_id" />
        <x-form.select name="classe_id" label="Classe" :options="$classes" :selected="$a?->classe_id" />
        <x-form.select name="echelon_id" label="Échelon" :options="$echelons" :selected="$a?->echelon_id" />
        <x-form.select name="indice_id" label="Indice" :options="$indices" :selected="$a?->indice_id" />
        <x-form.select name="position_administrative_id" label="Position administrative" :options="$positions" :selected="$a?->position_administrative_id" />
        <x-form.input name="date_integration" label="Date d'intégration" type="date" :value="$a?->date_integration?->toDateString()" />
        <x-form.input name="date_effet_emploi" label="Effet de l'emploi" type="date" :value="$a?->date_effet_emploi?->toDateString()" />
        <x-form.input name="date_nomination" label="Date de nomination" type="date" :value="$a?->date_nomination?->toDateString()" />
        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-3">
            L'emploi pré-remplit la catégorie (et l'échelle si unique). L'indice se complète dès que catégorie, échelle, classe et échelon sont renseignés.
            La date de retraite et l'allocation familiale sont calculées automatiquement.
        </p>
    </div>

    {{-- Affectation --}}
    <div x-show="tab === 'affectation'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$a?->structure_id" />
        <x-form.select name="region_id" label="Région" :options="$regions" :selected="$a?->region_id" />

        {{-- Province/Circonscription & Commune : options injectées en cascade par JS --}}
        <div>
            <label for="province_id" class="label">Province / Circonscription</label>
            <select name="province_id" id="province_id" class="input" data-selected="{{ old('province_id', $a?->province_id) }}">
                <option value="">—</option>
            </select>
            @error('province_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="localite_id" class="label">Commune</label>
            <select name="localite_id" id="localite_id" class="input" data-selected="{{ old('localite_id', $a?->localite_id) }}">
                <option value="">—</option>
            </select>
            @error('localite_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        {{-- Établissement / Poste : liste déroulante des établissements (saisie libre possible
             pour un service/poste). Le lien hiérarchique établissement ↔ structure vit dans le module Structure. --}}
        <div>
            <label for="etablissement" class="label">Établissement / Poste</label>
            <input type="text" name="etablissement" id="etablissement" list="liste-etablissements" class="input"
                   value="{{ old('etablissement', $a?->etablissement) }}" placeholder="Sélectionner un établissement ou saisir un poste…">
            <datalist id="liste-etablissements">
                @foreach ($etablissements ?? [] as $etab)
                    <option value="{{ $etab }}"></option>
                @endforeach
            </datalist>
            @error('etablissement')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <x-form.input name="date_affectation" label="Date d'affectation" type="date" :value="$a?->date_affectation?->toDateString()" />
        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-3">
            Sélectionnez la région : la province (ou la circonscription d'éducation pour Kadiogo et Guiriko) puis la commune se filtrent automatiquement.
        </p>
    </div>

    {{-- Enseignement --}}
    <div x-show="tab === 'enseignement'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.select name="type_enseignement_id" label="Type d'enseignement" :options="$typesEns" :selected="$a?->type_enseignement_id" />
        <x-form.select name="specialite_id" label="Spécialité" :options="$specialites" :selected="$a?->specialite_id" />
        <x-form.input name="volume_horaire_du" label="Volume horaire dû" type="number" :value="$a?->volume_horaire_du" />
        <x-form.input name="volume_horaire_assure" label="Volume horaire assuré" type="number" :value="$a?->volume_horaire_assure" />
        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-3">Le lieu d'exercice est déduit automatiquement selon l'emploi (enseignant ou administratif).</p>
    </div>

    {{-- Famille --}}
    <div x-show="tab === 'famille'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.select name="situation_matrimoniale" label="Situation matrimoniale" :options="$situations" :selected="$a?->situation_matrimoniale?->value" />
        <x-form.input name="nombre_enfants" label="Nombre d'enfants" type="number" :value="$a?->nombre_enfants ?? 0" />
        <x-form.input name="personnes_a_charge" label="Personnes à charge" type="number" :value="$a?->personnes_a_charge ?? 0" />
        <x-form.input name="distinction_honorifique" label="Distinction honorifique" :value="$a?->distinction_honorifique" />
        <x-form.textarea name="observations" label="Observations" :value="$a?->observations" class="sm:col-span-2 lg:col-span-3" />
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Cartes de la grille indiciaire : Catégorie → Échelle → Classe → Échelon → Indice.
    const emploiCat   = @json($emploiCategorie);   // emploi_id  → categorie_id
    const catEchelles = @json($categorieEchelles); // categorie_id → [echelle_id, …]
    const indices     = @json($indiceGrille);      // "cat-ech-cls-ech" → indice_id

    const byId = (id) => document.getElementById(id);
    const emploi    = byId('emploi_id');
    const categorie = byId('categorie_id');
    const echelle   = byId('echelle_id');
    const classe    = byId('classe_id');
    const echelon   = byId('echelon_id');
    const indice    = byId('indice_id');
    if (! emploi || ! categorie) return;

    const setVal = (el, val) => {
        if (! el) return;
        const v = (val === undefined || val === null) ? '' : String(val);
        el.value = v;
        const ts = window.agentSelects && window.agentSelects[el.id];
        if (ts) ts.setValue(v, true); // reflète la valeur dans Tom Select sans déclencher d'événement
    };

    function syncEchelleDepuisCategorie() {
        const liste = (catEchelles[categorie.value] || []).map(String);
        if (liste.length === 1) {
            setVal(echelle, liste[0]);                          // une seule échelle → auto-sélection
        } else if (echelle.value && ! liste.includes(echelle.value)) {
            setVal(echelle, '');                                // échelle hors catégorie → on vide
        }
    }

    function calculerIndice() {
        if (categorie.value && echelle.value && classe.value && echelon.value) {
            const cle = [categorie.value, echelle.value, classe.value, echelon.value].join('-');
            if (indices[cle] !== undefined) setVal(indice, indices[cle]);
        }
    }

    emploi.addEventListener('change', function () {
        const cat = emploiCat[emploi.value];
        if (cat !== undefined) {
            setVal(categorie, cat);
            syncEchelleDepuisCategorie();
            calculerIndice();
        }
    });

    categorie.addEventListener('change', function () {
        syncEchelleDepuisCategorie();
        calculerIndice();
    });

    [echelle, classe, echelon].forEach((el) => el && el.addEventListener('change', calculerIndice));
})();
</script>
@endpush

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const conteneur = document.getElementById('agent-fields');
    if (! conteneur || typeof TomSelect === 'undefined') return;

    // Saisie intelligente : toutes les listes déroulantes deviennent recherchables.
    window.agentSelects = {};
    conteneur.querySelectorAll('select').forEach(function (sel) {
        window.agentSelects[sel.id] = new TomSelect(sel, {
            allowEmptyOption: true,
            create: false,
            sortField: { field: 'text', direction: 'asc' },
        });
    });

    // --- Cascade géographique : Région → Province/Circonscription → Commune ---
    const provincesParRegion   = @json($provincesParRegion);
    const localitesParProvince = @json($localitesParProvince);

    const regionSel   = document.getElementById('region_id');
    const provinceSel = document.getElementById('province_id');
    const localiteSel = document.getElementById('localite_id');
    const tsProvince  = window.agentSelects['province_id'];
    const tsLocalite  = window.agentSelects['localite_id'];
    const tsRegion    = window.agentSelects['region_id'];
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
