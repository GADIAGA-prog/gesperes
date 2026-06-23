@extends('layouts.app')
@section('title', 'Rôles & droits')
@section('header', 'Gestion accès — Rôles & droits d\'accès')
@section('content')
@include('gestion-acces._tabs')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($roles as $role)
        <div class="card">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold text-gray-800">{{ $role->name }}</h3>
                <span class="text-xs text-gray-400">{{ $role->users_count }} utilisateur(s)</span>
            </div>
            <p class="text-xs text-gray-500 mb-3">{{ $role->permissions->count() }} permission(s)</p>
            @can('users.update')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-secondary w-full justify-center text-sm">Gérer les droits</a>
            @endcan
        </div>
    @endforeach
</div>
@endsection
