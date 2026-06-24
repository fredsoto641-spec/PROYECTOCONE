@php
    $navLinks = \App\Support\PublicNavigation::links(url('/'));

    $fallbackCoverImage = 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1200&q=80';
    $splitImageUrls = static fn ($values) => collect(is_array($values) ? $values : [$values])
        ->flatMap(fn ($value) => preg_split('/\R/u', (string) $value) ?: [])
        ->map(fn (string $url): string => trim($url))
        ->filter()
        ->values();
    $coverImages = $splitImageUrls($post->cover_image_url);
    $coverImage = $coverImages->first() ?: $fallbackCoverImage;
    $gallery = $coverImages
        ->slice(1)
        ->merge($splitImageUrls($post->gallery_image_urls ?? []))
        ->values();
    $galleryImages = collect([$coverImage])->merge($gallery)->values();
    $contactButtons = collect($contactButtons ?? []);
    $postCards = collect($postCards ?? []);
    $loginHref = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    $publishHref = route('advertise');

    $ageGate = $ageGate ?? \App\Models\AgeGateSetting::current()->toModalContent();
@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $post->subtitle ?: $post->title }}">
    <title>{{ $post->title }} | {{ $siteSettings->brandName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <style>
        {!! $siteSettings->inlineCssVariableBlock() !!}
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="background-color: var(--site-bg); color: var(--site-text);">
    <x-navbar
        :logo-primary="$siteSettings->brand_primary_text"
        :logo-accent="$siteSettings->brand_accent_text"
        logo-href="{{ url('/') }}"
        :links="$navLinks"
        :login-href="$loginHref"
        login-label="Login"
        :publish-href="$publishHref"
        publish-label="Publicar Anuncio"
    />

    <main class="mx-auto grid max-w-7xl gap-8 px-6 py-10 lg:grid-cols-4 lg:px-8">
        <article class="lg:col-span-3">
            <div class="overflow-hidden rounded-3xl border border-[#E5E7EB] bg-white shadow-sm">
                <div class="relative aspect-[16/9] overflow-hidden bg-[#F3F4F6]">
                    <button
                        type="button"
                        class="group block h-full w-full cursor-zoom-in focus:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-[#E91E63]"
                        x-data
                        x-on:click="$dispatch('open-post-gallery', { index: 0 })"
                        aria-label="Ampliar imagen de portada de {{ $post->title }}"
                    >
                        <img
                            src="{{ $coverImage }}"
                            alt="{{ $post->title }}"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                        >
                    </button>

                    <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                        @if ($tags->contains('verificado'))
                            <x-badge variant="verified">Verificado</x-badge>
                        @endif
                        @if ($post->is_vip || $tags->contains('destacado'))
                            <x-badge variant="featured">Destacado</x-badge>
                        @endif
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.12em] text-[#E91E63]">
                            <a href="{{ route('categories.public.show', ['category' => $category->slug]) }}" class="hover:text-[#C2185B]">{{ $category->name }}</a>
                            <span class="size-1 rounded-full bg-[#E5E7EB]"></span>
                            <span>{{ $siteSettings->server_country }}</span>
                            @if ($post->published_at)
                                <span class="size-1 rounded-full bg-[#E5E7EB]"></span>
                                <span>{{ $post->published_at->diffForHumans() }}</span>
                            @endif
                        </div>

                        @if ($post->location)
                            <a
                                href="{{ route('posts.locations.show', ['location' => \Illuminate\Support\Str::slug($post->location)]) }}"
                                class="inline-flex cursor-pointer items-center gap-2 self-start rounded-full bg-[#F3F4F6] px-3 py-1.5 text-xs font-bold uppercase tracking-[0.08em] text-[#374151] transition hover:bg-[#E91E63]/10 hover:text-[#E91E63] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E91E63] focus-visible:ring-offset-2 sm:justify-end"
                                aria-label="Ver posts en {{ $post->location }}"
                            >
                                <x-heroicon-o-map-pin class="size-4 text-[#E91E63]" aria-hidden="true" />
                                <span>{{ $post->location }}</span>
                            </a>
                        @endif
                    </div>

                    <h1 class="max-w-3xl text-3xl font-black leading-tight text-[#222222] md:text-5xl">{{ $post->title }}</h1>

                    @if ($post->subtitle)
                        <p class="mt-4 max-w-3xl text-lg leading-8 text-[#6B7280]">{{ $post->subtitle }}</p>
                    @endif

                    @if ($post->tags)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a
                                    href="{{ route('posts.tags.show', ['tag' => \Illuminate\Support\Str::slug($tag)]) }}"
                                    class="rounded-full bg-[#F3F4F6] px-3 py-1 text-xs font-semibold text-[#374151] transition hover:bg-[#E91E63]/10 hover:text-[#E91E63]"
                                >
                                    #{{ $tag }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="prose prose-neutral mt-10 max-w-none space-y-4 text-[#374151]">
                        {!! \App\Support\PostBodyRenderer::render($post->body) !!}
                    </div>

                    @if ($gallery->isNotEmpty())
                        <section class="mt-10">
                            <h2 class="text-xl font-bold text-[#222222]">Galería</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @foreach ($gallery as $image)
                                    <button
                                        type="button"
                                        class="group cursor-zoom-in overflow-hidden rounded-2xl focus:outline-none focus-visible:ring-4 focus-visible:ring-[#E91E63] focus-visible:ring-offset-2"
                                        x-data
                                        x-on:click="$dispatch('open-post-gallery', { index: {{ $loop->index + 1 }} })"
                                        aria-label="Ampliar imagen {{ $loop->iteration }} de la galería de {{ $post->title }}"
                                    >
                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $post->title }}"
                                            class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                            loading="lazy"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>

            <section class="mt-10">
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#E91E63]">Más de {{ $category->name }}</p>
                    <h2 class="mt-2 text-2xl font-black text-[#222222]">Posts relacionados</h2>
                </div>

                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($relatedPosts as $relatedPost)
                        <x-listing-card :listing="$relatedPost" />
                    @empty
                        <p class="col-span-full rounded-3xl border border-dashed border-[#E5E7EB] bg-white p-8 text-center text-sm text-[#6B7280]">
                            Todavía no hay otros posts publicados en esta categoría.
                        </p>
                    @endforelse
                </div>
            </section>
        </article>

        <aside class="space-y-5 lg:col-span-1">
            <div class="sticky top-24 space-y-5">
                @foreach ($postCards as $card)
                    @php
                        $cardColor = $card->color ?? '#E91E63';
                        $fillsBackground = (bool) ($card->fill_background ?? false);
                    @endphp
                    <section
                        class="overflow-hidden rounded-3xl border shadow-sm {{ $fillsBackground ? 'border-transparent text-white' : 'border-[#E5E7EB] bg-white' }}"
                        @if ($fillsBackground) style="background-color: {{ $cardColor }};" @endif
                    >
                        @unless ($fillsBackground)
                            <div class="h-1.5" style="background-color: {{ $cardColor }};"></div>
                        @endunless
                        <div class="p-5">
                        <h2 class="text-lg font-bold {{ $fillsBackground ? 'text-white' : 'text-[#222222]' }}">{{ $card->title }}</h2>
                        <dl class="mt-4 space-y-3">
                            @foreach ($card->fields ?? [] as $field)
                                <div class="flex items-start justify-between gap-4 border-b pb-3 last:border-b-0 last:pb-0 {{ $fillsBackground ? 'border-white/25' : 'border-[#F3F4F6]' }}">
                                    <dt class="text-sm font-semibold {{ $fillsBackground ? 'text-white/80' : 'text-[#6B7280]' }}">{{ $field['key'] ?? '' }}</dt>
                                    <dd class="text-right text-sm font-bold {{ $fillsBackground ? 'text-white' : 'text-[#222222]' }}">{{ $field['value'] ?? '' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        </div>
                    </section>
                @endforeach

                <section class="rounded-3xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#E91E63]">Contacto</p>

                    <div class="mt-5 space-y-3">
                        @foreach ($contactButtons as $contact)
                            @if ($contact['external'])
                                <x-button href="{{ $contact['href'] }}" variant="secondary" class="w-full justify-start border-transparent text-white hover:text-white" target="_blank" rel="noopener noreferrer" style="background-color: {{ $contact['button_color'] }};">
                                    <x-dynamic-component :component="$contact['icon']" class="size-5" />
                                    {{ $contact['label'] }}
                                </x-button>
                            @else
                                <x-button href="{{ $contact['href'] }}" variant="secondary" class="w-full justify-start border-transparent text-white hover:text-white" style="background-color: {{ $contact['button_color'] }};">
                                    <x-dynamic-component :component="$contact['icon']" class="size-5" />
                                    {{ $contact['label'] }}
                                </x-button>
                            @endif
                        @endforeach
                    </div>

                    @if ($contactButtons->isEmpty())
                        <p class="mt-5 rounded-2xl border border-dashed border-[#E5E7EB] p-4 text-sm text-[#6B7280]">
                            Este post todavía no tiene canales de contacto activos.
                        </p>
                    @endif
                </section>
            </div>
        </aside>
    </main>

    <x-footer
        :brand="$siteSettings->brandName()"
        :brand-initials="$siteSettings->brandInitials()"
        brand-href="{{ url('/') }}"
        description="Clasificados premium con una experiencia moderna, clara y confiable."
        copyright="Todos los derechos reservados."
        :groups="$siteSettings->footerGroups()"
        :legal-links="$siteSettings->footerLegalLinks()"
    />

    <x-post-image-gallery :images="$galleryImages" :title="$post->title" />
    <x-age-confirmation-modal :content="$ageGate" />
</body>
</html>
