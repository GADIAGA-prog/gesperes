@extends('layouts.app')
@section('title', 'Importer des indices')
@section('header', 'Importer des indices')

@section('content')
<div class="mb-4">
    <a href="{{ route('referentiels.show', 'indices') }}" class="text-sm text-institution-600 hover:underline">← Retour aux indices</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Formulaire d'import --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Téléverser un fichier</h3>
            <p class="text-sm text-gray-600 mb-4">
                Fichier Excel (.xlsx, .xls) ou CSV. La <strong>première ligne</strong> doit contenir les en-têtes
                exactement comme indiqué ci-dessous.
            </p>

            <form method="POST" action="{{ route('referentiels.indices.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="fichier" class="label">Fichier</label>
                    <input type="file" name="fichier" id="fichier" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-institution-600 file:px-4 file:py-2 file:text-white">
                    @error('fichier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('referentiels.indices.template') }}" class="btn btn-secondary">⬇ Télécharger le modèle</a>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
        </div>

        {{-- Documentation du format --}}
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Format des données à importer</h3>
            <p class="text-sm text-gray-600 mb-3">
                L'indice est déterminé par le triplet <strong>catégorie × classe × échelon</strong>.
                Chaque ligne crée (ou met à jour) la valeur d'indice de cette combinaison.
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-3 py-2 border-b">Colonne</th>
                            <th class="px-3 py-2 border-b">Obligatoire</th>
                            <th class="px-3 py-2 border-b">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-3 py-2 font-mono">categorie</td>
                            <td class="px-3 py-2 text-red-600">Oui</td>
                            <td class="px-3 py-2 text-gray-600">Code de la catégorie (ou son libellé). Ex. : <code class="bg-gray-100 px-1 rounded">A</code></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono">classe</td>
                            <td class="px-3 py-2 text-red-600">Oui</td>
                            <td class="px-3 py-2 text-gray-600">Code de la classe (ou son libellé).</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono">echelon</td>
                            <td class="px-3 py-2 text-red-600">Oui</td>
                            <td class="px-3 py-2 text-gray-600">Code de l'échelon (ou son libellé).</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono">valeur</td>
                            <td class="px-3 py-2 text-red-600">Oui</td>
                            <td class="px-3 py-2 text-gray-600">Valeur numérique de l'indice (entier). Ex. : <code class="bg-gray-100 px-1 rounded">350</code></td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono">code</td>
                            <td class="px-3 py-2 text-gray-400">Non</td>
                            <td class="px-3 py-2 text-gray-600">Code de l'indice. Généré automatiquement (<code class="bg-gray-100 px-1 rounded">CATEGORIE-CLASSE-ECHELON</code>) si laissé vide.</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono">libelle</td>
                            <td class="px-3 py-2 text-gray-400">Non</td>
                            <td class="px-3 py-2 text-gray-600">Libellé descriptif. Généré automatiquement si laissé vide.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ul class="mt-4 text-xs text-gray-500 space-y-1 list-disc list-inside">
                <li>Les codes sont reconnus sans tenir compte de la casse.</li>
                <li>Si la combinaison catégorie × classe × échelon existe déjà, sa valeur est <strong>mise à jour</strong>.</li>
                <li>Les lignes dont un référentiel est introuvable sont ignorées et signalées après l'import.</li>
                <li>Taille maximale du fichier : 5 Mo.</li>
            </ul>
        </div>
    </div>

    {{-- Codes disponibles pour aider la saisie --}}
    <div class="space-y-6">
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-2">Catégories disponibles</h3>
            @forelse ($categories as $c)
                <div class="flex justify-between text-sm py-0.5 border-b border-gray-50">
                    <span class="font-mono">{{ $c->code }}</span><span class="text-gray-500 truncate ml-2">{{ $c->libelle }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Aucune catégorie. Créez-les d'abord.</p>
            @endforelse
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-2">Classes disponibles</h3>
            @forelse ($classes as $c)
                <div class="flex justify-between text-sm py-0.5 border-b border-gray-50">
                    <span class="font-mono">{{ $c->code }}</span><span class="text-gray-500 truncate ml-2">{{ $c->libelle }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Aucune classe. Créez-les d'abord.</p>
            @endforelse
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-2">Échelons disponibles</h3>
            @forelse ($echelons as $e)
                <div class="flex justify-between text-sm py-0.5 border-b border-gray-50">
                    <span class="font-mono">{{ $e->code }}</span><span class="text-gray-500 truncate ml-2">{{ $e->libelle }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Aucun échelon. Créez-les d'abord.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
