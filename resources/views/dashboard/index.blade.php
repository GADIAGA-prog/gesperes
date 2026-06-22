@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('header', 'Tableau de bord')

@section('content')
@php
    $cards = [
        ['label' => 'Effectif total', 'value' => $cartes['effectif_total'], 'color' => 'institution', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87'],
        ['label' => 'Agents actifs', 'value' => $cartes['actifs'], 'color' => 'green', 'icon' => 'M5 13l4 4L19 7'],
        ['label' => 'Proches retraite', 'value' => $cartes['proches_retraite'], 'color' => 'amber', 'icon' => 'M12 8v4l3 3'],
        ['label' => 'Dossiers incomplets', 'value' => $cartes['dossiers_incomplets'], 'color' => 'red', 'icon' => 'M12 9v2m0 4h.01'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ($cards as $c)
        <div class="card flex items-center gap-4">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-{{ $c['color'] }}-100 text-{{ $c['color'] }}-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($c['value'], 0, ',', ' ') }}</p>
                <p class="text-sm text-gray-500">{{ $c['label'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card text-center">
        <p class="text-sm text-gray-500">Hommes</p>
        <p class="text-xl font-bold text-institution-700">{{ number_format($cartes['hommes'], 0, ',', ' ') }}</p>
    </div>
    <div class="card text-center">
        <p class="text-sm text-gray-500">Femmes</p>
        <p class="text-xl font-bold text-pink-600">{{ number_format($cartes['femmes'], 0, ',', ' ') }}</p>
    </div>
    <div class="card text-center">
        <p class="text-sm text-gray-500">Documents expirés</p>
        <p class="text-xl font-bold text-red-600">{{ number_format($cartes['documents_expires'], 0, ',', ' ') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Répartition par sexe</h3>
        <div id="chartSexe"></div>
    </div>
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Pyramide des âges</h3>
        <div id="chartAge"></div>
    </div>
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Effectif par région</h3>
        <div id="chartRegion"></div>
    </div>
    <div class="card">
        <h3 class="font-semibold text-gray-700 mb-3">Départs à la retraite (prévision)</h3>
        <div id="chartRetraite"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#1d4ed8', '#db2777', '#059669', '#d97706', '#7c3aed'];

    new ApexCharts(document.querySelector('#chartSexe'), {
        chart: { type: 'donut', height: 280 },
        labels: @json(array_keys($parSexe)),
        series: @json(array_values($parSexe)),
        colors: ['#1d4ed8', '#db2777'],
        legend: { position: 'bottom' },
    }).render();

    new ApexCharts(document.querySelector('#chartAge'), {
        chart: { type: 'bar', height: 280 },
        series: [{ name: 'Agents', data: @json(array_values($trancheAge)) }],
        xaxis: { categories: @json(array_keys($trancheAge)) },
        colors: ['#1d4ed8'],
        plotOptions: { bar: { borderRadius: 4 } },
        dataLabels: { enabled: false },
    }).render();

    new ApexCharts(document.querySelector('#chartRegion'), {
        chart: { type: 'bar', height: 320 },
        series: [{ name: 'Agents', data: @json(array_values($parRegion)) }],
        xaxis: { categories: @json(array_keys($parRegion)) },
        colors: ['#059669'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        dataLabels: { enabled: false },
    }).render();

    new ApexCharts(document.querySelector('#chartRetraite'), {
        chart: { type: 'line', height: 280 },
        series: [{ name: 'Départs', data: @json(array_values($departsRetraite)) }],
        xaxis: { categories: @json(array_keys($departsRetraite)) },
        colors: ['#d97706'],
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 4 },
    }).render();
});
</script>
@endpush
