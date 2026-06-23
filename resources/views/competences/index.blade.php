@extends('layouts.app')
@section('title', 'Compétences')
@section('header', 'Évaluation — Référentiel des compétences')

@section('content')
@include('evaluation._tabs')
<div x-data="{
        vide: { id: null, code: '', libelle: '', domaine: '', actif: true },
        form: {},
        init() { this.form = { ...this.vide }; },
        editer(c) { this.form = { ...c }; window.scrollTo({ top: 0, behavior: 'smooth' }); },
        annuler() { this.form = { ...this.vide }; }
     }"
     class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    @can('competences.manage')
    <div class="card lg:order-2">
        <h3 class="font-semibold text-gray-700 mb-3" x-text="form.id ? 'Modifier la compétence' : 'Ajouter une compétence'"></h3>
        <form method="POST" :action="form.id ? '{{ url('competences') }}/' + form.id : '{{ route('competences.store') }}'" class="space-y-3">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
            <div><label class="label">Code</label><input name="code" x-model="form.code" class="input" required></div>
            <div><label class="label">Libellé</label><input name="libelle" x-model="form.libelle" class="input" required></div>
            <div><label class="label">Domaine</label><input name="domaine" x-model="form.domaine" class="input" placeholder="Pédagogie, Gestion, Numérique…"></div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="actif" value="1" x-model="form.actif" class="rounded border-gray-300 text-institution-600"> Active
            </label>
            <div class="flex gap-2">
                <button class="btn btn-primary" x-text="form.id ? 'Mettre à jour' : 'Ajouter'"></button>
                <button type="button" class="btn btn-secondary" x-show="form.id" @click="annuler()">Annuler</button>
            </div>
        </form>
    </div>
    @endcan

    <div class="lg:col-span-2 card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $competences->count() }} compétence(s)</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Code</th><th class="table-head">Libellé</th>
                <th class="table-head">Domaine</th><th class="table-head">Agents</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($competences as $c)
                    <tr class="hover:bg-gray-50 {{ $c->actif ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3 text-sm font-mono">{{ $c->code }}</td>
                        <td class="px-4 py-3 text-sm">{{ $c->libelle }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $c->domaine ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $c->agents_count }}</td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @can('competences.manage')
                                <button @click="editer(@js($c->only(['id','code','libelle','domaine','actif'])))" class="text-institution-600 hover:underline">Modifier</button>
                                <button onclick="if(confirm('Supprimer ?')) document.getElementById('cp-{{ $c->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                                <form id="cp-{{ $c->id }}" method="POST" action="{{ route('competences.destroy', $c) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucune compétence.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
