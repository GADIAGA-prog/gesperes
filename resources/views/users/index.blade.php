@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('header', 'Comptes utilisateurs')
@section('content')
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $users->total() }} compte(s)</p>
    @can('users.create')<a href="{{ route('users.create') }}" class="btn btn-primary">+ Nouvel utilisateur</a>@endcan
</div>
<div class="card overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead><tr class="text-left text-xs uppercase text-gray-500">
            <th class="table-head">Nom</th><th class="table-head">E-mail</th>
            <th class="table-head">Rôles</th><th class="table-head">État</th><th class="table-head text-right"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($users as $u)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium">{{ $u->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-sm">
                        @foreach ($u->roles as $r)<span class="badge bg-institution-50 text-institution-700 mr-1">{{ $r->name }}</span>@endforeach
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge {{ $u->actif ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $u->actif ? 'Actif' : 'Inactif' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-sm">
                        @can('users.update')<a href="{{ route('users.edit', $u) }}" class="text-institution-600 hover:underline">Modifier</a>@endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
