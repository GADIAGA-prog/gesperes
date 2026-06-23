<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <title>@yield('title', 'Tableau de bord') · {{ config('app.name', 'GesPerES') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-800">
<div x-data="{ sidebarOpen: false }" class="min-h-screen flex">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

    {{-- Contenu --}}
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
        {{-- Topbar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between px-4 h-16">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('header', 'Tableau de bord')</h1>
                </div>
                <div class="flex items-center gap-4" x-data="{ open: false }">
                    <span class="hidden sm:block text-sm text-gray-500">{{ now()->translatedFormat('l d F Y') }}</span>
                    <div class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-institution-100 text-institution-700">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Mon profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Déconnexion</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @include('partials.flash')
            @yield('content')
        </main>

        <footer class="px-6 py-4 text-center text-xs text-gray-400">
            {{ config('app.name') }} · v1.0 — DRH-MESFPT / Burkina Faso
        </footer>
    </div>
</div>

@include('partials.chatbox')
@stack('scripts')
</body>
</html>
