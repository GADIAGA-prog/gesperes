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
        {{-- Structure : cascade hiérarchique (parent → … → service/poste). --}}
        @include('partials.cascade-structure', [
            'nom' => 'structure_id',
            'config' => $structuresCascade,
            'selected' => old('structure_id', $a?->structure_id),
            'label' => "Structure d'affectation",
        ])

        {{-- Fonction (saisie dans l'onglet Carrière), affichée ici en lecture seule. --}}
        <div>
            <label class="label">Fonction</label>
            <div id="affectation-fonction-mirror" class="input bg-gray-50 text-gray-600">{{ $a?->fonction?->libelle ?? '—' }}</div>
            <p class="mt-1 text-xs text-gray-400">Défini dans l'onglet Carrière → Fonction.</p>
        </div>

        <x-form.input name="date_affectation" label="Date d'affectation" type="date" :value="$a?->date_affectation?->toDateString()" />

        <x-form.select name="fiche_poste_id" label="Fiche de poste (titulaire)" :options="$fichesPoste" :selected="$a?->fiche_poste_id" placeholder="— Aucune —" />

        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-3">
            La région, la province et la commune sont déduites automatiquement de la structure d'affectation choisie.
            La fiche de poste liste les postes <strong>adoptés</strong> (Outils GRH → Fiches de poste).
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
    @php $chargeManuelle = $a && (int) ($a->personnes_a_charge ?? 0) !== (int) ($a->nombre_enfants ?? 0); @endphp
    <div x-show="tab === 'famille'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
         x-data="{ chargeManuelle: {{ $chargeManuelle ? 'true' : 'false' }} }">
        <x-form.select name="situation_matrimoniale" label="Situation matrimoniale" :options="$situations" :selected="$a?->situation_matrimoniale?->value" />
        {{-- Personnes à charge = nombre d'enfants par défaut ; saisie libre si modifiée manuellement. --}}
        <x-form.input name="nombre_enfants" label="Nombre d'enfants" type="number" min="0" :value="$a?->nombre_enfants ?? 0"
                      x-on:input="if (! chargeManuelle) $refs.charge.value = $event.target.value" />
        <x-form.input name="personnes_a_charge" label="Personnes à charge (par défaut : nombre d'enfants)" type="number" min="0"
                      :value="$a?->personnes_a_charge ?? 0" x-ref="charge" x-on:input="chargeManuelle = true" />
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
        if (sel.closest('[data-cascade-structure]')) return; // cascade Alpine : selects natifs
        window.agentSelects[sel.id] = new TomSelect(sel, {
            allowEmptyOption: true,
            create: false,
            sortField: { field: 'text', direction: 'asc' },
        });
    });

    // --- Établissement/Poste = Fonction : miroir en lecture seule, synchronisé depuis l'onglet Carrière ---
    const fonctionLabels = @json($fonctions);
    const fonctionSel = document.getElementById('fonction_id');
    const fonctionMirror = document.getElementById('affectation-fonction-mirror');
    const tsFonction = window.agentSelects['fonction_id'];
    if (fonctionSel && fonctionMirror) {
        const majFonction = () => { fonctionMirror.textContent = fonctionLabels[fonctionSel.value] || '—'; };
        if (tsFonction) { tsFonction.on('change', majFonction); } else { fonctionSel.addEventListener('change', majFonction); }
    }
});
</script>
@endpush
