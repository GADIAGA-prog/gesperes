@extends('layouts.app')
@section('title', 'Droits du rôle')
@section('header', 'Droits : ' . $role->name)
@section('content')
<form method="POST" action="{{ route('roles.update', $role) }}" class="card">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($groupes as $groupe => $permissions)
            <div>
                <h4 class="font-semibold text-gray-700 text-sm mb-2 pb-1 border-b border-gray-100">{{ $groupe }}</h4>
                <div class="space-y-1.5">
                    @foreach ($permissions as $key => $label)
                        <label class="flex items-start gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                   {{ in_array($key, $attribuees) ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-gray-300 text-institution-600">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">Enregistrer les droits</button>
    </div>
</form>
@endsection
