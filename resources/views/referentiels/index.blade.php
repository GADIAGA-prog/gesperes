@extends('layouts.app')
@section('title', 'Référentiels')
@section('header', 'Référentiels & nomenclatures')
@section('content')
<p class="text-sm text-gray-500 mb-4">Gérez les nomenclatures de base utilisées dans les fiches agents.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($registre as $slug => $config)
        <a href="{{ route('referentiels.show', $slug) }}" class="card hover:shadow-md transition flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">{{ $config['titre'] }}</p>
                <p class="text-xs text-gray-500">{{ $config['model']::count() }} entrée(s)</p>
            </div>
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    @endforeach
</div>
@endsection
