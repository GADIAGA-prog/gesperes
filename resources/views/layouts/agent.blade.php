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
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GesPerES">
    <link rel="apple-touch-icon" href="/images/icons/apple-touch-icon.png">
    <link rel="icon" href="/images/icons/favicon-32.png" type="image/png">

    <title>@yield('title', 'Espace agent') · {{ config('app.name', 'GesPerES') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php
    $u = auth()->user();
    $agentConnecte = $u?->agent;
    $notifsNonLues = $agentConnecte
        ? \App\Models\NotificationRh::where('agent_id', $agentConnecte->id)->where('lu', false)->count()
        : 0;
    $prenom = $agentConnecte?->prenoms ? \Illuminate\Support\Str::of($agentConnecte->prenoms)->explode(' ')->first() : $u?->name;
    $initiales = strtoupper(mb_substr($agentConnecte?->prenoms ?: $u->name, 0, 1) . mb_substr($agentConnecte?->nom ?: '', 0, 1));

    $nav = [
        ['route' => 'espace-agent.dashboard',     'label' => 'Accueil', 'icon' => '<path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.5a.75.75 0 00.75.75h4.5a.75.75 0 00.75-.75V15a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v5.25a.75.75 0 00.75.75h4.5a.75.75 0 00.75-.75V9.75"/>'],
        ['route' => 'espace-agent.profil',         'label' => 'Profil',  'icon' => '<path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>'],
        ['route' => 'espace-agent.actes',          'label' => 'Actes',   'icon' => '<path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>'],
        ['route' => 'espace-agent.notifications',  'label' => 'Notifs',  'icon' => '<path d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>', 'badge' => $notifsNonLues],
    ];
@endphp
<body class="font-sans antialiased bg-slate-50 text-slate-800" style="padding-top:env(safe-area-inset-top);">

{{-- ░░ En-tête dégradé ░░ --}}
<header class="bg-gradient-to-br from-institution-700 via-institution-700 to-institution-900 text-white shadow-lg">
    <div class="mx-auto max-w-5xl px-4 pt-4 pb-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/15 backdrop-blur text-base font-bold ring-1 ring-white/20">
                    {{ $initiales ?: 'A' }}
                </span>
                <div class="min-w-0">
                    <p class="text-[13px] leading-tight text-white/70">Bonjour,</p>
                    <p class="truncate text-[15px] font-semibold leading-tight">{{ $prenom }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2" x-data="{ open:false }">
                <a href="{{ route('espace-agent.notifications') }}" class="relative grid h-10 w-10 place-items-center rounded-xl bg-white/10 hover:bg-white/20 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    @if ($notifsNonLues > 0)
                        <span class="absolute -top-1 -right-1 grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-red-500 px-1 text-[11px] font-bold ring-2 ring-institution-700">{{ $notifsNonLues > 9 ? '9+' : $notifsNonLues }}</span>
                    @endif
                </a>
                <div class="relative">
                    <button @click="open=!open" class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 hover:bg-white/20 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open=false" x-transition
                         class="absolute right-0 z-30 mt-2 w-52 overflow-hidden rounded-xl bg-white text-slate-700 shadow-xl ring-1 ring-black/5">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">{{ $u->name }}</p>
                            <p class="text-xs text-slate-400">Matricule {{ $agentConnecte?->matricule }}</p>
                        </div>
                        <a href="{{ route('espace-agent.profil') }}" class="block px-4 py-2.5 text-sm hover:bg-slate-50">Mes informations</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Titre de page --}}
        <div class="mt-5">
            <h1 class="text-xl font-bold leading-tight">@yield('header', 'Espace agent')</h1>
            @hasSection('sous-titre')<p class="mt-0.5 text-sm text-white/70">@yield('sous-titre')</p>@endif
        </div>
    </div>

    {{-- Navigation horizontale (desktop) --}}
    <nav class="hidden lg:block bg-white/10 backdrop-blur">
        <div class="mx-auto flex max-w-5xl gap-1 px-4">
            @foreach ($nav as $item)
                @php $actif = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="relative flex items-center gap-2 px-4 py-3 text-sm font-medium transition {{ $actif ? 'text-white' : 'text-white/70 hover:text-white' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                    @if (($item['badge'] ?? 0) > 0)<span class="rounded-full bg-red-500 px-1.5 text-[11px] font-bold">{{ $item['badge'] }}</span>@endif
                    @if ($actif)<span class="absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-white"></span>@endif
                </a>
            @endforeach
        </div>
    </nav>
</header>

{{-- ░░ Contenu ░░ --}}
<main class="mx-auto w-full max-w-5xl px-4 pb-28 pt-5 lg:pb-10">
    @include('partials.flash')
    @yield('content')
</main>

{{-- ░░ Bannière d'installation (PWA) ░░ --}}
<div x-data="{ prompt:null, show:false,
        init(){
            window.addEventListener('beforeinstallprompt', e => { e.preventDefault(); this.prompt = e; this.show = true; });
            window.addEventListener('appinstalled', () => { this.show = false; });
        },
        async installer(){ if(!this.prompt) return; this.prompt.prompt(); await this.prompt.userChoice; this.prompt = null; this.show = false; } }"
     x-show="show" x-cloak x-transition
     class="fixed inset-x-0 z-40 mx-auto max-w-md px-4" style="bottom:calc(5.5rem + env(safe-area-inset-bottom));">
    <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-2xl ring-1 ring-black/5">
        <img src="/images/icons/icon-192.png" alt="" class="h-11 w-11 rounded-xl">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-slate-800">Installer l'application</p>
            <p class="truncate text-xs text-slate-400">Accès rapide depuis votre écran d'accueil</p>
        </div>
        <button @click="show=false" class="px-2 text-slate-400">&times;</button>
        <button @click="installer()" class="btn-primary px-3 py-2 text-xs">Installer</button>
    </div>
</div>

{{-- ░░ Barre d'onglets (mobile) ░░ --}}
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden"
     style="padding-bottom:env(safe-area-inset-bottom);">
    <div class="mx-auto grid max-w-5xl grid-cols-4">
        @foreach ($nav as $item)
            @php $actif = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition {{ $actif ? 'text-institution-700' : 'text-slate-400' }}">
                <span class="relative">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="{{ $actif ? '2' : '1.6' }}" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                    @if (($item['badge'] ?? 0) > 0)
                        <span class="absolute -top-1.5 -right-2 grid h-4 min-w-[1rem] place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</span>
                    @endif
                </span>
                {{ $item['label'] }}
                @if ($actif)<span class="absolute top-0 h-1 w-8 rounded-full bg-institution-700"></span>@endif
            </a>
        @endforeach
    </div>
</nav>

{{-- Enregistrement du service worker --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
@stack('scripts')
</body>
</html>
