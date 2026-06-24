@php
    $f = $fiche ?? null;
    $famillesOpt      = $famillesPro->mapWithKeys(fn ($x) => [$x->id => $x->code . ' — ' . $x->libelle]);
    $emploisTypesOpt  = $emploisTypes->mapWithKeys(fn ($x) => [$x->id => $x->code . ' — ' . $x->libelle]);
    $competencesOpt   = $competences->mapWithKeys(fn ($x) => [$x->id => $x->libelle . ($x->domaine ? ' (' . $x->domaine . ')' : '')]);

    $activitesInit   = old('activites', $f ? $f->activites->map(fn ($a) => ['libelle' => $a->libelle, 'taux_contribution' => $a->taux_contribution])->values()->all() : []);
    $indicateursInit = old('indicateurs', $f ? $f->indicateurs->map(fn ($i) => ['libelle' => $i->libelle, 'nature' => $i->nature])->values()->all() : []);
    $competencesInit = old('competences', $f ? $f->competences->map(fn ($c) => ['competence_id' => $c->id, 'type' => $c->pivot->type, 'niveau' => $c->pivot->niveau])->values()->all() : []);
@endphp

<div x-data="{
        activites: @js($activitesInit ?: [['libelle' => '', 'taux_contribution' => '']]),
        indicateurs: @js($indicateursInit ?: [['libelle' => '', 'nature' => '']]),
        competences: @js($competencesInit ?: []),
     }" class="space-y-6">

    {{-- ===== DESCRIPTIF — Identification ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Identification du poste</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-form.input name="intitule" label="Intitulé du poste" :value="$f?->intitule" required class="sm:col-span-2" />
            <x-form.input name="code" label="Code (auto si vide)" :value="$f?->code" placeholder="GRH-RT-CPR-4" />
            <x-form.select name="type_poste" label="Type de poste" :options="$typesPoste" :selected="$f?->type_poste?->value" required />
            <x-form.select name="position_mission" label="Position / mission" :options="$positionsMission" :selected="$f?->position_mission?->value" placeholder="—" />
            <x-form.select name="position_hierarchique" label="Position hiérarchique (chiffre du code)" :options="$positionsHierarchique" :selected="$f?->position_hierarchique?->value" placeholder="—" />
            <x-form.select name="famille_professionnelle_id" label="Famille professionnelle" :options="$famillesOpt" :selected="$f?->famille_professionnelle_id" placeholder="—" />
            <x-form.select name="emploi_type_id" label="Emploi-type" :options="$emploisTypesOpt" :selected="$f?->emploi_type_id" placeholder="—" />
            <x-form.input name="famille_emplois" label="Famille d'emplois" :value="$f?->famille_emplois" />
            <x-form.select name="emploi_id" label="Emploi" :options="$emplois" :selected="$f?->emploi_id" placeholder="—" />
            <x-form.select name="categorie_id" label="Catégorie" :options="$categories" :selected="$f?->categorie_id" placeholder="—" />
            <x-form.select name="structure_id" label="Unité administrative (structure)" :options="$structures" :selected="$f?->structure_id" placeholder="—" />
        </div>
    </section>

    {{-- ===== Mission ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Mission du poste</h3>
        <x-form.textarea name="mission" label="Raison d'être / finalité (verbes d'action)" :value="$f?->mission" />
    </section>

    {{-- ===== Activités permanentes (répéteur) ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Activités permanentes</h3>
        <template x-for="(row, i) in activites" :key="i">
            <div class="flex gap-2 mb-2">
                <input type="text" class="input flex-1" placeholder="Activité (verbe d'action)" x-model="row.libelle" :name="`activites[${i}][libelle]`">
                <input type="text" class="input w-32" placeholder="Taux %" x-model="row.taux_contribution" :name="`activites[${i}][taux_contribution]`">
                <button type="button" class="btn btn-secondary" @click="activites.splice(i, 1)">✕</button>
            </div>
        </template>
        <button type="button" class="btn btn-secondary text-sm mt-1" @click="activites.push({libelle: '', taux_contribution: ''})">+ Ajouter une activité</button>
    </section>

    {{-- ===== Relations du poste ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Relations du poste</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form.input name="niveau_hierarchique_superieur" label="Niveau hiérarchique supérieur" :value="$f?->niveau_hierarchique_superieur" />
            <x-form.input name="niveau_hierarchique_inferieur" label="Niveau hiérarchique inférieur" :value="$f?->niveau_hierarchique_inferieur" />
            <x-form.textarea name="relations_internes" label="Relations fonctionnelles internes" :value="$f?->relations_internes" />
            <x-form.textarea name="relations_externes" label="Relations fonctionnelles externes" :value="$f?->relations_externes" />
        </div>
    </section>

    {{-- ===== PROFIL — Dimension ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Dimension du poste</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form.textarea name="moyens_generaux" label="Moyens généraux" :value="$f?->moyens_generaux" />
            <x-form.textarea name="moyens_specifiques" label="Moyens spécifiques" :value="$f?->moyens_specifiques" />
        </div>
    </section>

    {{-- ===== Compétences requises (répéteur) ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Compétences requises</h3>
        <template x-for="(row, i) in competences" :key="i">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 mb-2">
                <select class="input sm:col-span-6" x-model="row.competence_id" :name="`competences[${i}][competence_id]`">
                    <option value="">— Compétence —</option>
                    @foreach ($competencesOpt as $id => $lib)
                        <option value="{{ $id }}">{{ $lib }}</option>
                    @endforeach
                </select>
                <select class="input sm:col-span-3" x-model="row.type" :name="`competences[${i}][type]`">
                    @foreach ($typesCompetence as $val => $lib)
                        <option value="{{ $val }}">{{ $lib }}</option>
                    @endforeach
                </select>
                <select class="input sm:col-span-2" x-model="row.niveau" :name="`competences[${i}][niveau]`">
                    @foreach ($niveauxCompetence as $val => $lib)
                        <option value="{{ $val }}">{{ $lib }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-secondary sm:col-span-1" @click="competences.splice(i, 1)">✕</button>
            </div>
        </template>
        <button type="button" class="btn btn-secondary text-sm mt-1"
                @click="competences.push({competence_id: '', type: 'metier', niveau: 'application'})">+ Ajouter une compétence</button>
        <p class="text-xs text-gray-400 mt-2">Les compétences proviennent du dictionnaire (référentiel Compétences).</p>
    </section>

    {{-- ===== Conditions d'accès ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Conditions d'accès au poste</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-form.input name="niveau_etudes" label="Niveau d'études" :value="$f?->niveau_etudes" />
            <x-form.input name="domaine" label="Domaine" :value="$f?->domaine" />
            <x-form.input name="specialite" label="Spécialité" :value="$f?->specialite" />
            <x-form.input name="experience_pro" label="Expérience professionnelle" :value="$f?->experience_pro" />
        </div>
    </section>

    {{-- ===== Indicateurs de performance (répéteur) ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Indicateurs de performance</h3>
        <template x-for="(row, i) in indicateurs" :key="i">
            <div class="flex gap-2 mb-2">
                <input type="text" class="input flex-1" placeholder="Indicateur" x-model="row.libelle" :name="`indicateurs[${i}][libelle]`">
                <select class="input w-40" x-model="row.nature" :name="`indicateurs[${i}][nature]`">
                    <option value="">Nature…</option>
                    <option value="quantitatif">Quantitatif</option>
                    <option value="qualitatif">Qualitatif</option>
                </select>
                <button type="button" class="btn btn-secondary" @click="indicateurs.splice(i, 1)">✕</button>
            </div>
        </template>
        <button type="button" class="btn btn-secondary text-sm mt-1" @click="indicateurs.push({libelle: '', nature: ''})">+ Ajouter un indicateur</button>
    </section>

    {{-- ===== Cycle de vie ===== --}}
    <section class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Statut</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$f?->statut?->value ?? 'brouillon'" placeholder="—" />
            <x-form.input name="version" label="Version" :value="$f?->version" placeholder="ex. 2026" />
        </div>
    </section>
</div>
