@extends('layouts.app')
@section('title', 'Indemnités')
@section('header', 'Configurations — Indemnités')

@section('content')
@include('configurations._tabs')
<p class="text-sm text-gray-500 mb-4">
    Types d'indemnités paramétrables (décret 2014-427). Les montants/taux saisis ici alimentent le calcul ;
    aucun taux n'est codé en dur.
</p>

<div x-data="{
        vide: { id: null, code: '', libelle: '', mode: 'montant_fixe', valeur: '', reference_texte: '', actif: true },
        form: {},
        init() { this.form = { ...this.vide }; },
        editer(i) { this.form = { ...i }; window.scrollTo({ top: 0, behavior: 'smooth' }); },
        annuler() { this.form = { ...this.vide }; }
     }"
     class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    @can('indemnites.manage')
    <div class="card lg:order-2">
        <h3 class="font-semibold text-gray-700 mb-3" x-text="form.id ? 'Modifier l\'indemnité' : 'Ajouter une indemnité'"></h3>
        <form method="POST" :action="form.id ? '{{ url('indemnites') }}/' + form.id : '{{ route('indemnites.store') }}'" class="space-y-3">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>

            <div><label class="label">Code</label><input name="code" x-model="form.code" class="input" required></div>
            <div><label class="label">Libellé</label><input name="libelle" x-model="form.libelle" class="input" required></div>
            <div>
                <label class="label">Mode de calcul</label>
                <select name="mode" x-model="form.mode" class="input" required>
                    @foreach ($modes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" x-text="form.mode === 'pourcentage' ? 'Taux (%)' : 'Montant (FCFA / mois)'"></label>
                <input type="number" step="0.01" min="0" name="valeur" x-model="form.valeur" class="input" required>
            </div>
            <div><label class="label">Référence (texte)</label><input name="reference_texte" x-model="form.reference_texte" class="input" placeholder="décret 2014-427"></div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="actif" value="1" x-model="form.actif" class="rounded border-gray-300 text-institution-600"> Active
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary" x-text="form.id ? 'Mettre à jour' : 'Ajouter'"></button>
                <button type="button" class="btn btn-secondary" x-show="form.id" @click="annuler()">Annuler</button>
            </div>
            @foreach (['code','libelle','mode','valeur'] as $champ)
                @error($champ)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            @endforeach
        </form>
    </div>
    @endcan

    <div class="lg:col-span-2 card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $indemnites->count() }} indemnité(s)</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Code</th><th class="table-head">Libellé</th>
                <th class="table-head">Mode</th><th class="table-head">Valeur</th>
                <th class="table-head">Attrib.</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($indemnites as $i)
                    <tr class="hover:bg-gray-50 {{ $i->actif ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3 text-sm font-mono">{{ $i->code }}</td>
                        <td class="px-4 py-3 text-sm">{{ $i->libelle }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $i->mode?->label() }}</td>
                        <td class="px-4 py-3 text-sm font-medium">
                            {{ $i->mode?->value === 'pourcentage' ? rtrim(rtrim(number_format($i->valeur, 2), '0'), '.') . ' %' : number_format($i->valeur, 0, ',', ' ') . ' F' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $i->attributions_count }}</td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @can('indemnites.manage')
                                <button @click="editer(@js($i->only(['id','code','libelle','mode','valeur','reference_texte','actif'])))" class="text-institution-600 hover:underline">Modifier</button>
                                <button onclick="if(confirm('Supprimer cette indemnité ?')) document.getElementById('del-ind-{{ $i->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                                <form id="del-ind-{{ $i->id }}" method="POST" action="{{ route('indemnites.destroy', $i) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune indemnité définie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
