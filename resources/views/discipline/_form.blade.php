@php $d = $dossier ?? null; @endphp
<div id="discipline-fields" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @isset($agents)
        <x-form.select name="agent_id" label="Agent" :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.$a->nom_complet])" :selected="$agentSel ?? old('agent_id')" required />
    @else
        <div><label class="label">Agent</label><input class="input bg-gray-100" value="{{ $d->agent?->nom_complet }}" disabled><input type="hidden" name="agent_id" value="{{ $d->agent_id }}"></div>
    @endisset
    <x-form.select name="type" label="Type d'acte" :options="$types" :selected="$d?->type?->value ?? old('type')" required />
    <x-form.input name="date_acte" label="Date de l'acte" type="date" :value="$d?->date_acte?->toDateString() ?? old('date_acte')" required />
    <x-form.input name="reference_acte" label="Référence" :value="$d?->reference_acte ?? old('reference_acte')" />
    <x-form.input name="nature" label="Nature (si sanction)" :value="$d?->nature ?? old('nature')" placeholder="avertissement, blâme, exclusion…" />
    <x-form.select name="statut" label="Statut" :options="['ouvert'=>'Ouvert','clos'=>'Clos']" :selected="$d?->statut ?? old('statut','ouvert')" required />
    <x-form.textarea name="motif" label="Motif / faits" :value="$d?->motif ?? old('motif')" class="sm:col-span-2" required />
    <x-form.textarea name="decision" label="Décision" :value="$d?->decision ?? old('decision')" class="sm:col-span-2" />
    <x-form.textarea name="observation" label="Observation" :value="$d?->observation ?? old('observation')" class="sm:col-span-2" />
</div>
@isset($agents)
@push('head')<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>document.addEventListener('DOMContentLoaded',function(){const e=document.getElementById('agent_id');if(e&&typeof TomSelect!=='undefined'&&!e.tomselect)new TomSelect(e,{allowEmptyOption:true,create:false});});</script>
@endpush
@endisset
