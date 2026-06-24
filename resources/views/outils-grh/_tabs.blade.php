{{-- Navigation des sous-modules « Outils GRH » --}}
@php
    $tabs = [
        ['route' => 'gpec.index', 'label' => 'GPEC', 'active' => ['gpec.*'], 'perm' => 'gpec.view'],
        ['route' => 'fiches-poste.index', 'label' => 'Fiches de poste', 'active' => ['fiches-poste.*'], 'perm' => 'fiches-poste.view'],
        ['route' => 'outils-grh.tpee', 'label' => 'TPEE', 'active' => ['outils-grh.tpee'], 'perm' => 'gpec.view'],
        ['route' => 'outils-grh.referentiels-mpp', 'label' => 'Référentiels MPP GRH', 'active' => ['outils-grh.referentiels-mpp'], 'perm' => 'gpec.view'],
        ['route' => 'plan-formation.index', 'label' => 'Plan de formation', 'active' => ['plan-formation.*', 'actions-formation.*'], 'perm' => 'formations.view'],
        ['route' => 'besoins-formation.index', 'label' => 'Besoins de formation', 'active' => ['besoins-formation.*'], 'perm' => 'formations.view'],
        ['route' => 'alertes.index', 'label' => 'Alertes RH', 'active' => ['alertes.*'], 'perm' => 'alertes.view'],
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
