@php
    $a = $activite ?? null;
    $action = $a?->action;
    $programmeId = $action?->programme_id;
    $numero = $action ? (int) substr($a->code, strlen($action->code)) : null;
    $actionsJson = $actions->map(fn ($x) => ['id' => $x->id, 'code' => $x->code, 'libelle' => $x->libelle, 'programme_id' => $x->programme_id])->values();
@endphp

<div x-data="{
        programme: '{{ old('programme_id', $programmeId) }}',
        action: '{{ old('action_id', $a?->action_id) }}',
        numero: '{{ old('numero_activite', $numero) }}',
        actions: @js($actionsJson),
        get filtered() { return this.actions.filter(x => !this.programme || x.programme_id == this.programme); },
        get actionCode() { let x = this.actions.find(o => o.id == this.action); return x ? x.code : ''; },
        get code() { return this.actionCode && this.numero ? this.actionCode + String(this.numero).padStart(2,'0') : '—'; }
     }"
     class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    <x-form.input name="exercice" label="Exercice" type="number" :value="$a?->exercice ?? now()->year" required />
    <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$a?->structure_id" placeholder="— Aucune —" />

    <div>
        <label class="label">Programme <span class="text-red-500">*</span></label>
        <select x-model="programme" class="input">
            <option value="">— Sélectionner —</option>
            @foreach ($programmes as $id => $lbl)<option value="{{ $id }}">{{ $lbl }}</option>@endforeach
        </select>
    </div>

    <div>
        <label class="label">Action <span class="text-red-500">*</span></label>
        <select name="action_id" x-model="action" class="input" required>
            <option value="">— Sélectionner —</option>
            <template x-for="x in filtered" :key="x.id">
                <option :value="x.id" x-text="x.code + ' — ' + x.libelle"></option>
            </template>
        </select>
        @error('action_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label">N° activité (1–99) <span class="text-red-500">*</span></label>
        <input type="number" name="numero_activite" min="1" max="99" x-model="numero" class="input" required>
        @error('numero_activite')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-end">
        <p class="text-sm text-gray-600">Code activité : <span class="font-mono font-bold text-institution-700" x-text="code"></span></p>
    </div>

    <x-form.input name="libelle" label="Libellé de l'activité" :value="$a?->libelle" required class="sm:col-span-2" />

    <x-form.textarea name="objectif_strategique" label="Objectif stratégique" :value="$a?->objectif_strategique" class="sm:col-span-2" />
    <x-form.textarea name="objectif_operationnel" label="Objectif opérationnel" :value="$a?->objectif_operationnel" class="sm:col-span-2" />
    <x-form.input name="indicateur" label="Indicateur" :value="$a?->indicateur" class="sm:col-span-2" />

    <x-form.input name="valeur_initiale" label="Valeur initiale" :value="$a?->valeur_initiale" />
    <x-form.input name="cible" label="Cible" :value="$a?->cible" />
    <x-form.input name="localite" label="Localité(s)" :value="$a?->localite" class="sm:col-span-2" />
    <x-form.input name="montant" label="Montant planifié (FCFA)" type="number" :value="$a?->montant" />

    <div class="sm:col-span-2">
        <label class="label">Ventilation trimestrielle (fractions, doivent totaliser 1)</label>
        <div class="grid grid-cols-4 gap-2"
             x-data="{ t1:{{ (float) old('trimestre_1', $a?->trimestre_1 ?? 0) }}, t2:{{ (float) old('trimestre_2', $a?->trimestre_2 ?? 0) }}, t3:{{ (float) old('trimestre_3', $a?->trimestre_3 ?? 0) }}, t4:{{ (float) old('trimestre_4', $a?->trimestre_4 ?? 0) }},
                       get somme(){ return (Number(this.t1)+Number(this.t2)+Number(this.t3)+Number(this.t4)); } }">
            <input type="number" step="0.01" name="trimestre_1" x-model="t1" placeholder="T1" class="input">
            <input type="number" step="0.01" name="trimestre_2" x-model="t2" placeholder="T2" class="input">
            <input type="number" step="0.01" name="trimestre_3" x-model="t3" placeholder="T3" class="input">
            <input type="number" step="0.01" name="trimestre_4" x-model="t4" placeholder="T4" class="input">
            <p class="col-span-4 text-xs" :class="Math.abs(somme - 1) <= 0.01 || somme === 0 ? 'text-gray-400' : 'text-red-600'">
                Somme : <span x-text="somme.toFixed(2)"></span> <span x-show="Math.abs(somme - 1) > 0.01 && somme !== 0">(doit valoir 1,00)</span>
            </p>
        </div>
    </div>
</div>
