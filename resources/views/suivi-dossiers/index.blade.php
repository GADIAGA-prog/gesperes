@extends('layouts.app')
@section('title','Suivi des dossiers')
@section('header','Suivi des dossiers')
@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
    <p class="text-sm text-gray-500">{{ $dossiers->total() }} dossier(s)</p>
    <div class="flex gap-2">
        @can('suivi.manage')
            <a href="{{ route('suivi-dossiers.natures.index') }}" class="btn btn-secondary">Natures</a>
            <a href="{{ route('suivi-dossiers.create') }}" class="btn btn-primary">+ Nouveau dossier</a>
        @endcan
    </div>
</div>

<form method="GET" class="card mb-4 grid grid-cols-1 sm:grid-cols-6 gap-3">
    <input type="text" name="recherche" value="{{ $filtres['recherche'] ?? '' }}" placeholder="Réf. bordereau" class="input sm:col-span-2">
    <select name="nature_id" class="input">
        <option value="">Toutes natures</option>
        @foreach($natures as $id=>$lib)<option value="{{ $id }}" {{ (string)($filtres['nature_id']??'')===(string)$id?'selected':'' }}>{{ $lib }}</option>@endforeach
    </select>
    <select name="etape" class="input">
        <option value="">Toutes étapes</option>
        @foreach($etapes as $v=>$l)<option value="{{ $v }}" {{ ($filtres['etape']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
    </select>
    <select name="statut" class="input">
        <option value="">Tous statuts</option>
        @foreach($statuts as $v=>$l)<option value="{{ $v }}" {{ ($filtres['statut']??'')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
    </select>
    <label class="inline-flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="en_retard" value="1" {{ !empty($filtres['en_retard'])?'checked':'' }} class="rounded border-gray-300">
        En retard
    </label>
    <div class="sm:col-span-6 flex gap-2">
        <button class="btn btn-primary">Filtrer</button>
        <a href="{{ route('suivi-dossiers.index') }}" class="btn btn-secondary">Réinitialiser</a>
    </div>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Bordereau</th>
            <th class="table-head">Nature</th>
            <th class="table-head">Étape</th>
            <th class="table-head">Localisation (service / agent)</th>
            <th class="table-head">Réception</th>
            <th class="table-head">Échéance</th>
            <th class="table-head">Statut</th>
            <th class="table-head text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($dossiers as $d)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    <a href="{{ route('suivi-dossiers.show',$d) }}" class="font-medium text-institution-700 hover:underline">{{ $d->reference_bordereau }}</a>
                    @if($d->objet)<p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($d->objet, 40) }}</p>@endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $d->nature?->libelle ?? '—' }}</td>
                <td class="px-4 py-3"><span class="badge {{ $d->etape?->color() }}">{{ $d->etape?->label() }}</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    {{ $d->serviceActuel?->libelle ?? $d->structure?->libelle ?? '—' }}
                    @if($d->agentActuel)<p class="text-xs text-gray-400">{{ $d->agentActuel->nom_complet }}</p>@endif
                </td>
                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $d->date_reception?->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm whitespace-nowrap">
                    @if($d->date_limite)
                        {{ $d->date_limite->format('d/m/Y') }}
                        @if($d->en_retard)
                            <span class="badge bg-red-100 text-red-700">En retard</span>
                        @elseif(!$d->statut?->estTermine() && $d->jours_restants !== null)
                            <span class="badge bg-green-100 text-green-700">J-{{ $d->jours_restants }}</span>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3"><span class="badge {{ $d->statut?->color() }}">{{ $d->statut?->label() }}</span></td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                    <a href="{{ route('suivi-dossiers.show',$d) }}" class="text-institution-600 hover:underline">Voir</a>
                    @can('suivi.manage')
                        <a href="{{ route('suivi-dossiers.edit',$d) }}" class="ml-2 text-institution-600 hover:underline">Modifier</a>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Aucun dossier.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $dossiers->links() }}</div>
@endsection
