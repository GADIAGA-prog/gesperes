@extends('layouts.app')
@section('title','Natures de dossier')
@section('header','Suivi des dossiers — Natures')
@section('content')
<div class="mb-4">
    <a href="{{ route('suivi-dossiers.index') }}" class="btn btn-secondary">&larr; Retour aux dossiers</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Ajout --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-3">Nouvelle nature</h3>
        <form method="POST" action="{{ route('suivi-dossiers.natures.store') }}" class="space-y-3">@csrf
            <x-form.input name="code" label="Code" :value="old('code')" placeholder="Optionnel" />
            <x-form.input name="libelle" label="Libellé" :value="old('libelle')" required />
            <x-form.input name="delai_defaut_jours" label="Délai par défaut (jours)" type="number" min="0" :value="old('delai_defaut_jours')" />
            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="actif" value="1" checked class="rounded border-gray-300"> Actif
            </label>
            <button class="btn btn-primary w-full">Ajouter</button>
        </form>
    </div>

    {{-- Liste --}}
    <div class="card lg:col-span-2 overflow-x-auto">
        <h3 class="font-semibold text-gray-800 mb-3">Natures existantes</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Code</th><th class="table-head">Libellé</th>
                <th class="table-head">Délai</th><th class="table-head">Actif</th>
                <th class="table-head">Dossiers</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
            @forelse($natures as $n)
                @php $fid = 'nat-'.$n->id; @endphp
                <tr>
                    <td class="px-3 py-2">
                        <form id="{{ $fid }}" method="POST" action="{{ route('suivi-dossiers.natures.update',$n) }}">@csrf @method('PUT')</form>
                        <input form="{{ $fid }}" name="code" value="{{ $n->code }}" class="input w-24">
                    </td>
                    <td class="px-3 py-2"><input form="{{ $fid }}" name="libelle" value="{{ $n->libelle }}" class="input" required></td>
                    <td class="px-3 py-2"><input form="{{ $fid }}" name="delai_defaut_jours" type="number" min="0" value="{{ $n->delai_defaut_jours }}" class="input w-20"></td>
                    <td class="px-3 py-2"><input form="{{ $fid }}" type="checkbox" name="actif" value="1" {{ $n->actif?'checked':'' }} class="rounded border-gray-300"></td>
                    <td class="px-3 py-2 text-sm text-gray-500">{{ $n->dossiers_count }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        <button form="{{ $fid }}" class="text-institution-600 hover:underline text-sm">Enregistrer</button>
                        <button type="button" onclick="if(confirm('Supprimer cette nature ?'))document.getElementById('dn-{{ $n->id }}').submit()" class="ml-2 text-red-500 hover:underline text-sm">Suppr.</button>
                        <form id="dn-{{ $n->id }}" method="POST" action="{{ route('suivi-dossiers.natures.destroy',$n) }}" class="hidden">@csrf @method('DELETE')</form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune nature définie.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
