@php
    $navLinks = \App\Support\PublicNavigation::links();

    $search = [
        'formLabel' => 'Buscar anuncios',
        'action' => route('posts.search'),
        'cityLabel' => 'Ciudad',
        'categoryLabel' => 'Categoría',
        'queryLabel' => 'Texto libre',
        'cityPlaceholder' => 'Ciudad',
        'categoryPlaceholder' => 'Categoría',
        'queryPlaceholder' => 'Buscar por palabra clave',
        'submitLabel' => 'Buscar',
    ];

    $siteSettings = $siteSettings ?? \App\Models\SiteSetting::current();
    $categories = collect($categories ?? []);
    $searchOptions = \App\Support\PublicSearchOptions::all();
    $latestPublications = collect($latestPublications ?? []);
    $premiumListings = collect($premiumListings ?? []);

    $locationDirectory = \App\Support\PublicLocationDirectory::make();

    $ageGate = $ageGate ?? \App\Models\AgeGateSetting::current()->toModalContent();

    $loginHref = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    $publishHref = route('advertise');

@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plataforma premium de clasificados con anuncios verificados, búsqueda avanzada y experiencia moderna.">
    <title>{{ $siteSettings->site_title }} | {{ $siteSettings->brandName() }}</title>
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
        logo-href="#inicio"
        :links="$navLinks"
        :login-href="$loginHref"
        login-label="Login"
        :publish-href="$publishHref"
        publish-label="Publicar Anuncio"
    />

    <main id="inicio">
        <x-hero
            eyebrow="Clasificados premium"
            :title="$siteSettings->site_title"
            :subtitle="$siteSettings->site_subtitle"
            
            :image="$siteSettings->cover_image_url ?: \App\Models\SiteSetting::DEFAULTS['cover_image_url']"
            :cities="$searchOptions['locations']"
            :categories="$searchOptions['categories']"
            :search="$search"
        />

        <x-content-section id="categorias" eyebrow="Explora" title="Categorías principales" description="Accede rápido a las zonas con mayor actividad y anuncios actualizados.">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($categories as $category)
                    <x-category-card :category="$category" />
                @empty
                    @for ($index = 0; $index < 3; $index++)
                        <div class="overflow-hidden rounded-3xl border border-[#E5E7EB] bg-white shadow-sm" aria-hidden="true">
                            <div class="skeleton-shimmer aspect-[16/10]"></div>
                            <div class="p-5">
                                <div class="w-full">
                                    <div class="skeleton-shimmer h-5 w-32 rounded-full"></div>
                                    <div class="skeleton-shimmer mt-3 h-4 w-24 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endforelse
            </div>
        </x-content-section>

        <x-content-section id="destacados" eyebrow="Selección premium" title="Anuncios destacados" description="Perfiles con mejor visibilidad, estado actualizado y señales de confianza.">
            @if ($premiumListings->count() > 4)
                <x-listing-cards-marquee :listings="$premiumListings->all()" />
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    @forelse ($premiumListings as $listing)
                        <x-listing-card :listing="$listing" />
                    @empty
                        <div class="col-span-full rounded-3xl border border-[#E5E7EB] bg-white p-10 text-center text-sm text-[#6B7280]">
                            Todavía no hay posts premium publicados.
                        </div>
                    @endforelse
                </div>
            @endif
        </x-content-section>

        <x-content-section id="recientes" eyebrow="Actividad" title="Últimas publicaciones" description="Anuncios recientes de la plataforma, organizados para descubrir actividad nueva de forma rápida y discreta.">
            <x-latest-publications-marquee :publications="$latestPublications" />
        </x-content-section>

        <x-cta-section
            title="Haz que tu anuncio destaque desde el primer día"
            description="Publica con una presentación premium, diseño optimizado para móvil y componentes preparados para crecer con tu marketplace."
            button-label="Publicar Anuncio"
            :button-href="$publishHref"
        />

        <x-location-directory :directory="$locationDirectory" />
    </main>

    <x-footer
        :brand="$siteSettings->brandName()"
        :brand-initials="$siteSettings->brandInitials()"
        brand-href="#inicio"
        description="Clasificados premium con una experiencia moderna, clara y confiable."
        copyright="Todos los derechos reservados."
        :groups="$siteSettings->footerGroups()"
        :legal-links="$siteSettings->footerLegalLinks()"
    />

    <x-age-confirmation-modal :content="$ageGate" />
</body>
</html>
