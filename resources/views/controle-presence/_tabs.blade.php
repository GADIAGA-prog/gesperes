{{-- Navigation des sous-modules « Contrôle présence » --}}
@php
    $tabs = [
        ['route' => 'pointages.index', 'label' => 'Pointage', 'active' => ['pointages.*'], 'perm' => 'pointage.view'],
        ['route' => 'conges.index', 'label' => 'Congés', 'active' => ['conges.*'], 'perm' => 'conges.view'],
        ['route' => 'fiches.index', 'label' => 'Fiches de présence', 'active' => ['fiches.*'], 'perm' => 'presence.reports'],
    ];
@endphp
<nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-4">
    @foreach ($tabs as $tab)
        @can($tab['perm'])
            @php $on = request()->routeIs(...$tab['active']); @endphp
            <a href="{{ route($tab['route']) }}"
               class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $on ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ $tab['label'] }}
            </a>
        @endcan
    @endforeach
</nav>
