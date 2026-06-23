@extends('layouts.app')
@section('title', 'Importer un PDF-PAR')
@section('header', 'Importer le budget / programme d\'activité')

@section('content')
<div class="mb-4"><a href="{{ route('budget.index') }}" class="text-sm text-institution-600 hover:underline">← Budget des structures</a></div>

<div class="card max-w-2xl">
    <p class="text-sm text-gray-600 mb-4">
        Téléversez un fichier <strong>PDF-PAR</strong> (Excel .xlsx) d'une structure. Le système lit les feuilles
        <code class="text-xs bg-gray-100 px-1 rounded">budget associé au programme</code> et
        <code class="text-xs bg-gray-100 px-1 rounded">Programme d'activité annuelle</code>.
        La structure exécutante est déduite du chapitre (libellé). L'import est <strong>idempotent</strong>
        (ré-importer le même fichier met à jour les données).
    </p>

    <form method="POST" action="{{ route('budget.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <x-form.input name="exercice" label="Exercice (par défaut : année en cours)" type="number" :value="now()->year" />
        <div>
            <label for="fichier" class="label">Fichier PDF-PAR (.xlsx)</label>
            <input type="file" name="fichier" id="fichier" accept=".xlsx,.xls" required
                   class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-institution-600 file:px-4 file:py-2 file:text-white">
            @error('fichier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('budget.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Importer</button>
        </div>
    </form>
</div>
@endsection
