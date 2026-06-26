<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GesPerES">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
    <link rel="icon" href="/images/icons/favicon-32.png" type="image/png">

    <title>@yield('title', 'Espace agent') · {{ config('app.name', 'GesPerES') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $logoFile = collect(['logo.png', 'logo.svg', 'logo.webp', 'logo.jpg'])->first(fn ($f) => is_file(public_path('images/' . $f)));
@endphp
<body class="font-sans antialiased text-slate-800">
<div class="relative flex min-h-screen flex-col bg-gradient-to-br from-institution-700 via-institution-800 to-institution-900"
     style="padding-top:env(safe-area-inset-top);">

    {{-- En-tête de marque --}}
    <div class="flex flex-col items-center px-6 pt-12 pb-8 text-center text-white">
        <span class="grid h-20 w-20 place-items-center overflow-hidden rounded-3xl bg-white/15 shadow-xl ring-1 ring-white/20 backdrop-blur">
            @if ($logoFile)
                <img src="{{ asset('images/' . $logoFile) }}" alt="GesPerES" class="h-14 w-14 object-contain">
            @else
                <span class="text-2xl font-bold">G</span>
            @endif
        </span>
        <h1 class="mt-4 text-2xl font-bold">{{ config('app.name', 'GesPerES') }}</h1>
        <p class="mt-1 text-sm text-white/70">Espace agent — Personnel enseignant du secondaire</p>
    </div>

    {{-- Feuille de contenu --}}
    <div class="flex-1 rounded-t-3xl bg-slate-50 px-5 pb-10 pt-7 shadow-2xl">
        <div class="mx-auto w-full max-w-sm">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-administ-500/10 px-4 py-3 text-sm text-administ-600 ring-1 ring-administ-500/20">{{ session('status') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <p class="bg-slate-50 pb-6 text-center text-xs text-slate-400">© {{ date('Y') }} — DRH-MESFPT / Burkina Faso</p>
</div>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
</body>
</html>
