@props([
    'id' => null,
    'eyebrow' => null,
    'title',
    'description' => null,
])

<section @if($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'bg-[#F8F8F8] py-16 sm:py-20']) }}>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 max-w-2xl">
            @if ($eyebrow)
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">{{ $eyebrow }}</p>
            @endif
            <h2 class="mt-3 text-3xl font-black tracking-normal text-[#222222] sm:text-4xl">{{ $title }}</h2>
            @if ($description)
                <p class="mt-4 text-base leading-7 text-[#6B7280]">{{ $description }}</p>
            @endif
        </div>

        {{ $slot }}
    </div>
</section>
