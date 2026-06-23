@php $e = $evaluation ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @isset($agents)
        <x-form.select name="agent_id" label="Agent" :options="$agents->mapWithKeys(fn($a)=>[$a->id => $a->matricule.' — '.$a->nom_complet])" :selected="$agentSel ?? old('agent_id')" required />
    @else
        <div><label class="label">Agent</label><input class="input bg-gray-100" value="{{ $e->agent?->nom_complet }}" disabled><input type="hidden" name="agent_id" value="{{ $e->agent_id }}"></div>
    @endisset
    <x-form.input name="periode" label="Période (année)" type="number" :value="$e?->periode ?? old('periode', now()->year)" required />
    <x-form.input name="date_evaluation" label="Date d'évaluation" type="date" :value="$e?->date_evaluation?->toDateString() ?? old('date_evaluation')" required />
    <x-form.input name="note" label="Note (/20)" type="number" step="0.01" :value="$e?->note ?? old('note')" />
    <x-form.select name="statut" label="Statut" :options="['brouillon'=>'Brouillon','valide'=>'Validée']" :selected="$e?->statut ?? old('statut','brouillon')" required />
    <x-form.textarea name="objectifs" label="Objectifs" :value="$e?->objectifs ?? old('objectifs')" class="sm:col-span-2" />
    <x-form.textarea name="appreciation" label="Appréciation" :value="$e?->appreciation ?? old('appreciation')" class="sm:col-span-2" />
</div>
