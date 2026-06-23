<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-30 w-64 bg-institution-800 text-institution-100 transform transition-transform duration-200 lg:translate-x-0">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-institution-700">
        <x-brand-logo size="h-10 w-10 shrink-0" class="bg-white p-0.5" />
        <div>
            <p class="font-bold leading-none text-white">GesPerES</p>
            <p class="text-[10px] text-institution-300">DRH-MESFPT</p>
        </div>
    </div>

    <nav class="px-3 py-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        @php
            $nav = [
                ['route' => 'dashboard', 'label' => 'Tableau de bord', 'perm' => 'dashboard.view', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'agents.index', 'label' => 'Gestion des effectifs', 'perm' => ['agents.view', 'structures.view'], 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 10-8 0 4 4 0 008 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z', 'match' => ['agents.*', 'structures.*']],
                ['route' => 'carriere.index', 'label' => 'Carrière et mouvement', 'perm' => ['carriere.view', 'mouvements.view'], 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'match' => ['carriere.*', 'mouvements.*', 'affectations.*']],
                ['route' => 'documents.recherche', 'label' => 'Actes et archives', 'perm' => 'documents.view', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'match' => 'documents.*'],
                ['route' => 'conges.index', 'label' => 'Contrôle présence', 'perm' => ['pointage.view', 'conges.view', 'presence.reports'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'match' => ['pointages.*', 'conges.*', 'fiches.*']],
                ['route' => 'performance.index', 'label' => 'Évaluation', 'perm' => ['competences.view', 'performance.view', 'discipline.view'], 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'match' => ['competences.*', 'performance.*', 'discipline.*']],
                ['route' => 'gpec.index', 'label' => 'Outils GRH', 'perm' => 'gpec.view', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'match' => ['gpec.*', 'outils-grh.*']],
                ['route' => 'budget.personnel', 'label' => 'Budget', 'perm' => 'budget.view', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'match' => 'budget.*'],
                ['route' => 'indemnites.index', 'label' => 'Configurations', 'perm' => ['settings.view', 'indemnites.view'], 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'match' => ['referentiels.*', 'indemnites.*']],
                ['route' => 'users.index', 'label' => 'Gestion accès', 'perm' => ['users.view', 'audit.view'], 'icon' => 'M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4', 'match' => ['users.*', 'roles.*', 'audit.*']],
                ['route' => 'manuel.index', 'label' => 'Manuel d\'usage', 'perm' => 'dashboard.view', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'match' => 'manuel.*'],
            ];
        @endphp

        @foreach ($nav as $item)
            @canany((array) $item['perm'])
                @php $active = isset($item['match']) ? request()->routeIs(...(array) $item['match']) : request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ $active ? 'bg-institution-700 text-white' : 'text-institution-200 hover:bg-institution-700/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endcanany
        @endforeach
    </nav>
</aside>
