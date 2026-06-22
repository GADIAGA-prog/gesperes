@extends('layouts.app')
@section('title', 'Modifier une structure')
@section('header', 'Modifier : ' . $structure->libelle)
@section('content')
<form method="POST" action="{{ route('structures.update', $structure) }}" class="card max-w-3xl">
    @csrf @method('PUT')
    @include('structures._form')
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
        @can('structures.delete')
            <button type="button" class="btn btn-danger"
                    onclick="if(confirm('Supprimer cette structure ?')) document.getElementById('del-struct').submit()">Supprimer</button>
        @else <span></span> @endcan
        <div class="flex gap-2">
            <a href="{{ route('structures.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </div>
</form>
@can('structures.delete')
    <form id="del-struct" method="POST" action="{{ route('structures.destroy', $structure) }}" class="hidden">@csrf @method('DELETE')</form>
@endcan
@endsection
