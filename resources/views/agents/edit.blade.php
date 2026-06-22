@extends('layouts.app')
@section('title', 'Modifier un agent')
@section('header', 'Modifier : ' . $agent->nom_complet)

@section('content')
<form method="POST" action="{{ route('agents.update', $agent) }}" class="card">
    @csrf
    @method('PUT')
    @include('agents._form')
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
        @can('agents.delete')
            <button type="button" form="delete-agent" class="btn btn-danger"
                    onclick="if(confirm('Supprimer cet agent ?')) document.getElementById('delete-agent').submit()">Supprimer</button>
        @else
            <span></span>
        @endcan
        <div class="flex gap-2">
            <a href="{{ route('agents.show', $agent) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </div>
</form>
@can('agents.delete')
    <form id="delete-agent" method="POST" action="{{ route('agents.destroy', $agent) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endcan
@endsection
