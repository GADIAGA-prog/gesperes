@extends('layouts.app')
@section('title','Acte disciplinaire')
@section('header',"Nouvel acte disciplinaire")
@section('content')
<form method="POST" action="{{ route('discipline.store') }}" class="card max-w-3xl">@csrf
    @include('discipline._form')
    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
        <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary">Enregistrer</button>
    </div>
</form>
@endsection
