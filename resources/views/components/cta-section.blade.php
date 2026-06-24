@props([
    'title',
    'description',
    'buttonLabel',
    'buttonHref' => '#',
])

<section class="bg-[#F8F8F8] px-4 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-[#E91E63] px-6 py-12 text-white shadow-xl shadow-[#E91E63]/20 sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-12 lg:px-12">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-black tracking-normal sm:text-4xl">{{ $title }}</h2>
            <p class="mt-4 text-base leading-7 text-white/85">{{ $description }}</p>
        </div>
        <x-button :href="$buttonHref" variant="light" size="lg" class="mt-8 shrink-0 lg:mt-0">{{ $buttonLabel }}</x-button>
    </div>
</section>
