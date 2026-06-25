@props(['cle', 'placeholder' => 'Filtrer…'])
{{-- Cellule de filtre par colonne (sous l'en-tête). L'input fait partie du
     formulaire GET qui entoure le tableau : Entrée ou « Filtrer » applique. --}}
@php $valeurs = (array) request('f', []); @endphp
<th class="px-3 py-1.5 font-normal">
    <input type="text" name="f[{{ $cle }}]" value="{{ $valeurs[$cle] ?? '' }}"
           placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'w-full rounded border-gray-200 text-xs py-1 px-2 font-normal placeholder:text-gray-400']) }}>
</th>
