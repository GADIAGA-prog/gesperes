@extends('layouts.app')
@section('title', 'Référentiels MPP GRH')
@section('header', 'Outils GRH — Référentiels MPP GRH')

@section('content')
@include('outils-grh._tabs')

<p class="text-sm text-gray-500 mb-4">Manuel des Processus et Procédures de la GRH. Sélectionnez un processus pour faire ressortir ses procédures et opérations.</p>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    {{-- Liste des processus --}}
    <aside class="lg:col-span-1">
        <div class="card p-0 overflow-hidden">
            <ul class="divide-y divide-gray-100">
                @foreach ($processus as $p)
                    @php $actif = $selection && $selection->id === $p->id; @endphp
                    <li>
                        <a href="{{ route('outils-grh.referentiels-mpp', ['processus' => $p->id]) }}"
                           class="flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $actif ? 'bg-institution-50 border-l-4 border-institution-600' : '' }}">
                            <span class="{{ $actif ? 'font-semibold text-institution-700' : 'text-gray-700' }}">
                                <span class="text-gray-400">P{{ $p->numero }}</span> · {{ $p->libelle }}
                            </span>
                            <span class="badge bg-gray-100 text-gray-600">{{ $p->procedures_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    {{-- Procédures & opérations du processus sélectionné --}}
    <section class="lg:col-span-3 space-y-6">
        @if (! $selection)
            <div class="card text-center text-gray-400 py-10">Aucun processus. Lancez <code>php artisan mpp:importer</code>.</div>
        @else
            <h2 class="text-lg font-semibold text-gray-800">Processus {{ $selection->numero }} : {{ $selection->libelle }}</h2>

            @forelse ($selection->procedures as $proc)
                <div class="card">
                    <h3 class="font-semibold text-institution-700 mb-3">{{ $proc->libelle }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs border border-gray-200">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500 uppercase tracking-wide">
                                    <th class="border border-gray-200 px-2 py-1">Opération</th>
                                    <th class="border border-gray-200 px-2 py-1">Structure resp.</th>
                                    <th class="border border-gray-200 px-2 py-1">Fait générateur</th>
                                    <th class="border border-gray-200 px-2 py-1">Tâches</th>
                                    <th class="border border-gray-200 px-2 py-1">Intervenants</th>
                                    <th class="border border-gray-200 px-2 py-1">Résultats</th>
                                    <th class="border border-gray-200 px-2 py-1">Délais</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($proc->operations as $op)
                                    <tr class="align-top">
                                        <td class="border border-gray-200 px-2 py-1 font-medium text-gray-700">{{ $op->libelle }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-pre-line">{{ $op->structure_responsable }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-pre-line">{{ $op->fait_generateur }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-pre-line">{{ $op->taches }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-pre-line">{{ $op->intervenants }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-pre-line">{{ $op->resultats }}</td>
                                        <td class="border border-gray-200 px-2 py-1 whitespace-nowrap">{{ $op->delais }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="card text-center text-gray-400 py-6">Aucune procédure pour ce processus.</div>
            @endforelse
        @endif
    </section>
</div>
@endsection
