@php $a = $agent ?? null; @endphp

<div x-data="{ tab: 'etat' }">
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
        <p class="text-xs text-gray-400 sm:col-span-2 lg:col-span-3">La date de retraite et l'allocation familiale sont calculées automatiquement.</p>
    </div>

    {{-- Affectation --}}
    <div x-show="tab === 'affectation'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$a?->structure_id" />
        <x-form.input name="region" label="Région" :value="$a?->region" />
        <x-form.input name="province" label="Province" :value="$a?->province" />
        <x-form.input name="commune" label="Commune" :value="$a?->commune" />
        <x-form.input name="etablissement" label="Établissement" :value="$a?->etablissement" />
        <x-form.select name="localite_id" label="Localité" :options="$localites" :selected="$a?->localite_id" />
        <x-form.input name="date_affectation" label="Date d'affectation" type="date" :value="$a?->date_affectation?->toDateString()" />
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
