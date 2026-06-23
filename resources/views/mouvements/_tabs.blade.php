@include('carriere-mouvement._tabs')
{{-- Navigation entre les vues du module Mouvements --}}
@php
    $tabs = [
        ['route' => 'affectations.index', 'label' => 'Affectations', 'active' => 'affectations.*', 'perm' => 'affectations.view'],
        ['route' => 'mouvements.index', 'label' => 'Changements de position', 'active' => 'mouvements.index', 'perm' => 'mouvements.view'],
        ['route' => 'mouvements.sorties-temporaires', 'label' => 'Sorties temporaires', 'active' => 'mouvements.sorties-temporaires', 'perm' => 'mouvements.view'],
        ['route' => 'mouvements.sorties-definitives', 'label' => 'Sorties définitives', 'active' => 'mouvements.sorties-definitives', 'perm' => 'mouvements.view'],
    ];
@endphp
<nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-4">
    @foreach ($tabs as $tab)
        @can($tab['perm'])
            @php $on = request()->routeIs($tab['active']); @endphp
            <a href="{{ route($tab['route']) }}"
               class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $on ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ $tab['label'] }}
            </a>
        @endcan
    @endforeach
</nav>
