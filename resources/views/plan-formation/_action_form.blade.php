@php
    $a = $action ?? null;
    $isEdit = (bool) $a;
    $selectedPublics = $a?->public_cible ?? old('public_cible', []);
@endphp
<form method="POST"
      action="{{ $isEdit ? route('actions-formation.update', $a) : route('actions-formation.store') }}"
      class="border border-institution-200 rounded-lg p-4 bg-institution-50/40">
    @csrf
    @if($isEdit) @method('PUT') @else <input type="hidden" name="programme_formation_id" value="{{ $programme->id }}"> @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-form.input name="numero_ordre" label="N° ordre" type="number" min="0" :value="$a?->numero_ordre ?? old('numero_ordre')" />
        <x-form.input name="action" label="Action de formation" :value="$a?->action ?? old('action')" class="sm:col-span-2" required />
        <x-form.input name="theme_module" label="Thème / module (1)" :value="$a?->theme_module ?? old('theme_module')" class="sm:col-span-3" />

        <x-form.select name="type_modalite" label="Modalité (2)" :options="$enums['modalites']" :selected="$a?->type_modalite?->value ?? old('type_modalite')" />
        <x-form.select name="domaine" label="Domaine" :options="$enums['domaines']" :selected="$a?->domaine?->value ?? old('domaine')" />
        <x-form.select name="axe" label="Axe" :options="$enums['axes']" :selected="$a?->axe?->value ?? old('axe')" />
        <x-form.select name="strategie" label="Stratégie" :options="$enums['strategies']" :selected="$a?->strategie?->value ?? old('strategie')" />
        <x-form.select name="niveau_competence" label="Niveau de compétence" :options="$enums['niveaux']" :selected="$a?->niveau_competence?->value ?? old('niveau_competence')" />
        <x-form.select name="statut" label="Statut" :options="$enums['statuts']" :selected="$a?->statut?->value ?? old('statut','planifiee')" required />

        <div class="sm:col-span-3">
            <label class="label">Public cible (3)</label>
            <div class="flex flex-wrap gap-x-4 gap-y-1">
                @foreach($enums['publics'] as $val => $lib)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="public_cible[]" value="{{ $val }}"
                               {{ in_array($val, (array) $selectedPublics, true) ? 'checked' : '' }}
                               class="rounded border-gray-300">
                        {{ $lib }}
                    </label>
                @endforeach
            </div>
        </div>

        <x-form.input name="nombre_jours" label="Nombre de jours (4)" type="number" min="0" :value="$a?->nombre_jours ?? old('nombre_jours', 0)" required />
        <x-form.input name="nombre_agents" label="Nombre d'agents (5)" type="number" min="0" :value="$a?->nombre_agents ?? old('nombre_agents', 0)" required />
        <x-form.input name="cout" label="Coût FCFA (6)" type="number" min="0" step="0.01" :value="$a?->cout ?? old('cout', 0)" required />
        <x-form.input name="source_financement" label="Source de financement (7)" :value="$a?->source_financement ?? old('source_financement')" class="sm:col-span-2" placeholder="PMAP/DAF, PTF…" />
        <x-form.textarea name="observation" label="Observation" :value="$a?->observation ?? old('observation')" class="sm:col-span-3" rows="2" />
    </div>

    <div class="flex justify-end gap-2 mt-3">
        <button type="button" @click="editing=null" class="btn btn-secondary">Annuler</button>
        <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Ajouter l\'action' }}</button>
    </div>
</form>
