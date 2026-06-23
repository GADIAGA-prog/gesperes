@extends('layouts.app')
@section('title', 'Importer — ' . $config['titre'])
@section('header', 'Importer : ' . $config['titre'])

@section('content')
<div class="mb-4">
    <a href="{{ route('referentiels.show', $type) }}" class="text-sm text-institution-600 hover:underline">← Retour à {{ $config['titre'] }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Téléversement --}}
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Téléverser un fichier</h3>
            <p class="text-sm text-gray-600 mb-4">
                Fichier Excel (.xlsx, .xls) ou CSV. La <strong>première ligne</strong> doit contenir les en-têtes ci-dessous.
                Les lignes sont rapprochées par <strong>code</strong> (mise à jour si le code existe déjà).
            </p>
            <form method="POST" action="{{ route('referentiels.import', $type) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="fichier" class="label">Fichier</label>
                    <input type="file" name="fichier" id="fichier" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-institution-600 file:px-4 file:py-2 file:text-white">
                    @error('fichier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('referentiels.modele', $type) }}" class="btn btn-secondary">⬇ Modèle vierge</a>
                    <a href="{{ route('referentiels.export', $type) }}" class="btn btn-secondary">⬇ Données actuelles</a>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
        </div>

        {{-- Colonnes attendues --}}
        <div class="card">
            <h3 class="font-semibold text-gray-700 mb-3">Colonnes attendues</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2 border-b">Colonne</th><th class="px-3 py-2 border-b">Obligatoire</th><th class="px-3 py-2 border-b">Description</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr><td class="px-3 py-2 font-mono">code</td><td class="px-3 py-2 text-red-600">Oui</td><td class="px-3 py-2 text-gray-600">Identifiant unique de l'entrée.</td></tr>
                        <tr><td class="px-3 py-2 font-mono">libelle</td><td class="px-3 py-2 text-gray-400">Non</td><td class="px-3 py-2 text-gray-600">Libellé (= code si vide).</td></tr>
                        @foreach ($config['champs'] as $nom => $def)
                            @php $col = \Illuminate\Support\Str::endsWith($nom, '_id') ? \Illuminate\Support\Str::beforeLast($nom, '_id') : $nom; @endphp
                            <tr>
                                <td class="px-3 py-2 font-mono">{{ $col }}</td>
                                <td class="px-3 py-2 text-gray-400">Non</td>
                                <td class="px-3 py-2 text-gray-600">
                                    {{ $def['label'] }} —
                                    @switch($def['type'])
                                        @case('select') code (ou libellé) d'un(e) {{ \Illuminate\Support\Str::lower($def['label']) }} existant(e). @break
                                        @case('enum') une valeur parmi : {{ collect($sources[$nom] ?? [])->keys()->merge(collect($sources[$nom] ?? [])->values())->unique()->implode(', ') }}. @break
                                        @case('boolean') Oui / Non. @break
                                        @case('number') nombre entier. @break
                                        @default texte libre.
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                        <tr><td class="px-3 py-2 font-mono">actif</td><td class="px-3 py-2 text-gray-400">Non</td><td class="px-3 py-2 text-gray-600">Oui / Non (Oui par défaut).</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-gray-500">Codes insensibles à la casse · taille max. 5 Mo · les lignes invalides sont signalées après l'import.</p>
        </div>
    </div>

    {{-- Codes disponibles pour les champs liés --}}
    <div class="space-y-6">
        @forelse ($sources as $nom => $options)
            <div class="card">
                <h3 class="font-semibold text-gray-700 mb-2">{{ $config['champs'][$nom]['label'] ?? $nom }} — valeurs acceptées</h3>
                <p class="text-xs text-gray-400 mb-2">Saisissez le code ou le libellé.</p>
                <div class="max-h-72 overflow-y-auto">
                    @foreach ($options as $libelle)
                        <div class="text-sm py-0.5 border-b border-gray-50 text-gray-600 truncate">{{ $libelle }}</div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card text-sm text-gray-400">Aucun champ lié — colonnes simples uniquement.</div>
        @endforelse
    </div>
</div>
@endsection
