{{-- Navigation des sous-modules « Configurations » : un onglet par groupe de
     référentiels + les Indemnités. --}}
@php
    $groupes = \App\Support\ReferentielRegistry::groupes();
    $surReferentiels = request()->routeIs('referentiels.*');
    $groupeActif = $surReferentiels ? (request('groupe') ?: array_key_first($groupes)) : null;
@endphp
<nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-4">
    @can('settings.view')
        @foreach ($groupes as $cle => $g)
            @php $on = $groupeActif === $cle; @endphp
            <a href="{{ route('referentiels.index', ['groupe' => $cle]) }}"
               class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $on ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ \Illuminate\Support\Str::before($g['label'], ' (') }}
            </a>
        @endforeach
    @endcan
    @can('indemnites.view')
        @php $on = request()->routeIs('indemnites.*'); @endphp
        <a href="{{ route('indemnites.index') }}"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $on ? 'border-institution-600 text-institution-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Indemnités
        </a>
    @endcan
</nav>
