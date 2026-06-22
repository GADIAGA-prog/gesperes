@extends('layouts.app')
@section('title', 'Modifier un utilisateur')
@section('header', 'Modifier : ' . $user->name)
@section('content')
<form method="POST" action="{{ route('users.update', $user) }}" class="card max-w-3xl">
    @csrf @method('PUT')
    @include('users._form')
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
        @can('delete', $user)
            <button type="button" class="btn btn-danger" onclick="if(confirm('Supprimer cet utilisateur ?')) document.getElementById('del-user').submit()">Supprimer</button>
        @else <span></span> @endcan
        <div class="flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </div>
</form>
@can('delete', $user)
    <form id="del-user" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">@csrf @method('DELETE')</form>
@endcan
@endsection
