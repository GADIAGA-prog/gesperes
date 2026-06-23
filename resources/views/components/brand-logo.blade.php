@props([
    'size' => 'h-10 w-10',   // dimensions du logo / du monogramme
    'rounded' => 'rounded-lg',
    'fallback' => 'bg-white text-institution-800', // couleurs du monogramme de repli
])

@php
    // Détecte automatiquement le logo déposé dans public/images (quelle que soit l'extension).
    $logo = collect(['logo.png', 'logo.svg', 'logo.webp', 'logo.jpg', 'logo.jpeg'])
        ->first(fn ($f) => is_file(public_path('images/' . $f)));
@endphp

@if ($logo)
    <img src="{{ asset('images/' . $logo) }}" alt="DRH-MESFPT"
         {{ $attributes->merge(['class' => $size . ' ' . $rounded . ' object-contain']) }}>
@else
    {{-- Repli propre tant que le logo n'est pas déposé (évite l'image cassée) --}}
    <span {{ $attributes->merge(['class' => $size . ' ' . $rounded . ' ' . $fallback . ' inline-flex items-center justify-center font-bold']) }}>
        G
    </span>
@endif
