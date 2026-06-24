@php
    $navLinks = \App\Support\PublicNavigation::links(url('/'));

    $search = [
        'formLabel' => 'Buscar anuncios',
        'action' => '#',
        'cityLabel' => 'Ciudad',
        'categoryLabel' => 'Categoría',
        'queryLabel' => 'Texto libre',
        'cityPlaceholder' => 'Ciudad',
        'categoryPlaceholder' => 'Categoría',
        'queryPlaceholder' => 'Buscar por palabra clave',
        'submitLabel' => 'Buscar',
    ];

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
    <meta name="description" content="Posts publicados en la categoría {{ $category->name }}.">
    <title>{{ $category->name }} | {{ $siteSettings->brandName() }}</title>
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

    <main id="inicio">
        <x-hero
            eyebrow="Categoría"
            :title="$category->name"
            :subtitle="$category->description ?: $siteSettings->site_subtitle"
            :image="$category->image_url ?: ($siteSettings->cover_image_url ?: \App\Models\SiteSetting::DEFAULTS['cover_image_url'])"
            :cities="['Lima', 'Miraflores', 'San Isidro', 'Barranco']"
            :categories="[$category->name]"
            :search="$search"
        />

        <x-content-section id="posts" eyebrow="Publicaciones" :title="'Posts de '.$category->name" description="Explora solo las publicaciones disponibles dentro de esta categoría.">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($posts as $listing)
                    <x-listing-card :listing="$listing" />
                @empty
                    <div class="col-span-full rounded-3xl border border-[#E5E7EB] bg-white p-10 text-center text-sm text-[#6B7280]">
                        Todavía no hay posts publicados en esta categoría.
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @endif
        </x-content-section>

        <x-cta-section
            title="Haz que tu anuncio destaque desde el primer día"
            description="Publica con una presentación premium, diseño optimizado para móvil y componentes preparados para crecer con tu marketplace."
            button-label="Publicar Anuncio"
            :button-href="$publishHref"
        />

        <div id="zonas">
            <x-location-directory :directory="$locationDirectory" />
        </div>
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
