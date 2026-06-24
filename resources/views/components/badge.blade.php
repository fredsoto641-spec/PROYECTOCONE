@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'primary' => 'bg-[#E91E63] text-white',
        'verified' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        'featured' => 'bg-[#E91E63]/10 text-[#E91E63] ring-1 ring-[#E91E63]/15',
        'neutral' => 'bg-white/90 text-[#222222] ring-1 ring-[#E5E7EB]',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.($variants[$variant] ?? $variants['neutral'])]) }}>
    {{ $slot }}
</span>
