@props([
    'feature',
])

@php
    $icons = [
        'check' => '<path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" stroke-linecap="round" />',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" stroke-linejoin="round" />',
        'headset' => '<path d="M4 13a8 8 0 0 1 16 0" stroke-linecap="round" /><path d="M18 19c0 1-1 2-3 2h-2" stroke-linecap="round" /><path d="M4 13v3a2 2 0 0 0 2 2h1v-7H6a2 2 0 0 0-2 2Zm16 0v3a2 2 0 0 1-2 2h-1v-7h1a2 2 0 0 1 2 2Z" stroke-linejoin="round" />',
    ];
@endphp

<article class="rounded-3xl border border-[#E5E7EB] bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#222222]/10">
    <div class="grid size-12 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            {!! $icons[$feature['icon']] ?? $icons['check'] !!}
        </svg>
    </div>
    <h3 class="mt-5 text-lg font-bold text-[#222222]">{{ $feature['title'] }}</h3>
    <p class="mt-3 text-sm leading-6 text-[#6B7280]">{{ $feature['description'] }}</p>
</article>
