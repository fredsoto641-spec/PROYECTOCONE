@php
    $navLinks = \App\Support\PublicNavigation::links(url('/'));
    $loginHref = \Illuminate\Support\Facades\Route::has('login') ? route('login') : '#';
    $contactButtons = collect($contactButtons ?? []);
@endphp

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce los tipos de anuncio, beneficios y opciones para publicar en {{ $siteSettings->brandName() }}.">
    <title>Publicar anuncio | {{ $siteSettings->brandName() }}</title>
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
        :publish-href="route('advertise').'#contacto'"
        publish-label="Contactar"
    />

    <main id="inicio">
        <section class="relative isolate overflow-hidden bg-[#18181B]">
            <div class="absolute inset-0 -z-10 opacity-30" style="background: radial-gradient(circle at 80% 20%, var(--site-primary), transparent 32%), radial-gradient(circle at 10% 90%, #7C3AED, transparent 28%);"></div>
            <div class="mx-auto grid max-w-7xl gap-12 px-6 py-20 lg:grid-cols-[1.08fr_.92fr] lg:items-center lg:px-8 lg:py-28">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-white">
                        <x-heroicon-o-sparkles class="size-4 text-[#F9A8D4]" aria-hidden="true" />
                        Publica con una presentación premium
                    </span>
                    <h1 class="mt-6 max-w-3xl text-4xl font-black tracking-tight text-white sm:text-6xl">
                        Tu anuncio, diseñado para <span class="text-[#F472B6]">destacar</span>
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-white/70">
                        Cuéntanos qué necesitas y preparamos una publicación clara, atractiva y optimizada para móvil, con contacto directo y presencia en las secciones adecuadas.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-button :href="'#contacto'" size="lg">Quiero publicar</x-button>
                        <x-button :href="'#tipos'" variant="light" size="lg">Ver tipos de anuncio</x-button>
                    </div>
                    <div class="mt-9 flex flex-wrap gap-x-7 gap-y-3 text-sm font-semibold text-white/75">
                        <span class="inline-flex items-center gap-2"><x-heroicon-o-check-circle class="size-5 text-[#F472B6]" /> Diseño responsive</span>
                        <span class="inline-flex items-center gap-2"><x-heroicon-o-check-circle class="size-5 text-[#F472B6]" /> Contacto directo</span>
                        <span class="inline-flex items-center gap-2"><x-heroicon-o-check-circle class="size-5 text-[#F472B6]" /> Publicación guiada</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-md">
                    <div class="absolute -inset-5 rounded-[2.5rem] bg-[#E91E63]/20 blur-3xl"></div>
                    <div class="relative rotate-2 overflow-hidden rounded-[2rem] border border-white/20 bg-white p-3 shadow-2xl">
                        <div class="relative aspect-[4/5] overflow-hidden rounded-[1.45rem] bg-gradient-to-br from-[#27272A] via-[#3F3F46] to-[#18181B]">
                            <div class="absolute inset-0 grid place-items-center">
                                <div class="text-center text-white/70">
                                    <x-heroicon-o-photo class="mx-auto size-14" aria-hidden="true" />
                                    <p class="mt-3 text-sm font-semibold">Tu mejor fotografía aquí</p>
                                </div>
                            </div>
                            <span class="absolute left-4 top-4 rounded-full bg-[#E91E63] px-3 py-1 text-xs font-black uppercase tracking-wide text-white">Destacado</span>
                        </div>
                        <div class="px-3 pb-4 pt-5">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#E91E63]">Tu ubicación · Tu categoría</p>
                            <h2 class="mt-2 text-xl font-black text-[#222222]">Un título breve que atraiga miradas</h2>
                            <p class="mt-2 text-sm leading-6 text-[#6B7280]">Información ordenada, galería, beneficios y botones de contacto.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="tipos" class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">Elige tu presencia</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-[#222222] sm:text-4xl">Tipos de anuncio</h2>
                <p class="mt-4 leading-7 text-[#6B7280]">Puedes comenzar con una publicación completa o darle mayor exposición desde el primer día.</p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-2">
                <article class="rounded-[2rem] border border-[#E5E7EB] bg-white p-8 shadow-sm">
                    <div class="flex items-start justify-between gap-5">
                        <span class="grid size-14 place-items-center rounded-2xl bg-[#F3F4F6] text-[#222222]">
                            <x-heroicon-o-rectangle-stack class="size-7" aria-hidden="true" />
                        </span>
                        <span class="rounded-full bg-[#F3F4F6] px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#6B7280]">Esencial</span>
                    </div>
                    <h3 class="mt-7 text-2xl font-black text-[#222222]">Anuncio estándar</h3>
                    <p class="mt-3 leading-7 text-[#6B7280]">Una ficha profesional con toda la información necesaria para presentar tu anuncio y recibir contactos.</p>
                    <ul class="mt-7 space-y-4 text-sm font-semibold text-[#374151]">
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Portada, galería y descripción completa</li>
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Ubicación, categoría y etiquetas</li>
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Botones directos de contacto</li>
                    </ul>
                </article>

                <article class="relative overflow-hidden rounded-[2rem] border-2 border-[#E91E63] bg-white p-8 shadow-xl shadow-[#E91E63]/10">
                    <div class="absolute right-0 top-0 rounded-bl-3xl bg-[#E91E63] px-5 py-2 text-xs font-black uppercase tracking-wide text-white">Mayor visibilidad</div>
                    <span class="grid size-14 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]">
                        <x-heroicon-o-star class="size-7" aria-hidden="true" />
                    </span>
                    <h3 class="mt-7 text-2xl font-black text-[#222222]">Anuncio destacado</h3>
                    <p class="mt-3 leading-7 text-[#6B7280]">Incluye la ficha completa y suma una presencia preferente para captar más atención dentro de la plataforma.</p>
                    <ul class="mt-7 space-y-4 text-sm font-semibold text-[#374151]">
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Presencia en la selección premium</li>
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Badge visual de destacado</li>
                        <li class="flex gap-3"><x-heroicon-o-check class="mt-0.5 size-5 shrink-0 text-[#E91E63]" /> Mayor exposición frente al anuncio estándar</li>
                    </ul>
                </article>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-[.85fr_1.15fr] lg:items-center">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#E91E63]">Beneficios</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-[#222222] sm:text-4xl">Todo lo necesario para causar una buena primera impresión</h2>
                        <p class="mt-5 leading-7 text-[#6B7280]">La información se presenta de manera visual y ordenada para que las personas encuentren lo importante sin perder tiempo.</p>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ([
                            ['icon' => 'heroicon-o-device-phone-mobile', 'title' => 'Pensado para móvil', 'text' => 'Tu publicación se adapta a cualquier pantalla.'],
                            ['icon' => 'heroicon-o-magnifying-glass', 'title' => 'Fácil de descubrir', 'text' => 'Aparece en categorías, ubicaciones, etiquetas y búsqueda.'],
                            ['icon' => 'heroicon-o-photo', 'title' => 'Contenido visual', 'text' => 'Portada y galería para mostrar tu propuesta con claridad.'],
                            ['icon' => 'heroicon-o-chat-bubble-left-right', 'title' => 'Contacto sin rodeos', 'text' => 'WhatsApp y Telegram disponibles desde el anuncio.'],
                        ] as $benefit)
                            <article class="rounded-3xl border border-[#E5E7EB] bg-[#FAFAFA] p-6">
                                <span class="grid size-11 place-items-center rounded-2xl bg-[#E91E63]/10 text-[#E91E63]">
                                    <x-dynamic-component :component="$benefit['icon']" class="size-6" aria-hidden="true" />
                                </span>
                                <h3 class="mt-5 font-black text-[#222222]">{{ $benefit['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#6B7280]">{{ $benefit['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section id="contacto" class="mx-auto max-w-5xl px-6 py-20 lg:px-8">
            <div class="overflow-hidden rounded-[2.5rem] bg-[#222222] px-6 py-12 text-center shadow-2xl sm:px-12 sm:py-16">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#F472B6]">Siguiente paso</p>
                <h2 class="mx-auto mt-3 max-w-2xl text-3xl font-black tracking-tight text-white sm:text-4xl">Hablemos de tu anuncio</h2>
                <p class="mx-auto mt-5 max-w-2xl leading-7 text-white/65">Escríbenos por tu canal preferido. Te ayudaremos a elegir el tipo de anuncio y te indicaremos la información necesaria para publicarlo.</p>

                @if ($contactButtons->isNotEmpty())
                    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        @foreach ($contactButtons as $contact)
                            <a
                                href="{{ $contact['href'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-12 items-center justify-center gap-3 rounded-full px-7 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-white/30"
                                style="background-color: {{ $contact['button_color'] }}"
                            >
                                <x-dynamic-component :component="$contact['icon']" class="size-5" aria-hidden="true" />
                                Contactar por {{ $contact['provider'] === 'whatsapp' ? 'WhatsApp' : 'Telegram' }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mx-auto mt-8 max-w-xl rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-white/70">
                        Los canales de contacto estarán disponibles muy pronto.
                    </p>
                @endif

                <div class="mx-auto mt-9 grid max-w-2xl gap-4 border-t border-white/10 pt-8 text-left sm:grid-cols-3">
                    @foreach ([
                        ['step' => '01', 'text' => 'Elige el tipo de anuncio'],
                        ['step' => '02', 'text' => 'Envía textos y fotografías'],
                        ['step' => '03', 'text' => 'Revisa y publica'],
                    ] as $item)
                        <div class="flex items-center gap-3 sm:block">
                            <span class="text-sm font-black text-[#F472B6]">{{ $item['step'] }}</span>
                            <p class="sm:mt-2 text-sm font-semibold text-white/80">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
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
