@extends('layouts.app')
@section('title', 'Dossier · ' . $agent->nom_complet)
@section('header', 'Dossier individuel : ' . $agent->nom_complet)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <a href="{{ route('agents.show', $agent) }}" class="text-sm text-institution-600 hover:underline">← Retour à la fiche agent</a>
    <div class="flex gap-2">
        @can('documents.download')
            @if ($documents->isNotEmpty())
                <a href="{{ route('agents.documents.export', $agent) }}" class="btn btn-secondary">Exporter le dossier (ZIP)</a>
            @endif
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $documents->count() }} document(s) classé(s)</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Type</th><th class="table-head">Référence</th>
                <th class="table-head">Date</th><th class="table-head">Expiration</th>
                <th class="table-head">État</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($documents as $doc)
                    <tr class="hover:bg-gray-50 {{ $doc->archive ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 text-sm">
                            {{ $doc->type_document?->label() }}
                            @if ($doc->evenementCarriere)
                                <span class="block text-[11px] text-gray-400">↳ {{ $doc->evenementCarriere->type?->label() }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->reference ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->date_document?->format('d/m/Y') ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($doc->date_expiration)
                                <span class="{{ $doc->est_expire ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $doc->date_expiration->format('d/m/Y') }}</span>
                            @else — @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if ($doc->archive)
                                <span class="badge bg-gray-200 text-gray-600">Archivé</span>
                            @else
                                <span class="badge bg-green-100 text-green-700">Actif</span>
                            @endif
                            <span class="block text-[11px] text-gray-400">{{ $doc->taille_lisible }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            @can('documents.download')
                                <a href="{{ route('documents.download', $doc) }}" class="text-institution-600 hover:underline">Télécharger</a>
                            @endcan
                            @can('documents.upload')
                                <button onclick="document.getElementById('arch-doc-{{ $doc->id }}').submit()" class="ml-2 text-gray-500 hover:underline">{{ $doc->archive ? 'Désarchiver' : 'Archiver' }}</button>
                                <form id="arch-doc-{{ $doc->id }}" method="POST" action="{{ route('documents.archiver', $doc) }}" class="hidden">@csrf</form>
                            @endcan
                            @can('documents.delete')
                                <button onclick="if(confirm('Supprimer ce document ?')) document.getElementById('del-doc-{{ $doc->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                                <form id="del-doc-{{ $doc->id }}" method="POST" action="{{ route('documents.destroy', $doc) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun document.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('documents.upload')
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Numériser / téléverser</h3>
        <form method="POST" action="{{ route('agents.documents.store', $agent) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <x-form.select name="type_document" label="Type" :options="collect($types)->mapWithKeys(fn($t)=>[$t->value=>$t->label()])" required />
            <x-form.input name="reference" label="Référence" />
            <div class="grid grid-cols-2 gap-2">
                <x-form.input name="date_document" label="Date du document" type="date" />
                <x-form.input name="date_expiration" label="Expiration" type="date" />
            </div>
            @if ($evenements->isNotEmpty())
                <x-form.select name="carriere_evenement_id" label="Rattacher à un acte de carrière"
                    :options="$evenements->mapWithKeys(fn($e)=>[$e->id => $e->date_effet?->format('d/m/Y').' — '.$e->type?->label()])"
                    placeholder="— Aucun —" />
            @endif
            <div>
                <label for="fichiers" class="label">Fichiers (PDF/JPG/PNG — plusieurs possibles)</label>
                <input type="file" name="fichiers[]" id="fichiers" accept=".pdf,.jpg,.jpeg,.png" multiple required
                       class="block w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-institution-600 file:px-3 file:py-1.5 file:text-white">
                @error('fichiers')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('fichiers.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-form.textarea name="commentaire" label="Commentaire" rows="2" />
            <button type="submit" class="btn btn-primary w-full justify-center">Enregistrer au dossier</button>
        </form>
    </div>
    @endcan
</div>
@endsection
