@php
    $navLinks = \App\Support\PublicNavigation::links(url('/'));
    $loginHref = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    $publishHref = route('advertise');
@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Posts publicados para {{ $label }}.">
    <title>{{ $label }} | {{ $siteSettings->brandName() }}</title>
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
        <a href="{{ $directoryHref }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#E91E63] hover:text-[#C2185B]">
            <x-heroicon-o-arrow-left class="size-4" aria-hidden="true" />
            Volver al directorio
        </a>

        <div class="mt-7 max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">{{ $eyebrow }}</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight text-[#222222] sm:text-5xl">{{ $label }}</h1>
            <p class="mt-4 text-base leading-7 text-[#6B7280]">
                {{ trans_choice('{1} :count publicación activa|[2,*] :count publicaciones activas', $posts->total(), ['count' => $posts->total()]) }}
            </p>
        </div>

        <section class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($posts as $listing)
                <x-listing-card :listing="$listing" />
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-[#E5E7EB] bg-white p-10 text-center text-sm text-[#6B7280]">
                    {{ $emptyMessage }}
                </div>
            @endforelse
        </section>

        @if ($posts->hasPages())
            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
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
