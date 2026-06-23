@php $b = $besoin ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @isset($agents)
        <x-form.select name="agent_id" label="Agent" :options="$agents" :selected="$b?->agent_id ?? ($agentSel ?? old('agent_id'))" />
    @endisset
    <x-form.select name="structure_id" label="Structure" :options="$structures" :selected="$b?->structure_id ?? old('structure_id')" />
    <x-form.input name="annee_recueil" label="Année de recueil" type="number" min="2000" max="2100" :value="$b?->annee_recueil ?? old('annee_recueil', now()->year)" required />
    <x-form.select name="domaine" label="Domaine de formation" :options="$domaines" :selected="$b?->domaine?->value ?? old('domaine')" />
    <x-form.input name="theme_souhaite" label="Thème de formation souhaité" :value="$b?->theme_souhaite ?? old('theme_souhaite')" class="sm:col-span-2" required />

    <x-form.textarea name="activite" label="Activité exécutée avec difficulté" :value="$b?->activite ?? old('activite')" rows="2" />
    <x-form.textarea name="taches" label="Tâches concernées" :value="$b?->taches ?? old('taches')" rows="2" />
    <x-form.textarea name="difficultes" label="Difficultés rencontrées" :value="$b?->difficultes ?? old('difficultes')" class="sm:col-span-2" rows="2" />

    <x-form.select name="cause" label="Cause de la difficulté" :options="$causes" :selected="$b?->cause?->value ?? old('cause')" />
    <x-form.select name="solution" label="Solution proposée" :options="$solutions" :selected="$b?->solution?->value ?? old('solution')" />
    <x-form.select name="niveau_maitrise" label="Niveau de maîtrise actuel" :options="$niveaux" :selected="$b?->niveau_maitrise?->value ?? old('niveau_maitrise')" />
    <x-form.select name="frequence" label="Fréquence de la tâche" :options="$frequences" :selected="$b?->frequence?->value ?? old('frequence')" />

    <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$b?->statut ?? old('statut','exprime')" required />
    <x-form.textarea name="observation" label="Observation" :value="$b?->observation ?? old('observation')" class="sm:col-span-2" rows="2" />
</div>
@isset($agents)
@push('head')<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>document.addEventListener('DOMContentLoaded',function(){const e=document.getElementById('agent_id');if(e&&typeof TomSelect!=='undefined'&&!e.tomselect)new TomSelect(e,{allowEmptyOption:true,create:false});});</script>
@endpush
@endisset
