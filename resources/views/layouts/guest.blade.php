<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'GesPerES') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="DRH-MESFPT" class="mx-auto h-24 w-24 object-contain">
            <h1 class="mt-3 text-2xl font-bold text-gray-800">{{ config('app.name', 'GesPerES') }}</h1>
            <p class="text-sm text-gray-500">Gestion du Personnel Enseignant du Secondaire</p>
        </div>
        <div class="w-full sm:max-w-md bg-white shadow-md rounded-xl p-6 sm:p-8">
            {{ $slot }}
        </div>
        <p class="mt-6 text-xs text-gray-400">© {{ date('Y') }} — DRH-MESFPT / Burkina Faso</p>
    </div>
</body>
</html>
