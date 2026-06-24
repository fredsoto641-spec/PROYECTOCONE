@props([
    'eyebrow',
    'title',
    'subtitle',
    'image',
    'cities' => [],
    'categories' => [],
    'search',
    'selectedSearch' => [],
])

<section class="relative isolate min-h-[70vh] overflow-hidden">
    <img src="{{ $image }}" alt="" class="absolute inset-0 -z-20 h-full w-full scale-105 object-cover blur-[2px]" loading="eager">
    <div class="absolute inset-0 -z-10 bg-[linear-gradient(135deg,rgba(0,0,0,0.45),rgba(88,28,135,0.35),rgba(0,0,0,0.58))]"></div>

    <div class="mx-auto flex min-h-[70vh] max-w-7xl flex-col justify-center px-4 py-24 sm:px-6 lg:px-8">
        <div class="max-w-3xl text-white">
            <p class="mb-4 text-sm font-bold uppercase tracking-[0.24em] text-white/80">{{ $eyebrow }}</p>
            <h1 class="text-4xl font-black leading-tight tracking-normal sm:text-5xl lg:text-6xl">{{ $title }}</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-white/85 sm:text-lg">{{ $subtitle }}</p>
        </div>

        <div class="mt-10">
            <x-search-bar :cities="$cities" :categories="$categories" :labels="$search" :selected="$selectedSearch" />
        </div>
    </div>
</section>
