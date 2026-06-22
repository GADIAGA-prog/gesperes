@extends('layouts.app')
@section('title', 'Documents · ' . $agent->nom_complet)
@section('header', 'Documents : ' . $agent->nom_complet)

@section('content')
<div class="mb-4"><a href="{{ route('agents.show', $agent) }}" class="text-sm text-institution-600 hover:underline">← Retour à la fiche agent</a></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">Documents enregistrés</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Type</th><th class="table-head">Référence</th>
                <th class="table-head">Date</th><th class="table-head">Expiration</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($documents as $doc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $doc->type_document?->label() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->reference ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->date_document?->format('d/m/Y') ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($doc->date_expiration)
                                <span class="{{ $doc->est_expire ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $doc->date_expiration->format('d/m/Y') }}</span>
                            @else — @endif
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('documents.download')
                                <a href="{{ route('documents.download', $doc) }}" class="text-institution-600 hover:underline">Télécharger</a>
                            @endcan
                            @can('documents.delete')
                                <button onclick="if(confirm('Supprimer ce document ?')) document.getElementById('del-doc-{{ $doc->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                                <form id="del-doc-{{ $doc->id }}" method="POST" action="{{ route('documents.destroy', $doc) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun document.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('documents.upload')
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Téléverser</h3>
        <form method="POST" action="{{ route('agents.documents.store', $agent) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <x-form.select name="type_document" label="Type" :options="collect($types)->mapWithKeys(fn($t)=>[$t->value=>$t->label()])" required />
            <x-form.input name="reference" label="Référence" />
            <x-form.input name="date_document" label="Date du document" type="date" />
            <x-form.input name="date_expiration" label="Date d'expiration" type="date" />
            <div>
                <label for="fichier" class="label">Fichier (PDF/JPG/PNG)</label>
                <input type="file" name="fichier" id="fichier" accept=".pdf,.jpg,.jpeg,.png" required
                       class="block w-full text-sm file:mr-3 file:rounded file:border-0 file:bg-institution-600 file:px-3 file:py-1.5 file:text-white">
                @error('fichier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-form.textarea name="commentaire" label="Commentaire" rows="2" />
            <button type="submit" class="btn btn-primary w-full justify-center">Téléverser</button>
        </form>
    </div>
    @endcan
</div>
@endsection
