@php $s = $structure ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="code" label="Code" :value="$s?->code" required />
    <x-form.input name="libelle" label="Libellé" :value="$s?->libelle" required />
    <x-form.select name="type" label="Type" :options="$types" :selected="$s?->type?->value" required />
    <x-form.select name="parent_id" label="Structure parente" :options="$parents" :selected="$s?->parent_id" placeholder="— Aucune (racine) —" />
    <x-form.input name="region" label="Région" :value="$s?->region" />
    <x-form.input name="province" label="Province" :value="$s?->province" />
    <x-form.select name="localite_id" label="Localité" :options="$localites" :selected="$s?->localite_id" />
    <label class="flex items-center gap-2 text-sm text-gray-700 mt-6">
        <input type="checkbox" name="actif" value="1" {{ old('actif', $s?->actif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-institution-600">
        Structure active
    </label>
</div>
