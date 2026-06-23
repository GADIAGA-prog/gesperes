@extends('layouts.app')
@section('title', 'Recherche documentaire')
@section('header', 'Recherche documentaire')

@section('content')
<p class="text-sm text-gray-500 mb-4">{{ $documents->total() }} document(s)</p>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-5 gap-3">
    <input type="text" name="q" value="{{ $filtres['q'] ?? '' }}" placeholder="Référence, nom de fichier, commentaire…" class="input sm:col-span-2">
    <select name="agent_id" class="input">
        <option value="">Tous les agents</option>
        @foreach ($agents as $a)
            <option value="{{ $a->id }}" {{ (string)($filtres['agent_id'] ?? '') === (string)$a->id ? 'selected' : '' }}>{{ $a->matricule }} — {{ $a->nom_complet }}</option>
        @endforeach
    </select>
    <select name="type_document" class="input">
        <option value="">Tous les types</option>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" {{ ($filtres['type_document'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <select name="etat" class="input">
            <option value="">Tous</option>
            <option value="actif" {{ ($filtres['etat'] ?? '') === 'actif' ? 'selected' : '' }}>Actifs</option>
            <option value="archive" {{ ($filtres['etat'] ?? '') === 'archive' ? 'selected' : '' }}>Archivés</option>
            <option value="expire" {{ ($filtres['etat'] ?? '') === 'expire' ? 'selected' : '' }}>Expirés</option>
        </select>
        <button type="submit" class="btn btn-primary">Filtrer</button>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                <th class="table-head">Agent</th>
                <th class="table-head">Type</th>
                <th class="table-head">Référence</th>
                <th class="table-head">Date</th>
                <th class="table-head">Expiration</th>
                <th class="table-head">État</th>
                <th class="table-head text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($documents as $doc)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('agents.documents.index', $doc->agent_id) }}" class="text-institution-700 hover:underline">{{ $doc->agent?->nom_complet ?? '—' }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $doc->type_document?->label() }}</td>
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
                        @elseif ($doc->est_expire)
                            <span class="badge bg-red-100 text-red-700">Expiré</span>
                        @else
                            <span class="badge bg-green-100 text-green-700">Actif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        @can('documents.download')
                            <a href="{{ route('documents.download', $doc) }}" class="text-institution-600 hover:underline">Télécharger</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun document trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $documents->links() }}</div>
@endsection
