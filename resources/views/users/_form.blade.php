@php $u = $user ?? null; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form.input name="name" label="Nom complet" :value="$u?->name" required />
    <x-form.input name="email" label="Adresse e-mail" type="email" :value="$u?->email" required />
    <x-form.input name="password" label="{{ $u ? 'Nouveau mot de passe (laisser vide)' : 'Mot de passe' }}" type="password" :required="! $u" />
    <x-form.input name="password_confirmation" label="Confirmation du mot de passe" type="password" :required="! $u" />
    <x-form.input name="region" label="Région (périmètre)" :value="$u?->region" />
    <x-form.select name="structure_id" label="Structure (périmètre)" :options="$structures" :selected="$u?->structure_id" />
</div>
<div class="mt-4">
    <p class="label">Rôles</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach ($roles as $role)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                       {{ collect(old('roles', $u?->roles->pluck('name')->all() ?? []))->contains($role->name) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-institution-600">
                {{ $role->name }}
            </label>
        @endforeach
    </div>
</div>
<label class="flex items-center gap-2 text-sm text-gray-700 mt-4">
    <input type="checkbox" name="actif" value="1" {{ old('actif', $u?->actif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-institution-600">
    Compte actif
</label>
