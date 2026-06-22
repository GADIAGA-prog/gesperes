@extends('layouts.app')
@section('title', 'Structures')
@section('header', 'Organigramme des structures')

@section('content')
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $structures->count() }} structure(s)</p>
    @can('structures.create')
        <a href="{{ route('structures.create') }}" class="btn btn-primary">+ Nouvelle structure</a>
    @endcan
</div>

<div class="card">
    @if ($racines->isEmpty())
        <p class="text-center text-gray-400 py-8">Aucune structure enregistrée.</p>
    @else
        <ul class="space-y-1">
            @foreach ($racines as $racine)
                @include('structures._noeud', ['noeud' => $racine, 'tous' => $structures, 'niveau' => 0])
            @endforeach
        </ul>
    @endif
</div>
@endsection
