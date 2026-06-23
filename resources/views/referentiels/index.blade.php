@extends('layouts.app')
@section('title', 'Référentiels')
@section('header', 'Configurations — Référentiels & nomenclatures')
@section('content')
@include('configurations._tabs')
<p class="text-sm text-gray-500 mb-6">Gérez les nomenclatures de base utilisées dans les fiches agents et le budget.</p>

@foreach ($groupes as $g)
    <section class="mb-8">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 border-b border-gray-200 pb-2 mb-4">{{ $g['label'] }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($g['items'] as $slug => $config)
                <a href="{{ route('referentiels.show', $slug) }}" class="card hover:shadow-md transition flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $config['titre'] }}</p>
                        <p class="text-xs text-gray-500">{{ $config['model']::count() }} entrée(s)</p>
                    </div>
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </section>
@endforeach
@endsection
