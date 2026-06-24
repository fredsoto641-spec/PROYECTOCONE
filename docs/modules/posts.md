# Módulo de posts

## Administración

Rutas bajo `/dashboard/posts`.

Operaciones:

- Crear.
- Editar.
- Eliminar.
- Activar/ocultar.
- Marcar/desmarcar VIP.

Los formularios de creación y edición usan un onboarding 1/4–3/4 con navegación
lateral y seis pasos: datos básicos, contenido, imágenes, contacto, cards y
publicación. Los errores abren automáticamente el paso correspondiente y el hash
de la URL conserva la sección activa.

## Campos

### Obligatorios

- Categoría.
- Título.
- Ubicación del catálogo.
- Cuerpo.
- Modo de publicación.

### Opcionales

- Subtítulo.
- Portada.
- Galería.
- Contactos.
- Tags.
- Fecha de finalización.
- Cards.

## Publicación

### Inmediata

`published_at` se asigna a `now()`.

### Programada

Exige fecha de publicación.

### Finalización

- Inmediata: `ends_at` debe ser posterior al momento actual.
- Programada: `ends_at` debe ser posterior a `published_at`.

## Imágenes

- `cover_image_url`: portada.
- `gallery_image_urls`: JSON con URLs.
- El formulario acepta una URL por línea.
- La portada también acepta un archivo.
- La galería permite cargar hasta 12 archivos por envío.
- Las imágenes subidas se agregan después de las URLs escritas.
- La vista también tolera saltos de línea dentro de elementos históricos.

La galería modal incluye portada y todas las entradas adicionales.

## Tags

El formulario admite comas o saltos de línea. Se limpian, filtran y deduplican.

Tags especiales:

- `verificado`: badge de verificación.
- `destacado`: badge destacado.

`is_vip` también activa destacado.

## Posts relacionados

- Misma categoría.
- Excluye el post actual.
- Solo públicamente visibles.
- Ordenados por publicación/creación.
- Máximo tres.

## Cuerpo enriquecido

`PostBodyRenderer` soporta enlaces, URLs automáticas e imágenes Markdown seguras.

## Detalle público

Ruta:

```text
/{category-slug}/{post-slug}
```

Valida:

- Categoría activa.
- Pertenencia del post a la categoría.
- Visibilidad pública.

## Cards públicas

- El título se muestra en una sola línea y aplica elipsis cuando supera el ancho.
- Debajo se muestra una línea del subtítulo, también con elipsis.
- El texto completo permanece disponible en el atributo `title`.
- El espacio del subtítulo se conserva aunque el post no tenga uno, para
  mantener todas las cards alineadas.

## Paginación pública

Los listados de categoría, búsqueda, ubicación y etiqueta muestran un máximo de
20 posts por página. La búsqueda conserva sus filtros al navegar entre páginas.

## Persistencia de cards

Posts y cards se guardan en una transacción. En edición, las cards del post se recrean para mantener orden y estado.
