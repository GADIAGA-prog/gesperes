@php $d = $dossier ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="reference_bordereau" label="Référence du bordereau" :value="$d?->reference_bordereau ?? old('reference_bordereau')" required />

    <x-form.select name="nature_id" label="Nature du dossier"
        :options="$natures->mapWithKeys(fn($n)=>[$n->id => $n->libelle])"
        :selected="$d?->nature_id ?? old('nature_id')"
        data-delais="{{ $natures->mapWithKeys(fn($n)=>[$n->id => $n->delai_defaut_jours])->toJson() }}" />

    <x-form.input name="objet" label="Objet" :value="$d?->objet ?? old('objet')" class="sm:col-span-2" placeholder="Objet / intitulé du dossier" />

    <x-form.select name="structure_id" label="Structure concernée" :options="$structures" :selected="$d?->structure_id ?? ($structureSel ?? old('structure_id'))" required />

    <x-form.select name="etape" label="Étape du dossier" :options="$etapes" :selected="$d?->etape?->value ?? old('etape','reception')" required />

    <x-form.select name="service_actuel_id" label="Service où se situe le dossier" :options="$structures" :selected="$d?->service_actuel_id ?? old('service_actuel_id')" />

    <x-form.select name="agent_actuel_id" label="Agent en charge" :options="$agents" :selected="$d?->agent_actuel_id ?? old('agent_actuel_id')" />

    <x-form.input name="date_reception" label="Date de réception" type="date" :value="$d?->date_reception?->toDateString() ?? old('date_reception')" required />

    <x-form.input name="delai_jours" label="Délai de traitement (jours)" type="number" min="0" :value="$d?->delai_jours ?? old('delai_jours', 0)" required />

    <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$d?->statut?->value ?? old('statut','en_cours')" required />

    <x-form.input name="date_traitement" label="Date de traitement (si clôturé)" type="date" :value="$d?->date_traitement?->toDateString() ?? old('date_traitement')" />

    <x-form.textarea name="observation" label="Observation" :value="$d?->observation ?? old('observation')" class="sm:col-span-2" />
</div>

@push('scripts')
<script>
// Pré-remplit le délai de traitement avec le délai par défaut de la nature choisie.
document.addEventListener('DOMContentLoaded', function () {
    const nature = document.getElementById('nature_id');
    const delai  = document.getElementById('delai_jours');
    if (!nature || !delai) return;
    const delais = JSON.parse(nature.getAttribute('data-delais') || '{}');
    nature.addEventListener('change', function () {
        const v = delais[this.value];
        if (v !== null && v !== undefined && (!delai.value || delai.value === '0')) {
            delai.value = v;
        }
    });
});
</script>
@endpush
