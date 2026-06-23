@extends('layouts.app')
@section('title', 'Manuel d\'usage')
@section('header', 'Manuel d\'usage')

@section('content')
<p class="text-sm text-gray-500 mb-6">Guide d'utilisation de la plateforme GesPerES, par module. Besoin d'aide ciblée ? Utilisez l'assistant (bulle en bas à droite).</p>

<div class="space-y-8">
    @foreach ($rubriques as $module => $items)
        <section>
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 border-b border-gray-200 pb-2 mb-4">{{ $module }}</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ($items as $r)
                    <div class="card" id="manuel-{{ $r['id'] }}">
                        <p class="font-semibold text-gray-800 mb-1">{{ $r['titre'] }}</p>
                        <p class="text-sm text-gray-600">{{ $r['contenu'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection
