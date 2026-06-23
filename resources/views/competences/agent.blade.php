@extends('layouts.app')
@section('title', 'Compétences · ' . $agent->nom_complet)
@section('header', 'Compétences : ' . $agent->nom_complet)

@section('content')
<div class="mb-4"><a href="{{ route('agents.show', $agent) }}" class="text-sm text-institution-600 hover:underline">← Retour à la fiche agent</a></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">Compétences de l'agent</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500">
                <th class="table-head">Compétence</th><th class="table-head">Niveau</th>
                <th class="table-head">Acquise le</th><th class="table-head">Source</th><th class="table-head text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($agent->competences as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $c->libelle }} @if($c->domaine)<span class="text-xs text-gray-400">({{ $c->domaine }})</span>@endif</td>
                        <td class="px-4 py-3 text-sm">{{ $niveaux[$c->pivot->niveau] ?? $c->pivot->niveau }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $c->pivot->date_acquisition ? \Illuminate\Support\Carbon::parse($c->pivot->date_acquisition)->format('d/m/Y') : '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $c->pivot->source ? ucfirst($c->pivot->source) : '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            @can('competences.manage')
                                <button onclick="if(confirm('Retirer ?')) document.getElementById('rc-{{ $c->id }}').submit()" class="text-red-500 hover:underline">Retirer</button>
                                <form id="rc-{{ $c->id }}" method="POST" action="{{ route('agents.competences.retirer', [$agent, $c]) }}" class="hidden">@csrf @method('DELETE')</form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucune compétence.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('competences.manage')
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Attribuer une compétence</h3>
        @if ($disponibles->isEmpty())
            <p class="text-sm text-gray-400">Aucune compétence disponible. Ajoutez-en au <a href="{{ route('competences.index') }}" class="text-institution-600 hover:underline">référentiel</a>.</p>
        @else
        <form method="POST" action="{{ route('agents.competences.attribuer', $agent) }}" class="space-y-3">
            @csrf
            <x-form.select name="competence_id" label="Compétence" :options="$disponibles->pluck('libelle','id')" required />
            <x-form.select name="niveau" label="Niveau" :options="$niveaux" required />
            <x-form.input name="date_acquisition" label="Date d'acquisition" type="date" />
            <x-form.select name="source" label="Source" :options="['formation'=>'Formation','experience'=>'Expérience']" placeholder="—" />
            <button class="btn btn-primary w-full justify-center">Attribuer</button>
        </form>
        @endif
    </div>
    @endcan
</div>
@endsection
