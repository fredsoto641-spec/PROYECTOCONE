# Referencia técnica

## `Post::scopePubliclyVisible()`

Archivo: `app/Models/Post.php`

Aplica:

```text
is_active = true
AND (published_at IS NULL OR published_at <= now)
AND (ends_at IS NULL OR ends_at > now)
```

Los métodos equivalentes para una instancia son:

- `isPendingPublication()`
- `isPubliclyVisible()`
- `isFinished()`

## `PostBodyRenderer`

Convierte el cuerpo del post en HTML seguro.

Soporta:

```markdown
[Texto](https://example.com)
https://example.com
![Descripción](https://example.com/image.jpg)
```

Características:

- Solo esquemas HTTP/HTTPS.
- Escape de texto y etiquetas.
- Enlaces externos con `noopener noreferrer`.
- Imágenes cargadas con `loading="lazy"`.

## `PublicLocationDirectory::make()`

Construye el bloque “Explora anuncios por zona”.

1. Consulta posts públicamente visibles.
2. Exige categoría activa.
3. Agrupa por slug de ubicación.
4. Ordena por cantidad de posts y luego alfabéticamente.
5. Distribuye las ubicaciones en tres cards.
6. Genera enlaces `/u/{slug}`.

## `PublicSearchOptions`

### `categories()`

Devuelve categorías activas que tienen al menos un post visible.

### `locations()`

Devuelve ubicaciones presentes en posts visibles dentro de categorías activas.

### `all()`

Contrato:

```php
[
    'categories' => [
        ['label' => 'Nombre', 'value' => 'slug'],
    ],
    'locations' => [
        ['label' => 'Nombre', 'value' => 'slug'],
    ],
]
```

## Buscador público

Controlador: `PublicPostSearchController`

Filtros:

- `location`
- `category`
- `query`

Los tres son opcionales y acumulativos con `AND`.

La palabra clave genera un grupo:

```text
title LIKE keyword
OR subtitle LIKE keyword
OR body LIKE keyword
OR tags LIKE keyword
```

Ese grupo se combina mediante `AND` con categoría y ubicación.

## Galería de imágenes

Componente: `post-image-gallery.blade.php`

- La portada ocupa el índice 0.
- Las URLs adicionales se separan por saltos de línea.
- Las entradas repetidas se preservan.
- Navegación circular anterior/siguiente.
- Teclas `ArrowLeft`, `ArrowRight` y `Escape`.
- Restaura el foco al cerrar.
- Bloquea el scroll del documento.

## Construcción de contactos

`PostController` usa integraciones activas:

- WhatsApp: URL base + prefijo + teléfono normalizado.
- Telegram: URL base + username sin `@`.
- SMS: esquema/base + teléfono.

Si el post no proporciona código de país se usa el configurado para el servidor.

## Cards

Hay dos tipos en la misma tabla:

- Plantilla: `post_id = NULL`.
- Copia de post: `post_id` con valor.

Al guardar un post:

1. Se validan cards y campos.
2. Se eliminan las copias anteriores.
3. Se recrean en orden.

Al cambiar presentación de una plantilla se sincronizan color y fondo de copias que coinciden por título y color anterior.

## Slugs

- Categorías: slug normalizado y sufijo incremental si existe.
- Posts: título normalizado + número aleatorio de cuatro dígitos.
- Ubicaciones/tags públicos: `Str::slug` en tiempo de consulta.

## Singletons de configuración

`SiteSetting::current()` y `AgeGateSetting::current()`:

- Retornan defaults si la tabla todavía no existe.
- Usan `firstOrCreate(['id' => 1], DEFAULTS)` cuando existe.

Esto permite renderizar parcialmente durante instalación o migraciones.
