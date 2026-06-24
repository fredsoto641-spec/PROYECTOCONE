@props([
    'directory',
])

<section class="bg-[#F8F8F8] px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">{{ $directory['eyebrow'] }}</p>
            <h2 class="mt-3 text-3xl font-black tracking-normal text-[#222222] sm:text-4xl">{{ $directory['title'] }}</h2>
            <p class="mt-4 text-base leading-7 text-[#6B7280]">{{ $directory['description'] }}</p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($directory['groups'] as $group)
                <x-location-group :group="$group" />
            @endforeach
        </div>
    </div>
</section>
