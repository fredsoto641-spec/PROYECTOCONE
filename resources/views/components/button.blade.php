@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-[#E91E63] focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-60';

    $variants = [
        'primary' => 'bg-[#E91E63] text-white shadow-sm shadow-[#E91E63]/25 hover:bg-[#D81B60]',
        'secondary' => 'border border-[#E5E7EB] bg-white text-[#222222] hover:border-[#E91E63]/40 hover:text-[#E91E63]',
        'ghost' => 'text-[#222222] hover:bg-[#F8F8F8] hover:text-[#E91E63]',
        'light' => 'bg-white text-[#E91E63] hover:bg-[#F8F8F8]',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
