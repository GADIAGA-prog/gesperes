@extends('layouts.app')
@section('title', 'Enveloppe budgétaire')
@section('header', 'Budget — Répartition de l\'enveloppe (n+1 à n+3)')

@section('content')
@include('budget._tabs')

@include('partials.flash')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="table-head">Période</th>
                        <th class="table-head">Intitulé</th>
                        <th class="table-head">Lignes</th>
                        <th class="table-head text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($enveloppes as $e)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold">{{ $e->annee_debut }}–{{ $e->annee_debut + 2 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $e->intitule }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $e->lignes_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('budget.enveloppe.show', $e) }}" class="text-sm text-institution-600 hover:underline">Ouvrir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Aucune enveloppe. Créez-en une.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @can('budget.manage')
        <div>
            <form method="POST" action="{{ route('budget.enveloppe.store') }}" class="card space-y-4">
                @csrf
                <p class="font-semibold text-gray-800">Nouvelle enveloppe</p>
                <div>
                    <label class="label">Année de début (n+1)</label>
                    <input type="number" name="annee_debut" value="{{ old('annee_debut', $anneeDefaut) }}" min="2000" max="2100" class="input" required>
                    <p class="text-xs text-gray-500 mt-1">Couvrira {{ old('annee_debut', $anneeDefaut) }} à {{ old('annee_debut', $anneeDefaut) + 2 }}.</p>
                </div>
                <div>
                    <label class="label">Intitulé</label>
                    <input type="text" name="intitule" value="{{ old('intitule') }}" placeholder="Enveloppe de référence du DPBEP…" class="input">
                </div>
                <button type="submit" class="btn btn-primary w-full">Créer</button>
                @error('annee_debut')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </form>
        </div>
    @endcan
</div>
@endsection
