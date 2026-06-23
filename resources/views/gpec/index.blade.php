@extends('layouts.app')
@section('title', 'GPEC')
@section('header', 'Outils GRH — GPEC (prévision des emplois et compétences)')

@section('content')
@include('outils-grh._tabs')
<p class="text-sm text-gray-500 mb-4">Projections sur {{ $annees }} ans à partir des données RH (départs à la retraite, effectifs, compétences).</p>

<div class="card mb-6">
    <h3 class="font-semibold text-gray-700 mb-3">Départs à la retraite par année</h3>
    <div id="chartDeparts"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">Besoins de remplacement par emploi ({{ $annees }} ans)</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="table-head">Emploi</th><th class="table-head">Départs prévus</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($besoins as $emploi => $c)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $emploi }}</td><td class="px-4 py-2 text-sm font-medium text-red-600">{{ $c }}</td></tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">Aucun départ prévu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card overflow-x-auto">
        <h3 class="font-semibold text-gray-700 mb-3">Effectifs par emploi (top 15)</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="table-head">Emploi</th><th class="table-head">Effectif</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse (array_slice($effectifs, 0, 15, true) as $emploi => $c)
                    <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $emploi }}</td><td class="px-4 py-2 text-sm font-medium">{{ $c }}</td></tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">Aucune donnée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card overflow-x-auto">
    <h3 class="font-semibold text-gray-700 mb-3">Cartographie des compétences</h3>
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="table-head">Compétence</th><th class="table-head">Agents</th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($competences as $lib => $c)
                <tr class="hover:bg-gray-50"><td class="px-4 py-2 text-sm">{{ $lib }}</td><td class="px-4 py-2 text-sm font-medium">{{ $c }}</td></tr>
            @empty
                <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">Aucune compétence renseignée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') return;
    new ApexCharts(document.querySelector('#chartDeparts'), {
        chart: { type: 'bar', height: 300 },
        series: [{ name: 'Départs', data: {!! json_encode(array_values($departs)) !!} }],
        xaxis: { categories: {!! json_encode(array_keys($departs)) !!} },
        colors: ['#e07a5f'],
        dataLabels: { enabled: true },
    }).render();
});
</script>
@endpush
