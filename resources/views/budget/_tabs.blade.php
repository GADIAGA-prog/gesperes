{{-- Navigation entre les sous-modules du module Budget --}}
@php
    $tabs = [
        ['route' => 'budget.personnel', 'label' => 'Dépenses du personnel', 'active' => ['budget.personnel']],
        ['route' => 'budget.annexes', 'label' => 'Tableaux annexes', 'active' => ['budget.annexes']],
        ['route' => 'budget.enveloppe.index', 'label' => 'Enveloppe (n+1 à n+3)', 'active' => ['budget.enveloppe.*']],
        ['route' => 'budget.index', 'label' => 'Dépenses de fonctionnement', 'active' => ['budget.index', 'budget.show', 'budget.create', 'budget.edit', 'budget.import.form', 'budget.par']],
    ];
@endphp
<nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-4">
    @foreach ($tabs as $tab)
        @php $on = request()->routeIs(...$tab['active']); @endphp
        <a href="{{ route($tab['route']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $on ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
