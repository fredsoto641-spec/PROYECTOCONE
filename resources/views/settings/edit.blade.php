<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Configuración del sitio
            </h2>
        </div>
    </x-slot>

    @php
        $locationErrorFields = ['name', 'department', 'sort_order'];
        $generalErrorFields = [
            'brand_primary_text',
            'brand_accent_text',
            'contact_country',
            'contact_phone',
            'contact_telegram_username',
            'site_title',
            'site_subtitle',
            'cover_image_url',
            'cover_image_file',
        ];
        $colorErrorFields = [
            'primary_color',
            'primary_hover_color',
            'text_color',
            'muted_color',
            'background_color',
            'admin_ink_color',
            'admin_ink_hover_color',
            'admin_muted_color',
            'admin_danger_color',
            'admin_focus_color',
        ];
        $serverErrorFields = ['server_country', 'server_country_code', 'server_utc_offset'];
        $footerErrorFields = ['footer_columns'];
        $ageGateErrorFields = [
            'age_gate_storage_key',
            'age_gate_badge',
            'age_gate_title',
            'age_gate_description',
            'age_gate_confirm_label',
            'age_gate_exit_label',
            'age_gate_exit_href',
            'age_gate_legal_text',
        ];
        $footerColumns = json_decode((string) old('footer_columns', ''), true);
        $footerColumns = is_array($footerColumns) ? $footerColumns : $settings->footerColumnsConfig();
        $initialSection = request()->has('locations_page') || collect($locationErrorFields)->contains(fn ($field) => $errors->has($field))
            ? 'locations'
            : (collect($colorErrorFields)->contains(fn ($field) => $errors->has($field))
                ? 'colors'
                : (collect($serverErrorFields)->contains(fn ($field) => $errors->has($field))
                    ? 'server'
                    : (collect($footerErrorFields)->contains(fn ($field) => $errors->has($field))
                        ? 'footer'
                        : (collect($ageGateErrorFields)->contains(fn ($field) => $errors->has($field))
                            ? 'age'
                            : (collect($generalErrorFields)->contains(fn ($field) => $errors->has($field)) ? 'cover' : 'cover')))));
    @endphp

    <div
        class="py-12"
        x-data="{ section: @js($initialSection) }"
        x-init="
            const hashSection = window.location.hash.replace('#', '');
            if (['cover', 'colors', 'server', 'footer', 'age', 'locations'].includes(hashSection)) {
                section = hashSection;
            }
            $watch('section', value => history.replaceState(null, '', `#${value}`));
        "
    >
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4 text-sm font-medium text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid items-start gap-6 lg:grid-cols-4">
                <aside class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:sticky lg:top-6">
                    <div class="border-b border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-900">Secciones</h3>
                        <p class="mt-1 text-sm text-gray-500">Elige qué deseas configurar.</p>
                    </div>

                    <nav class="grid gap-1 p-3 sm:grid-cols-2 lg:grid-cols-1" aria-label="Secciones de configuración">
                        @foreach ([
                            'cover' => ['Datos generales', 'Marca, contacto y portada'],
                            'colors' => ['Colores', 'Paleta pública y administrativa'],
                            'server' => ['Servidor', 'País, código y zona horaria'],
                            'footer' => ['Footer', 'Columnas y enlaces públicos'],
                            'age' => ['Confirmación de edad', 'Contenido y botones del modal'],
                            'locations' => ['Ubicaciones', 'Distritos disponibles en posts'],
                        ] as $sectionKey => [$sectionLabel, $sectionDescription])
                            <button
                                type="button"
                                x-on:click="section = '{{ $sectionKey }}'"
                                class="rounded-lg px-4 py-3 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                x-bind:class="section === '{{ $sectionKey }}'
                                    ? 'bg-gray-900 text-white shadow-sm'
                                    : 'text-gray-700 hover:bg-gray-100'"
                                x-bind:aria-current="section === '{{ $sectionKey }}' ? 'page' : null"
                            >
                                <span class="block text-sm font-semibold">{{ $sectionLabel }}</span>
                                <span
                                    class="mt-1 block text-xs"
                                    x-bind:class="section === '{{ $sectionKey }}' ? 'text-gray-300' : 'text-gray-500'"
                                >
                                    {{ $sectionDescription }}
                                </span>
                            </button>
                        @endforeach
                    </nav>
                </aside>

                <div class="min-w-0 lg:col-span-3">
            <div
                x-show="section !== 'locations'"
                x-cloak
                class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
            >
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-8 p-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="settings_section" x-model="section">

                    <section x-show="section === 'cover'" x-cloak class="space-y-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Datos generales</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Configura la marca, los canales comerciales y la portada del sitio.
                            </p>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <x-input-label for="brand_primary_text" value="Texto principal del logo" />
                                <x-text-input
                                    id="brand_primary_text"
                                    name="brand_primary_text"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('brand_primary_text', $settings->brand_primary_text)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('brand_primary_text')" />
                            </div>

                            <div>
                                <x-input-label for="brand_accent_text" value="Texto destacado del logo" />
                                <x-text-input
                                    id="brand_accent_text"
                                    name="brand_accent_text"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('brand_accent_text', $settings->brand_accent_text)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('brand_accent_text')" />
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <h4 class="text-sm font-semibold text-gray-900">Contacto para publicar anuncios</h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Estos datos construyen los botones de WhatsApp y Telegram de la vista “Publicar anuncio”.
                            </p>

                            <div
                                class="mt-4 grid gap-6 sm:grid-cols-3"
                                x-data="{
                                    countries: @js($serverCountries),
                                    country: @js(old('contact_country', $settings->contact_country ?: $settings->server_country)),
                                }"
                            >
                                <div>
                                    <x-input-label for="contact_country" value="País" />
                                    <select
                                        id="contact_country"
                                        name="contact_country"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        x-model="country"
                                        required
                                    >
                                        @foreach ($serverCountries as $country => $metadata)
                                            <option value="{{ $country }}">
                                                {{ $country }} (+{{ $metadata['dial_code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('contact_country')" />
                                </div>

                                <div>
                                    <x-input-label for="contact_phone" value="Número de teléfono" />
                                    <x-text-input
                                        id="contact_phone"
                                        name="contact_phone"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="old('contact_phone', $settings->contact_phone)"
                                        placeholder="999999999"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
                                </div>

                                <div>
                                    <x-input-label for="contact_telegram_username" value="Usuario de Telegram" />
                                    <x-text-input
                                        id="contact_telegram_username"
                                        name="contact_telegram_username"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="old('contact_telegram_username', $settings->contact_telegram_username)"
                                        placeholder="@usuario"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('contact_telegram_username')" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="site_title" value="Título" />
                            <x-text-input
                                id="site_title"
                                name="site_title"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('site_title', $settings->site_title)"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('site_title')" />
                        </div>

                        <div>
                            <x-input-label for="site_subtitle" value="Subtítulo" />
                            <textarea
                                id="site_subtitle"
                                name="site_subtitle"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >{{ old('site_subtitle', $settings->site_subtitle) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('site_subtitle')" />
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <h4 class="text-sm font-semibold text-gray-900">Banner de portada</h4>
                            <p class="mt-1 text-sm text-gray-500">Puedes usar una URL o subir una imagen. El archivo tendrá prioridad cuando se envíen ambos.</p>

                            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="cover_image_url" value="URL del banner" />
                                    <x-text-input
                                        id="cover_image_url"
                                        name="cover_image_url"
                                        type="url"
                                        class="mt-1 block w-full"
                                        :value="old('cover_image_url', $settings->cover_image_url)"
                                        placeholder="https://..."
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('cover_image_url')" />
                                </div>

                                <div>
                                    <x-input-label for="cover_image_file" value="Subir banner" />
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

                            @if ($settings->cover_image_url)
                                <img src="{{ $settings->cover_image_url }}" alt="Banner actual" class="mt-4 h-36 w-full rounded-lg object-cover">
                            @endif
                        </div>
                    </section>

                    <section x-show="section === 'colors'" x-cloak class="space-y-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Colores globales</h3>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            @foreach ([
                                'primary_color' => 'Primario',
                                'primary_hover_color' => 'Primario hover',
                                'text_color' => 'Texto',
                                'muted_color' => 'Texto secundario',
                                'background_color' => 'Fondo',
                                'admin_ink_color' => 'Admin principal',
                                'admin_ink_hover_color' => 'Admin hover',
                                'admin_muted_color' => 'Admin secundario',
                                'admin_danger_color' => 'Admin peligro',
                                'admin_focus_color' => 'Admin foco',
                            ] as $field => $label)
                                <div>
                                    <x-input-label :for="$field" :value="$label" />
                                    <div class="mt-1 flex gap-2">
                                        <input
                                            id="{{ $field }}"
                                            type="color"
                                            class="h-10 w-12 rounded-md border border-gray-300 bg-white p-1"
                                            value="{{ old($field, $settings->{$field}) }}"
                                            onchange="this.nextElementSibling.value = this.value"
                                        >
                                        <x-text-input
                                            name="{{ $field }}"
                                            type="text"
                                            class="block w-full"
                                            :value="old($field, $settings->{$field})"
                                            required
                                        />
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get($field)" />
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section
                        x-show="section === 'server'"
                        x-cloak
                        class="space-y-6"
                        x-data="{
                            countries: @js($serverCountries),
                            country: @js(old('server_country', $settings->server_country)),
                            code: @js(old('server_country_code', $settings->server_country_code)),
                            utc: @js(old('server_utc_offset', $settings->server_utc_offset)),
                            syncCountry() {
                                const selected = this.countries[this.country];

                                if (! selected) {
                                    return;
                                }

                                this.code = selected.code;
                                this.utc = selected.utc;
                            },
                        }"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Servidor</h3>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-3">
                            <div>
                                <x-input-label for="server_country" value="País" />
                                <select
                                    id="server_country"
                                    name="server_country"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    x-model="country"
                                    x-on:change="syncCountry"
                                    required
                                >
                                    <option value="">Selecciona un país</option>
                                    @foreach ($serverCountries as $country => $metadata)
                                        <option value="{{ $country }}" @selected(old('server_country', $settings->server_country) === $country)>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('server_country')" />
                            </div>

                            <div>
                                <x-input-label for="server_country_code" value="Código de país" />
                                <x-text-input
                                    id="server_country_code"
                                    name="server_country_code"
                                    type="text"
                                    class="mt-1 block w-full uppercase"
                                    x-model="code"
                                    placeholder="PE"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('server_country_code')" />
                            </div>

                            <div>
                                <x-input-label for="server_utc_offset" value="UTC" />
                                <x-text-input
                                    id="server_utc_offset"
                                    name="server_utc_offset"
                                    type="text"
                                    class="mt-1 block w-full"
                                    x-model="utc"
                                    placeholder="-05:00"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('server_utc_offset')" />
                            </div>
                        </div>
                    </section>

                    <section
                        x-show="section === 'footer'"
                        x-cloak
                        class="space-y-6"
                        x-data="{
                            columns: @js($footerColumns),
                            addColumn() {
                                if (this.columns.length >= 8) return;
                                this.columns.push({
                                    title: 'Nueva columna',
                                    items: [{ label: 'Nuevo enlace', href: '/#' }],
                                });
                            },
                            removeColumn(columnIndex) {
                                if (this.columns.length <= 1) return;
                                this.columns.splice(columnIndex, 1);
                            },
                            addItem(columnIndex) {
                                if (this.columns[columnIndex].items.length >= 12) return;
                                this.columns[columnIndex].items.push({ label: 'Nuevo enlace', href: '/#' });
                            },
                            removeItem(columnIndex, itemIndex) {
                                if (this.columns[columnIndex].items.length <= 1) return;
                                this.columns[columnIndex].items.splice(itemIndex, 1);
                            },
                        }"
                    >
                        <input type="hidden" name="footer_columns" x-bind:value="JSON.stringify(columns)">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Columnas del footer</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-600">
                                    Agrega hasta 8 columnas. Cada elemento contiene el texto visible y su enlace.
                                    Puedes usar rutas internas como <code class="rounded bg-gray-100 px-1 py-0.5">/#categorias</code>
                                    o URLs completas.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="admin-button-primary shrink-0"
                                x-on:click="addColumn"
                                x-bind:disabled="columns.length >= 8"
                            >
                                Agregar columna
                            </button>
                        </div>

                        <x-input-error class="mt-2" :messages="$errors->get('footer_columns')" />

                        <div class="space-y-5">
                            <template x-for="(column, columnIndex) in columns" x-bind:key="columnIndex">
                                <article class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                        <div class="min-w-0 flex-1">
                                            <label class="block text-sm font-medium text-gray-700">Título de columna</label>
                                            <input
                                                type="text"
                                                maxlength="80"
                                                required
                                                x-model="column.title"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <button
                                            type="button"
                                            class="admin-button-danger-outline"
                                            x-on:click="removeColumn(columnIndex)"
                                            x-bind:disabled="columns.length <= 1"
                                        >
                                            Eliminar columna
                                        </button>
                                    </div>

                                    <div class="mt-5 space-y-3">
                                        <template x-for="(item, itemIndex) in column.items" x-bind:key="itemIndex">
                                            <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_auto] md:items-end">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Texto</label>
                                                    <input
                                                        type="text"
                                                        maxlength="120"
                                                        required
                                                        x-model="item.label"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Enlace</label>
                                                    <input
                                                        type="text"
                                                        maxlength="2048"
                                                        required
                                                        x-model="item.href"
                                                        placeholder="/#seccion o https://..."
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                </div>
                                                <button
                                                    type="button"
                                                    class="admin-button-danger-outline"
                                                    x-on:click="removeItem(columnIndex, itemIndex)"
                                                    x-bind:disabled="column.items.length <= 1"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <button
                                        type="button"
                                        class="mt-4 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        x-on:click="addItem(columnIndex)"
                                        x-bind:disabled="column.items.length >= 12"
                                    >
                                        Agregar enlace
                                    </button>
                                </article>
                            </template>
                        </div>
                    </section>

                    <section x-show="section === 'age'" x-cloak class="space-y-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Modal de confirmación de edad</h3>
                        </div>

                        <label class="flex items-center gap-3 rounded-md border border-gray-200 p-4">
                            <input
                                type="checkbox"
                                name="age_gate_is_enabled"
                                value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                @checked(old('age_gate_is_enabled', $ageGateSettings->is_enabled))
                            >
                            <span class="text-sm font-medium text-gray-700">Mostrar modal de confirmación</span>
                        </label>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <x-input-label for="age_gate_storage_key" value="Storage key" />
                                <x-text-input
                                    id="age_gate_storage_key"
                                    name="age_gate_storage_key"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('age_gate_storage_key', $ageGateSettings->storage_key)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('age_gate_storage_key')" />
                            </div>

                            <div>
                                <x-input-label for="age_gate_badge" value="Badge" />
                                <x-text-input
                                    id="age_gate_badge"
                                    name="age_gate_badge"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('age_gate_badge', $ageGateSettings->badge)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('age_gate_badge')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="age_gate_title" value="Título" />
                            <x-text-input
                                id="age_gate_title"
                                name="age_gate_title"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('age_gate_title', $ageGateSettings->title)"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('age_gate_title')" />
                        </div>

                        <div>
                            <x-input-label for="age_gate_description" value="Descripción" />
                            <textarea
                                id="age_gate_description"
                                name="age_gate_description"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >{{ old('age_gate_description', $ageGateSettings->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('age_gate_description')" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <x-input-label for="age_gate_confirm_label" value="Texto botón confirmar" />
                                <x-text-input
                                    id="age_gate_confirm_label"
                                    name="age_gate_confirm_label"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('age_gate_confirm_label', $ageGateSettings->confirm_label)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('age_gate_confirm_label')" />
                            </div>

                            <div>
                                <x-input-label for="age_gate_exit_label" value="Texto botón salir" />
                                <x-text-input
                                    id="age_gate_exit_label"
                                    name="age_gate_exit_label"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('age_gate_exit_label', $ageGateSettings->exit_label)"
                                    required
                                />
                                <x-input-error class="mt-2" :messages="$errors->get('age_gate_exit_label')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="age_gate_exit_href" value="URL de salida" />
                            <x-text-input
                                id="age_gate_exit_href"
                                name="age_gate_exit_href"
                                type="url"
                                class="mt-1 block w-full"
                                :value="old('age_gate_exit_href', $ageGateSettings->exit_href)"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('age_gate_exit_href')" />
                        </div>

                        <div>
                            <x-input-label for="age_gate_legal_text" value="Texto legal" />
                            <textarea
                                id="age_gate_legal_text"
                                name="age_gate_legal_text"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >{{ old('age_gate_legal_text', $ageGateSettings->legal_text) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('age_gate_legal_text')" />
                        </div>
                    </section>

                    <div class="flex justify-end border-t border-gray-200 pt-6">
                        <x-primary-button>
                            Guardar configuración
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <section
                id="locations"
                x-show="section === 'locations'"
                x-cloak
                class="scroll-mt-24 overflow-hidden bg-white shadow-sm sm:rounded-lg"
            >
                <div class="border-b border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Ubicaciones distritales</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Estas ubicaciones estarán disponibles en el selector obligatorio del formulario de posts.
                    </p>

                    <form method="POST" action="{{ route('settings.locations.store') }}" class="mt-6 grid gap-4 sm:grid-cols-[1fr_1fr_120px_auto] sm:items-end">
                        @csrf
                        <input type="hidden" name="locations_page" value="{{ $locations->currentPage() }}">

                        <div>
                            <x-input-label for="location_name" value="Distrito o ubicación" />
                            <x-text-input
                                id="location_name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name')"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="location_department" value="Departamento" />
                            <x-text-input
                                id="location_department"
                                name="department"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('department', 'Lima')"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('department')" />
                        </div>

                        <div>
                            <x-input-label for="location_sort_order" value="Orden" />
                            <x-text-input
                                id="location_sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                                :value="old('sort_order', 0)"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                        </div>

                        <x-primary-button class="h-10">
                            Agregar
                        </x-primary-button>
                    </form>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse ($locations as $location)
                        <div class="p-6">
                            <div class="grid gap-3 lg:grid-cols-[1fr_1fr_120px_auto] lg:items-end">
                                <form
                                    id="location-update-{{ $location->id }}"
                                    method="POST"
                                    action="{{ route('settings.locations.update', $location) }}"
                                    class="contents"
                                >
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="locations_page" value="{{ $locations->currentPage() }}">

                                    <div>
                                        <x-input-label for="location-name-{{ $location->id }}" value="Ubicación" />
                                        <x-text-input
                                            id="location-name-{{ $location->id }}"
                                            name="name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            :value="$location->name"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <x-input-label for="location-department-{{ $location->id }}" value="Departamento" />
                                        <x-text-input
                                            id="location-department-{{ $location->id }}"
                                            name="department"
                                            type="text"
                                            class="mt-1 block w-full"
                                            :value="$location->department"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <x-input-label for="location-order-{{ $location->id }}" value="Orden" />
                                        <x-text-input
                                            id="location-order-{{ $location->id }}"
                                            name="sort_order"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full"
                                            :value="$location->sort_order"
                                        />
                                    </div>
                                </form>

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" form="location-update-{{ $location->id }}" class="admin-button-primary">
                                        Guardar
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route('settings.locations.destroy', $location) }}"
                                        onsubmit="return confirm('¿Eliminar esta ubicación?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="locations_page" value="{{ $locations->currentPage() }}">
                                        <button type="submit" class="admin-button-danger-outline h-10">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="p-6 text-sm text-gray-500">Todavía no hay ubicaciones configuradas.</p>
                    @endforelse
                </div>

                @if ($locations->hasPages())
                    <div class="border-t border-gray-200 px-6 py-4">
                        {{ $locations->links() }}
                    </div>
                @endif
            </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
