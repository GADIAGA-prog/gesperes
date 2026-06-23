@php $p = $plan ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="intitule" label="Intitulé du plan" :value="$p?->intitule ?? old('intitule')" class="sm:col-span-2" placeholder="Plan triennal de formation 2026-2028" required />
    <x-form.input name="annee_debut" label="Année de début" type="number" min="2000" max="2100" :value="$p?->annee_debut ?? old('annee_debut', now()->year)" required />
    <x-form.input name="annee_fin" label="Année de fin" type="number" min="2000" max="2100" :value="$p?->annee_fin ?? old('annee_fin', now()->year + 2)" required />
    <x-form.select name="statut" label="Statut" :options="$statuts" :selected="$p?->statut?->value ?? old('statut','brouillon')" required />
    <div class="hidden sm:block"></div>
    <x-form.textarea name="vision" label="Vision" :value="$p?->vision ?? old('vision')" class="sm:col-span-2" rows="2" />
    <x-form.textarea name="finalite" label="Finalité" :value="$p?->finalite ?? old('finalite')" class="sm:col-span-2" rows="2" />
    <x-form.textarea name="objectifs" label="Objectifs" :value="$p?->objectifs ?? old('objectifs')" class="sm:col-span-2" rows="3" />
</div>
@unless($p)
<p class="mt-3 text-xs text-gray-500">Un programme annuel sera créé automatiquement pour chaque année de la période.</p>
@endunless
