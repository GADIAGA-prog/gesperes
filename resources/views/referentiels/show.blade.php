@extends('layouts.app')
@section('title', $config['titre'])
@section('header', $config['titre'])
@section('content')
<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('referentiels.index') }}" class="text-sm text-institution-600 hover:underline">← Tous les référentiels</a>
    @if (($config['importable'] ?? false) && $type === 'indices')
        @can('settings.manage')
        <div class="flex gap-2">
            <a href="{{ route('referentiels.indices.template') }}" class="btn btn-secondary">⬇ Modèle Excel</a>
            <a href="{{ route('referentiels.indices.import.form') }}" class="btn btn-primary">Importer des indices</a>
        </div>
        @endcan
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Code</th><th class="table-head">Libellé</th>
                @foreach ($config['champs'] as $nom => $def)<th class="table-head">{{ $def['label'] }}</th>@endforeach
                <th class="table-head">Actif</th>
                @can('settings.manage')<th class="table-head text-right"></th>@endcan
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-sm font-mono">{{ $item->code }}</td>
                        <td class="px-4 py-2.5 text-sm">{{ $item->libelle }}</td>
                        @foreach ($config['champs'] as $nom => $def)
                            <td class="px-4 py-2.5 text-sm text-gray-600">
                                @if ($def['type'] === 'boolean'){{ $item->$nom ? 'Oui' : 'Non' }}
                                @elseif ($def['type'] === 'select'){{ optional($sources[$nom] ?? [])[$item->$nom] ?? '—' }}
                                @elseif ($def['type'] === 'enum'){{ ($sources[$nom] ?? collect())[is_object($item->$nom) ? $item->$nom->value : $item->$nom] ?? $item->$nom }}
                                @else {{ $item->$nom ?: '—' }}@endif
                            </td>
                        @endforeach
                        <td class="px-4 py-2.5"><span class="badge {{ $item->actif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->actif ? 'Oui' : 'Non' }}</span></td>
                        @can('settings.manage')
                        <td class="px-4 py-2.5 text-right text-sm whitespace-nowrap">
                            <a href="{{ route('referentiels.show', [$type, 'edit' => $item->id]) }}" class="text-institution-600 hover:underline">Éditer</a>
                            <button onclick="if(confirm('Supprimer ?')) document.getElementById('del-{{ $item->id }}').submit()" class="ml-2 text-red-500 hover:underline">Suppr.</button>
                            <form id="del-{{ $item->id }}" method="POST" action="{{ route('referentiels.destroy', [$type, $item->id]) }}" class="hidden">@csrf @method('DELETE')</form>
                        </td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">Aucune entrée.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>

    @can('settings.manage')
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $edition ? 'Modifier' : 'Ajouter' }}</h3>
        <form method="POST" action="{{ $edition ? route('referentiels.update', [$type, $edition->id]) : route('referentiels.store', $type) }}" class="space-y-3">
            @csrf
            @if ($edition) @method('PUT') @endif
            <x-form.input name="code" label="Code" :value="$edition?->code" required />
            <x-form.input name="libelle" label="Libellé" :value="$edition?->libelle" required />
            @foreach ($config['champs'] as $nom => $def)
                @if ($def['type'] === 'boolean')
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="{{ $nom }}" value="1" {{ old($nom, $edition?->$nom) ? 'checked' : '' }} class="rounded border-gray-300 text-institution-600">
                        {{ $def['label'] }}
                    </label>
                @elseif (in_array($def['type'], ['select', 'enum']))
                    <x-form.select :name="$nom" :label="$def['label']" :options="$sources[$nom] ?? []" :selected="is_object($edition?->$nom) ? $edition?->$nom->value : $edition?->$nom" />
                @else
                    <x-form.input :name="$nom" :label="$def['label']" :type="$def['type'] === 'number' ? 'number' : 'text'" :value="$edition?->$nom" />
                @endif
            @endforeach
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="actif" value="1" {{ old('actif', $edition?->actif ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-institution-600">
                Actif
            </label>
            <div class="flex gap-2 pt-2">
                @if ($edition)<a href="{{ route('referentiels.show', $type) }}" class="btn btn-secondary flex-1 justify-center">Annuler</a>@endif
                <button type="submit" class="btn btn-primary flex-1 justify-center">{{ $edition ? 'Mettre à jour' : 'Ajouter' }}</button>
            </div>
        </form>
    </div>
    @endcan
</div>
@endsection
