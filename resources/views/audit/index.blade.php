@extends('layouts.app')
@section('title', 'Journal d\'audit')
@section('header', 'Journal d\'audit')
@section('content')
<form method="GET" class="card mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="label">Catégorie</label>
        <select name="log" class="input">
            <option value="">Toutes</option>
            @foreach ($logs as $log)
                <option value="{{ $log }}" {{ ($filtres['log'] ?? '') === $log ? 'selected' : '' }}>{{ $log }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Événement</label>
        <select name="event" class="input">
            <option value="">Tous</option>
            @foreach (['created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression'] as $val => $lib)
                <option value="{{ $val }}" {{ ($filtres['event'] ?? '') === $val ? 'selected' : '' }}>{{ $lib }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtrer</button>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Date</th><th class="table-head">Utilisateur</th>
            <th class="table-head">Catégorie</th><th class="table-head">Événement</th><th class="table-head">Description</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($activites as $a)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm">{{ $a->causer?->name ?? 'Système' }}</td>
                    <td class="px-4 py-3 text-sm"><span class="badge bg-gray-100 text-gray-700">{{ $a->log_name }}</span></td>
                    <td class="px-4 py-3 text-sm">
                        @php $couleurs = ['created' => 'bg-green-100 text-green-800', 'updated' => 'bg-blue-100 text-blue-800', 'deleted' => 'bg-red-100 text-red-800']; @endphp
                        <span class="badge {{ $couleurs[$a->event] ?? 'bg-gray-100 text-gray-700' }}">{{ $a->event }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $a->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucune activité enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $activites->links() }}</div>
@endsection
