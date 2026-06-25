@props(['cle'])
{{-- En-tête de colonne triable : clic = tri asc, re-clic = desc. Préserve les
     filtres en cours (URL), revient en page 1. --}}
@php
    $triActuel  = request('tri');
    $sensActuel = request('sens') === 'desc' ? 'desc' : 'asc';
    $actif      = $triActuel === $cle;
    $prochain   = ($actif && $sensActuel === 'asc') ? 'desc' : 'asc';
    $params     = array_merge(request()->except('page'), ['tri' => $cle, 'sens' => $prochain]);
    $url        = request()->url() . '?' . http_build_query($params);
@endphp
<th {{ $attributes->merge(['class' => 'table-head']) }}>
    <a href="{{ $url }}" class="inline-flex items-center gap-1 hover:text-institution-700 whitespace-nowrap">
        <span>{{ $slot }}</span>
        @if ($actif)
            <span class="text-institution-600">{{ $sensActuel === 'asc' ? '▲' : '▼' }}</span>
        @else
            <span class="text-gray-300">↕</span>
        @endif
    </a>
</th>
