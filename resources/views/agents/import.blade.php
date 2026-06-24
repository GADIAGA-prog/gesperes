@extends('layouts.app')
@section('title', 'Importer des agents')
@section('header', 'Importer des agents')

@section('content')
<div class="card max-w-2xl">
    <div class="rounded-lg border border-institution-100 bg-institution-50/50 p-4 mb-4">
        <div class="flex items-center justify-between gap-3 mb-2">
            <h3 class="text-sm font-semibold text-gray-700">Modèle à respecter</h3>
            <a href="{{ route('agents.import.modele') }}" class="btn btn-secondary text-sm">⬇ Télécharger le modèle (.xlsx)</a>
        </div>
        <p class="text-sm text-gray-600">
            Utilisez le modèle ci-dessus : la première ligne contient les en-têtes attendus (à ne pas modifier) :
        </p>
        <code class="block mt-2 text-xs bg-white border border-gray-200 px-2 py-1 rounded">{{ implode(', ', $colonnesModele) }}</code>
        <ul class="mt-3 text-xs text-gray-500 list-disc list-inside space-y-1">
            <li>Formats acceptés : <strong>.xlsx, .xls, .csv</strong> (max 5 Mo).</li>
            <li>Le <strong>matricule</strong> est obligatoire et sert de clé : un matricule <strong>déjà existant</strong> (ou en double dans le fichier) est <strong>ignoré</strong>, jamais dupliqué.</li>
            <li><strong>emploi</strong> et <strong>categorie</strong> sont reconnus par leur libellé / code exact (sinon laissés vides).</li>
            <li><strong>sexe</strong> : M ou F ; <strong>date_naissance</strong> : JJ/MM/AAAA.</li>
        </ul>
    </div>
    <form method="POST" action="{{ route('agents.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label for="fichier" class="label">Fichier</label>
            <input type="file" name="fichier" id="fichier" accept=".xlsx,.xls,.csv" required
                   class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-institution-600 file:px-4 file:py-2 file:text-white">
            @error('fichier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('agents.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Importer</button>
        </div>
    </form>
</div>
@endsection
