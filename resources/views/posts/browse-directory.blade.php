@php
    $navLinks = \App\Support\PublicNavigation::links(url('/'));
    $loginHref = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    $publishHref = route('advertise');
    $otherDirectoryHref = route($type === 'location' ? 'posts.tags.index' : 'posts.locations.index');
    $otherDirectoryLabel = $type === 'location' ? 'Explorar etiquetas' : 'Explorar ubicaciones';
@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title }} | {{ $siteSettings->brandName() }}</title>
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

    <main id="inicio" class="mx-auto min-h-[60vh] max-w-7xl px-6 py-12 lg:px-8">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">{{ $eyebrow }}</p>
                <h1 class="mt-3 text-4xl font-black tracking-tight text-[#222222] sm:text-5xl">{{ $title }}</h1>
                <p class="mt-4 text-base leading-7 text-[#6B7280]">{{ $description }}</p>
            </div>

            <x-button :href="$otherDirectoryHref" variant="secondary">
                {{ $otherDirectoryLabel }}
            </x-button>
        </div>

        <section class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($groups as $group)
                <a
                    href="{{ $group['href'] }}"
                    class="group rounded-3xl border border-[#E5E7EB] bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[#E91E63]/30 hover:shadow-xl hover:shadow-[#222222]/10 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#E91E63]/40"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-black text-[#222222] transition group-hover:text-[#E91E63]">{{ $group['label'] }}</h2>
                            <p class="mt-2 text-sm text-[#6B7280]">
                                {{ trans_choice('{1} :count post|[2,*] :count posts', $group['count'], ['count' => $group['count']]) }}
                            </p>
                        </div>
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]">
                            @if ($type === 'location')
                                <x-heroicon-o-map-pin class="size-6" aria-hidden="true" />
                            @else
                                <x-heroicon-o-tag class="size-6" aria-hidden="true" />
                            @endif
                        </span>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-[#E5E7EB] bg-white p-10 text-center text-sm text-[#6B7280]">
                    Todavía no hay posts activos para construir este directorio.
                </div>
            @endforelse
        </section>
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

    <x-age-confirmation-modal :content="$ageGate" />
</body>
</html>
