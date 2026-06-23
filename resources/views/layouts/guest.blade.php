<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GesPerES') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    // Détection automatique du logo déposé dans public/images (quelle que soit l'extension).
    $logoFile = collect(['logo.png', 'logo.svg', 'logo.webp', 'logo.jpg', 'logo.jpeg'])
        ->first(fn ($f) => is_file(public_path('images/' . $f)));
    $logoUrl = $logoFile ? asset('images/' . $logoFile) : null;
@endphp
<body class="font-sans antialiased bg-gray-100">
    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 overflow-hidden">

        {{-- Logo en filigrane d'arrière-plan de la zone de connexion --}}
        @if ($logoUrl)
            <div class="pointer-events-none absolute inset-0 bg-center bg-no-repeat opacity-10"
                 style="background-image:url('{{ $logoUrl }}'); background-size:min(85vmin,560px);"></div>
        @endif

        <div class="relative z-10 w-full flex flex-col items-center">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name', 'GesPerES') }}</h1>
                <p class="text-sm text-gray-500">Gestion du Personnel Enseignant du Secondaire</p>
            </div>
            <div class="w-full sm:max-w-md bg-white/95 backdrop-blur-sm shadow-xl rounded-xl p-6 sm:p-8">
                {{ $slot }}
            </div>
            <p class="mt-6 text-xs text-gray-400">© {{ date('Y') }} — DRH-MESFPT / Burkina Faso</p>
        </div>
    </div>
</body>
</html>
