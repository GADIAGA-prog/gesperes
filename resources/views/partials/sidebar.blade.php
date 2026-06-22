<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-30 w-64 bg-institution-800 text-institution-100 transform transition-transform duration-200 lg:translate-x-0">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-institution-700">
        <img src="{{ asset('images/logo.png') }}" alt="DRH-MESFPT"
             class="h-10 w-10 shrink-0 rounded-lg bg-white object-contain p-0.5">
        <div>
            <p class="font-bold leading-none text-white">GesPerES</p>
            <p class="text-[10px] text-institution-300">DRH-MESFPT</p>
        </div>
    </div>

    <nav class="px-3 py-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        @php
            $nav = [
                ['route' => 'dashboard', 'label' => 'Tableau de bord', 'perm' => 'dashboard.view', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'agents.index', 'label' => 'Agents', 'perm' => 'agents.view', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 10-8 0 4 4 0 008 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z', 'match' => 'agents.*'],
                ['route' => 'structures.index', 'label' => 'Structures', 'perm' => 'structures.view', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1', 'match' => 'structures.*'],
                ['route' => 'affectations.index', 'label' => 'Affectations', 'perm' => 'affectations.view', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'match' => 'affectations.*'],
                ['route' => 'referentiels.index', 'label' => 'Référentiels', 'perm' => 'settings.view', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'match' => 'referentiels.*'],
                ['route' => 'users.index', 'label' => 'Utilisateurs', 'perm' => 'users.view', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'match' => 'users.*'],
                ['route' => 'roles.index', 'label' => 'Rôles & droits', 'perm' => 'users.view', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'match' => 'roles.*'],
                ['route' => 'audit.index', 'label' => 'Journal d\'audit', 'perm' => 'audit.view', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'match' => 'audit.*'],
            ];
        @endphp

        @foreach ($nav as $item)
            @can($item['perm'])
                @php $active = isset($item['match']) ? request()->routeIs($item['match']) : request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $active ? 'bg-institution-700 text-white' : 'text-institution-200 hover:bg-institution-700/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endcan
        @endforeach
    </nav>
</aside>
