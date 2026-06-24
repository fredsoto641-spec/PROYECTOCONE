@php
    $post = $post ?? null;
    $method = $method ?? 'POST';
    $submitLabel = $submitLabel ?? 'Guardar post';
    $integrations = collect($integrations ?? []);
    $locations = collect($locations ?? []);
    $cardTemplates = collect($cardTemplates ?? []);
    $galleryValue = old('gallery_image_urls', $post ? implode("\n", $post->gallery_image_urls ?? []) : '');
    $tagsValue = old('tags', $post ? implode(', ', $post->tags ?? []) : '');
    $publishMode = old('publish_mode', $post?->published_at?->isFuture() ? 'scheduled' : 'immediate');
    $publishedAtValue = old('published_at', $post?->published_at?->format('Y-m-d\TH:i'));
    $endsAtValue = old('ends_at', $post?->ends_at?->format('Y-m-d\TH:i'));
    $siteSettings = \App\Models\SiteSetting::current();
    $defaultDialCode = \App\Models\SiteSetting::SERVER_COUNTRIES[$siteSettings->server_country]['dial_code'] ?? '51';
    $postCardColorSuggestions = $postCardColorSuggestions ?? ['byTitle' => [], 'colors' => []];
    $postCardsValue = old('post_cards');
    $normalizePostCard = fn (array $card) => [
        'title' => $card['title'] ?? '',
        'color' => $card['color'] ?? '#E91E63',
        'fill_background' => filter_var($card['fill_background'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'is_active' => filter_var($card['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'isTemplate' => filter_var($card['isTemplate'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'fields' => collect($card['fields'] ?? [])->map(fn ($field) => [
            'key' => $field['key'] ?? '',
            'value' => $field['value'] ?? '',
        ])->values()->all(),
    ];
    $postCardsInitial = $postCardsValue !== null
        ? collect($postCardsValue)->map(fn ($card) => $normalizePostCard($card))->values()->all()
        : ($post?->cards?->map(fn ($card) => [
            'title' => $card->title,
            'color' => $card->color ?? '#E91E63',
            'fill_background' => $card->fill_background ?? false,
            'is_active' => $card->is_active,
            'isTemplate' => false,
            'fields' => collect($card->fields ?? [])->map(fn ($field) => [
                'key' => $field['key'] ?? '',
                'value' => $field['value'] ?? '',
            ])->values()->all(),
        ])->values()->all() ?? []);
    $postSectionFields = [
        'basic' => ['category_id', 'title', 'subtitle', 'location'],
        'content' => ['body'],
        'media' => ['cover_image_url', 'cover_image_file', 'gallery_image_urls', 'gallery_image_files'],
        'contact' => ['whatsapp_country_code', 'whatsapp_number', 'telegram_username', 'sms_country_code', 'sms_number', 'tags'],
        'cards' => ['post_cards'],
        'publication' => ['is_active', 'publish_mode', 'published_at', 'ends_at'],
    ];
    $initialSection = collect($postSectionFields)
        ->search(fn (array $fields) => collect($fields)->contains(
            fn (string $field) => $errors->has($field) || $errors->has($field.'.*'),
        )) ?: 'basic';
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    novalidate
    class="block"
    x-data="{
        section: @js($initialSection),
        sections: ['basic', 'content', 'media', 'contact', 'cards', 'publication'],
        fieldSections: {
            category_id: 'basic',
            title: 'basic',
            subtitle: 'basic',
            location: 'basic',
            body: 'content',
            cover_image_url: 'media',
            cover_image_file: 'media',
            gallery_image_urls: 'media',
            gallery_image_files: 'media',
            whatsapp_country_code: 'contact',
            whatsapp_number: 'contact',
            telegram_username: 'contact',
            sms_country_code: 'contact',
            sms_number: 'contact',
            tags: 'contact',
            post_cards: 'cards',
            is_active: 'publication',
            publish_mode: 'publication',
            published_at: 'publication',
            ends_at: 'publication',
        },
        publishMode: @js($publishMode),
        cards: @js($postCardsInitial),
        cardTemplates: @js($cardTemplates->toArray()),
        cardColorSuggestions: @js($postCardColorSuggestions['byTitle'] ?? []),
        reusableCardColors: @js($postCardColorSuggestions['colors'] ?? []),
        emojiPickerOpen: false,
        emojiGroups: [
            {
                label: 'Favoritos',
                emojis: ['🔥', '💋', '❤️', '✨', '⭐', '📍', '📞', '✅'],
            },
            {
                label: 'Caritas',
                emojis: ['😀', '😄', '😉', '😍', '😘', '😈', '🤩', '😎', '🥰', '😋', '😇', '🤭'],
            },
            {
                label: 'Símbolos',
                emojis: ['💕', '💖', '💎', '🌹', '🎀', '👑', '⚡', '🔞', '🕒', '💯', '🎉', '📸'],
            },
        ],
        addCard() {
            this.cards.push({
                title: '',
                color: '#E91E63',
                colorTouched: false,
                fill_background: false,
                is_active: true,
                isTemplate: false,
                fields: [{ key: '', value: '' }],
            });
        },
        addCardFromTemplate(templateId) {
            const template = this.cardTemplates.find(t => t.id === parseInt(templateId));
            if (!template) return;
            
            this.cards.push({
                title: template.title,
                color: template.color,
                colorTouched: true,
                fill_background: Boolean(template.fill_background),
                is_active: true,
                isTemplate: true,
                fields: (template.fields || []).map(f => ({
                    key: f.key || '',
                    value: f.value || '',
                })),
            });
        },
        normalizeTitle(value) {
            return (value || '').trim().toLowerCase();
        },
        suggestCardColor(card) {
            const suggested = this.cardColorSuggestions[this.normalizeTitle(card.title)];

            if (suggested && ! card.colorTouched) {
                card.color = suggested;
            }
        },
        setCardColor(card, color) {
            card.color = color;
            card.colorTouched = true;
        },
        removeCard(cardIndex) {
            this.cards.splice(cardIndex, 1);
        },
        addField(card) {
            card.fields.push({ key: '', value: '' });
        },
        removeField(card, fieldIndex) {
            card.fields.splice(fieldIndex, 1);
        },
        insertIntoBody(value, selectionFallback = '') {
            const textarea = this.$refs.body;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.slice(start, end);
            const insert = value.replace('__selection__', selected || selectionFallback);

            textarea.setRangeText(insert, start, end, 'end');
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.focus();
        },
        insertEmoji(emoji) {
            this.insertIntoBody(emoji);
            this.emojiPickerOpen = false;
        },
        insertLink() {
            const url = window.prompt('URL del enlace');

            if (!url) return;

            this.insertIntoBody(`[__selection__](${url})`, 'Texto del enlace');
        },
        insertImage() {
            const url = window.prompt('URL de la imagen');

            if (!url) return;

            this.insertIntoBody(`\n![Imagen](${url})\n`);
        },
        goTo(section) {
            if (! this.sections.includes(section)) return;
            this.section = section;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        nextSection() {
            const index = this.sections.indexOf(this.section);
            if (index < this.sections.length - 1) this.goTo(this.sections[index + 1]);
        },
        previousSection() {
            const index = this.sections.indexOf(this.section);
            if (index > 0) this.goTo(this.sections[index - 1]);
        },
        validateBeforeSubmit(event) {
            const invalid = [...this.$el.elements].find(element => ! element.checkValidity());
            if (! invalid) return;

            event.preventDefault();
            const baseName = (invalid.name || '').split('[')[0].replace('[]', '');
            this.goTo(this.fieldSections[baseName] || 'basic');
            setTimeout(() => {
                invalid.reportValidity();
                invalid.focus();
            }, 250);
        },
    }"
    x-init="
        const hashSection = window.location.hash.replace('#', '');
        if (sections.includes(hashSection)) section = hashSection;
        $watch('section', value => history.replaceState(null, '', `#${value}`));
    "
    x-on:submit="validateBeforeSubmit($event)"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-4">
        <aside class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:sticky lg:top-6">
            <div class="border-b border-gray-200 p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">
                    {{ $post ? 'Editar anuncio' : 'Nuevo anuncio' }}
                </p>
                <h3 class="mt-1 font-semibold text-gray-900">Completa tu publicación</h3>
                <p class="mt-1 text-sm text-gray-500">Avanza sección por sección. Puedes volver cuando quieras.</p>
            </div>

            <nav class="grid gap-1 p-3 sm:grid-cols-2 lg:grid-cols-1" aria-label="Secciones del post">
                @foreach ([
                    'basic' => ['1', 'Datos básicos', 'Categoría, título y ubicación'],
                    'content' => ['2', 'Contenido', 'Descripción principal'],
                    'media' => ['3', 'Imágenes', 'Portada y galería'],
                    'contact' => ['4', 'Contacto', 'Canales y etiquetas'],
                    'cards' => ['5', 'Cards', 'Información adicional'],
                    'publication' => ['6', 'Publicación', 'Visibilidad y fechas'],
                ] as $sectionKey => [$step, $sectionLabel, $sectionDescription])
                    <button
                        type="button"
                        x-on:click="goTo('{{ $sectionKey }}')"
                        class="flex gap-3 rounded-lg px-3 py-3 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                        x-bind:class="section === '{{ $sectionKey }}'
                            ? 'bg-gray-900 text-white shadow-sm'
                            : 'text-gray-700 hover:bg-gray-100'"
                        x-bind:aria-current="section === '{{ $sectionKey }}' ? 'step' : null"
                    >
                        <span
                            class="grid size-7 shrink-0 place-items-center rounded-full text-xs font-bold"
                            x-bind:class="section === '{{ $sectionKey }}' ? 'bg-white text-gray-900' : 'bg-gray-200 text-gray-700'"
                        >{{ $step }}</span>
                        <span>
                            <span class="block text-sm font-semibold">{{ $sectionLabel }}</span>
                            <span
                                class="mt-0.5 block text-xs"
                                x-bind:class="section === '{{ $sectionKey }}' ? 'text-gray-300' : 'text-gray-500'"
                            >{{ $sectionDescription }}</span>
                        </span>
                    </button>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 lg:col-span-3">
            <section x-show="section === 'basic'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 1 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Datos básicos</h3>
                    <p class="mt-1 text-sm text-gray-600">Define cómo se identificará y dónde aparecerá el anuncio.</p>
                </div>

    <div>
        <x-input-label for="category_id" value="Categoría" />
        <select
            id="category_id"
            name="category_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">Selecciona una categoría</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $post?->category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="title" value="Título" />
            <x-text-input
                id="title"
                name="title"
                type="text"
                class="mt-1 block w-full"
                :value="old('title', $post?->title)"
                required
                autofocus
            />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div>
            <x-input-label for="subtitle" value="Subtítulo" />
            <x-text-input
                id="subtitle"
                name="subtitle"
                type="text"
                class="mt-1 block w-full"
                :value="old('subtitle', $post?->subtitle)"
            />
            <x-input-error class="mt-2" :messages="$errors->get('subtitle')" />
        </div>
    </div>

    <div>
        <x-input-label for="location" value="Ubicación" />
        <select
            id="location"
            name="location"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >
            <option value="">Selecciona una ubicación</option>
            @foreach ($locations->groupBy('department') as $department => $departmentLocations)
                <optgroup label="{{ $department }}">
                    @foreach ($departmentLocations as $location)
                        <option value="{{ $location->name }}" @selected(old('location', $post?->location) === $location->name)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('location')" />
        @if ($locations->isEmpty())
            <p class="mt-2 text-sm text-red-600">
                Primero agrega una ubicación desde Configuración.
            </p>
        @endif
    </div>

                <div class="flex justify-end border-t border-gray-200 pt-6">
                    <button type="button" class="admin-button-primary" x-on:click="nextSection">
                        Siguiente: Contenido
                    </button>
                </div>
            </section>

            <section x-show="section === 'content'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 2 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Contenido del anuncio</h3>
                    <p class="mt-1 text-sm text-gray-600">Escribe una descripción clara y usa las herramientas para enriquecerla.</p>
                </div>

    <div>
        <x-input-label for="body" value="Texto" />
        <div class="mt-2 flex flex-wrap items-center gap-2 rounded-t-md border border-b-0 border-gray-300 bg-gray-50 p-2">
            @foreach (['🔥', '💋', '❤️', '✨', '⭐', '📍', '📞', '✅'] as $emoji)
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-md text-lg transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    title="Insertar {{ $emoji }}"
                    aria-label="Insertar {{ $emoji }}"
                    x-on:click="insertEmoji('{{ $emoji }}')"
                >
                    {{ $emoji }}
                </button>
            @endforeach

            <div class="relative" x-on:click.outside="emojiPickerOpen = false">
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 bg-white text-lg transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    title="Elegir emoji"
                    aria-label="Elegir emoji"
                    x-on:click="emojiPickerOpen = ! emojiPickerOpen"
                    x-bind:aria-expanded="emojiPickerOpen.toString()"
                >
                    ☺
                </button>

                <div
                    x-show="emojiPickerOpen"
                    x-cloak
                    x-transition
                    class="absolute left-0 z-20 mt-2 w-72 rounded-md border border-gray-200 bg-white p-3 shadow-lg"
                >
                    <template x-for="group in emojiGroups" :key="group.label">
                        <div class="mb-3 last:mb-0">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="group.label"></p>
                            <div class="grid grid-cols-8 gap-1">
                                <template x-for="emoji in group.emojis" :key="emoji">
                                    <button
                                        type="button"
                                        class="inline-flex size-8 items-center justify-center rounded-md text-lg transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1"
                                        x-on:click="insertEmoji(emoji)"
                                        x-bind:title="`Insertar ${emoji}`"
                                        x-bind:aria-label="`Insertar ${emoji}`"
                                    >
                                        <span x-text="emoji"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <span class="mx-1 h-6 w-px bg-gray-300"></span>

            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Insertar URL"
                aria-label="Insertar URL"
                x-on:click="insertLink()"
            >
                <x-heroicon-o-link class="h-5 w-[18px]" aria-hidden="true" />
            </button>

            <button
                type="button"
                class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                title="Insertar imagen por URL"
                aria-label="Insertar imagen por URL"
                x-on:click="insertImage()"
            >
                <x-heroicon-o-photo class="h-5 w-[18px]" aria-hidden="true" />
            </button>
        </div>
        <textarea
            id="body"
            name="body"
            rows="7"
            x-ref="body"
            class="block w-full rounded-b-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            required
        >{{ old('body', $post?->body) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('body')" />
    </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
                    <button type="button" class="admin-button-cancel" x-on:click="previousSection">Anterior</button>
                    <button type="button" class="admin-button-primary" x-on:click="nextSection">
                        Siguiente: Imágenes
                    </button>
                </div>
            </section>

            <section x-show="section === 'media'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 3 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Imágenes</h3>
                    <p class="mt-1 text-sm text-gray-600">Añade una portada atractiva y completa la galería por URL o archivo.</p>
                </div>

    <div class="rounded-lg border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-900">Imagen de portada</h3>
        <p class="mt-1 text-sm text-gray-500">La URL sigue disponible. Si también subes un archivo, se usará el archivo.</p>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="cover_image_url" value="URL de portada" />
                <x-text-input
                    id="cover_image_url"
                    name="cover_image_url"
                    type="url"
                    class="mt-1 block w-full"
                    :value="old('cover_image_url', $post?->cover_image_url)"
                    placeholder="https://..."
                />
                <x-input-error class="mt-2" :messages="$errors->get('cover_image_url')" />
            </div>

            <div>
                <x-input-label for="cover_image_file" value="Subir portada" />
                <input
                    id="cover_image_file"
                    name="cover_image_file"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                >
                <p class="mt-2 text-xs text-gray-500">JPEG, PNG o WebP. Máximo actual del servidor: {{ app(\App\Support\SecureImageUploader::class)->effectiveMaxMegabytes() }} MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('cover_image_file')" />
            </div>
        </div>

        @if ($post?->cover_image_url)
            <img src="{{ $post->cover_image_url }}" alt="Portada actual de {{ $post->title }}" class="mt-4 h-32 w-52 rounded-lg object-cover">
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-900">Galería de imágenes</h3>
        <p class="mt-1 text-sm text-gray-500">Las URLs se escriben una por línea. Los archivos seleccionados se añadirán al final de esa lista.</p>

        <div class="mt-4 grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="gallery_image_urls" value="URLs de galería" />
                <textarea
                    id="gallery_image_urls"
                    name="gallery_image_urls"
                    rows="5"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="https://imagen-1.jpg&#10;https://imagen-2.jpg"
                >{{ $galleryValue }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('gallery_image_urls')" />
            </div>

            <div>
                <x-input-label for="gallery_image_files" value="Subir varias imágenes" />
                <input
                    id="gallery_image_files"
                    name="gallery_image_files[]"
                    type="file"
                    multiple
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                >
                <p class="mt-2 text-xs text-gray-500">Hasta 12 archivos JPEG, PNG o WebP; máximo {{ app(\App\Support\SecureImageUploader::class)->effectiveMaxMegabytes() }} MB por archivo.</p>
                <x-input-error class="mt-2" :messages="$errors->get('gallery_image_files')" />
                <x-input-error class="mt-2" :messages="$errors->get('gallery_image_files.*')" />
            </div>
        </div>
    </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
                    <button type="button" class="admin-button-cancel" x-on:click="previousSection">Anterior</button>
                    <button type="button" class="admin-button-primary" x-on:click="nextSection">
                        Siguiente: Contacto
                    </button>
                </div>
            </section>

            <section x-show="section === 'contact'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 4 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Contacto y etiquetas</h3>
                    <p class="mt-1 text-sm text-gray-600">Configura los medios disponibles para responder y facilitar el descubrimiento.</p>
                </div>

    <div class="rounded-md border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-900">Botones de contacto</h3>

        @if ($integrations->isEmpty())
            <p class="mt-3 text-sm text-gray-500">
                Activa una integración para habilitar botones de contacto.
            </p>
        @else
            <div class="mt-4 grid gap-6">
                @if ($integrations->has('whatsapp'))
                    <div class="grid gap-6 sm:grid-cols-[120px_1fr]">
                        <div>
                            <x-input-label for="whatsapp_country_code" value="País WhatsApp" />
                            <x-text-input
                                id="whatsapp_country_code"
                                name="whatsapp_country_code"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('whatsapp_country_code', $post?->whatsapp_country_code ?: $defaultDialCode)"
                                placeholder="51"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_country_code')" />
                        </div>

                        <div>
                            <x-input-label for="whatsapp_number" value="Número WhatsApp" />
                            <x-text-input
                                id="whatsapp_number"
                                name="whatsapp_number"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('whatsapp_number', $post?->whatsapp_number)"
                                placeholder="999999999"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                        </div>
                    </div>
                @endif

                @if ($integrations->has('telegram'))
                    <div>
                        <x-input-label for="telegram_username" value="Usuario Telegram" />
                        <x-text-input
                            id="telegram_username"
                            name="telegram_username"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('telegram_username', $post?->telegram_username)"
                            placeholder="@usuario"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('telegram_username')" />
                    </div>
                @endif

                @if ($integrations->has('sms'))
                    <div class="grid gap-6 sm:grid-cols-[120px_1fr]">
                        <div>
                            <x-input-label for="sms_country_code" value="País SMS" />
                            <x-text-input
                                id="sms_country_code"
                                name="sms_country_code"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('sms_country_code', $post?->sms_country_code ?: $defaultDialCode)"
                                placeholder="51"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('sms_country_code')" />
                        </div>

                        <div>
                            <x-input-label for="sms_number" value="Número SMS" />
                            <x-text-input
                                id="sms_number"
                                name="sms_number"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('sms_number', $post?->sms_number)"
                                placeholder="999999999"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('sms_number')" />
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div>
        <x-input-label for="tags" value="Tags" />
        <x-text-input
            id="tags"
            name="tags"
            type="text"
            class="mt-1 block w-full"
            :value="$tagsValue"
            placeholder="premium, verificado, lima"
        />
        <x-input-error class="mt-2" :messages="$errors->get('tags')" />
    </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
                    <button type="button" class="admin-button-cancel" x-on:click="previousSection">Anterior</button>
                    <button type="button" class="admin-button-primary" x-on:click="nextSection">
                        Siguiente: Cards
                    </button>
                </div>
            </section>

            <section x-show="section === 'cards'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 5 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Cards informativas</h3>
                    <p class="mt-1 text-sm text-gray-600">Organiza datos adicionales como atención, perfil o características.</p>
                </div>

    <section class="rounded-md border border-gray-200 p-4">
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-sm font-semibold text-gray-900">Cards informativas</h3>
            <div class="flex gap-2">
                <template x-if="cardTemplates.length > 0">
                    <select
                        class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        x-on:change="addCardFromTemplate($event.target.value); $event.target.value = ''"
                    >
                        <option value="">Usar plantilla...</option>
                        <template x-for="template in cardTemplates" :key="template.id">
                            <option x-bind:value="template.id" x-text="template.title"></option>
                        </template>
                    </select>
                </template>
                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    title="Agregar card personalizada"
                    aria-label="Agregar card personalizada"
                    x-on:click="addCard()"
                >
                    <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                </button>
            </div>
        </div>

        <template x-if="cards.length === 0">
            <p class="mt-3 text-sm text-gray-500">
                Agrega cards como Perfil, Atención o cualquier dato extra del post.
                <template x-if="cardTemplates.length > 0">
                    <span>Usa una plantilla o crea una personalizada.</span>
                </template>
            </p>
        </template>

        <div class="mt-4 space-y-4">
            <template x-for="(card, cardIndex) in cards" :key="cardIndex">
                <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="w-full">
                            <div class="flex items-center gap-2">
                                <x-input-label value="Título de card" />
                                <template x-if="card.isTemplate">
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
                                        Plantilla
                                    </span>
                                </template>
                            </div>
                            <x-text-input
                                type="text"
                                class="mt-1 block w-full"
                                x-model="card.title"
                                x-on:input="suggestCardColor(card)"
                                x-bind:name="`post_cards[${cardIndex}][title]`"
                                x-bind:readonly="card.isTemplate"
                                placeholder="Perfil"
                            />
                        </div>

                        <button
                            type="button"
                            class="mt-6 inline-flex size-9 shrink-0 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                            title="Eliminar card"
                            aria-label="Eliminar card"
                            x-on:click="removeCard(cardIndex)"
                        >
                            <x-heroicon-o-trash class="h-5 w-[18px]" aria-hidden="true" />
                        </button>
                    </div>

                    <label class="mt-3 inline-flex items-center gap-3">
                        <input type="hidden" value="0" x-bind:name="`post_cards[${cardIndex}][is_active]`">
                        <input
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            x-model="card.is_active"
                            x-bind:name="`post_cards[${cardIndex}][is_active]`"
                        >
                        <span class="text-sm font-medium text-gray-700">Visible</span>
                    </label>

                    <div class="mt-4">
                        <x-input-label value="Color de card" />
                        <div class="mt-1 flex gap-2">
                            <input
                                type="color"
                                class="h-10 w-12 rounded-md border border-gray-300 bg-white p-1"
                                x-model="card.color"
                                x-on:input="setCardColor(card, $event.target.value)"
                            >
                            <x-text-input
                                type="text"
                                class="block w-full"
                                x-model="card.color"
                                x-on:input="card.colorTouched = true"
                                x-bind:name="`post_cards[${cardIndex}][color]`"
                                placeholder="#E91E63"
                            />
                        </div>

                        <template x-if="reusableCardColors.length > 0">
                            <div class="mt-3 flex flex-wrap gap-2">
                                <template x-for="color in reusableCardColors" :key="color">
                                    <button
                                        type="button"
                                        class="size-7 rounded-full border border-gray-300 shadow-sm transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                        x-bind:style="`background-color: ${color}`"
                                        x-bind:title="color"
                                        x-on:click="setCardColor(card, color)"
                                    >
                                        <span class="sr-only" x-text="`Usar ${color}`"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <label class="mt-3 inline-flex items-center gap-3">
                        <input type="hidden" value="0" x-bind:name="`post_cards[${cardIndex}][fill_background]`">
                        <input
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            x-model="card.fill_background"
                            x-bind:name="`post_cards[${cardIndex}][fill_background]`"
                        >
                        <span class="text-sm font-medium text-gray-700">Usar color como fondo</span>
                    </label>

                    <div class="mt-4 space-y-3">
                        <template x-for="(field, fieldIndex) in card.fields" :key="fieldIndex">
                            <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                                <x-text-input
                                    type="text"
                                    class="block w-full"
                                    x-model="field.key"
                                    x-bind:name="`post_cards[${cardIndex}][fields][${fieldIndex}][key]`"
                                    x-bind:readonly="card.isTemplate"
                                    x-bind:class="card.isTemplate ? 'bg-gray-100' : ''"
                                    placeholder="Clave"
                                />
                                <x-text-input
                                    type="text"
                                    class="block w-full"
                                    x-model="field.value"
                                    x-bind:name="`post_cards[${cardIndex}][fields][${fieldIndex}][value]`"
                                    placeholder="Valor"
                                />
                                <template x-if="!card.isTemplate">
                                    <button
                                        type="button"
                                        class="inline-flex size-10 items-center justify-center rounded-md border border-gray-300 text-gray-900 transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                        title="Eliminar dato"
                                        aria-label="Eliminar dato"
                                        x-on:click="removeField(card, fieldIndex)"
                                    >
                                        <x-heroicon-o-x-mark class="h-5 w-[18px]" aria-hidden="true" />
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>

                    <template x-if="!card.isTemplate">
                        <button
                            type="button"
                            class="mt-4 inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-900 transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                            x-on:click="addField(card)"
                        >
                            <x-heroicon-o-plus class="h-5 w-[18px]" aria-hidden="true" />
                            Agregar dato
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('post_cards')" />
    </section>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-6">
                    <button type="button" class="admin-button-cancel" x-on:click="previousSection">Anterior</button>
                    <button type="button" class="admin-button-primary" x-on:click="nextSection">
                        Siguiente: Publicación
                    </button>
                </div>
            </section>

            <section x-show="section === 'publication'" x-cloak class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div>
                    <p class="text-sm font-semibold text-indigo-600">Paso 6 de 6</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">Publicación</h3>
                    <p class="mt-1 text-sm text-gray-600">Revisa la visibilidad, el momento de publicación y la fecha de finalización.</p>
                </div>

    <div class="flex items-end">
        <label for="is_active" class="inline-flex items-center gap-3 rounded-md border border-gray-200 px-4 py-3">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                @checked(old('is_active', $post?->is_active ?? true))
            >
            <span class="text-sm font-medium text-gray-700">Publicado</span>
        </label>
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="publish_mode" value="Tipo de publicación" />
            <select
                id="publish_mode"
                name="publish_mode"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                x-model="publishMode"
                required
            >
                <option value="immediate">Inmediata</option>
                <option value="scheduled">Programada</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('publish_mode')" />
        </div>

        <div x-show="publishMode === 'scheduled'" x-cloak>
            <x-input-label for="published_at" value="Fecha y hora de publicación" />
            <x-text-input
                id="published_at"
                name="published_at"
                type="datetime-local"
                class="mt-1 block w-full"
                :value="$publishedAtValue"
                x-bind:required="publishMode === 'scheduled'"
            />
            <x-input-error class="mt-2" :messages="$errors->get('published_at')" />
        </div>

        <div>
            <x-input-label for="ends_at" value="Fecha y hora de finalización" />
            <x-text-input
                id="ends_at"
                name="ends_at"
                type="datetime-local"
                class="mt-1 block w-full"
                :value="$endsAtValue"
            />
            <x-input-error class="mt-2" :messages="$errors->get('ends_at')" />
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
        <button type="button" class="admin-button-cancel" x-on:click="previousSection">Anterior</button>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('posts.index') }}" class="admin-button-cancel">
                Cancelar
            </a>
            <x-primary-button>
                {{ $submitLabel }}
            </x-primary-button>
        </div>
    </div>
            </section>
        </div>
    </div>
</form>
