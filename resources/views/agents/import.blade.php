@extends('layouts.app')
@section('title', 'Importer des agents')
@section('header', 'Importer des agents')

@section('content')
<div class="card max-w-2xl">
    <p class="text-sm text-gray-600 mb-4">
        Téléversez un fichier Excel (.xlsx, .xls) ou CSV. La première ligne doit contenir les en-têtes :
        <code class="text-xs bg-gray-100 px-1 rounded">matricule, cle, nom, prenoms, sexe, date_naissance, emploi, categorie, region, province, commune, etablissement, nombre_enfants, situation_matrimoniale</code>
    </p>
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
