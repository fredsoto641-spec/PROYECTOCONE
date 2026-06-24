# Módulo de experiencia pública

## Inicio

Vista: `resources/views/welcome.blade.php`

Incluye:

- Hero configurable.
- Buscador.
- Categorías activas.
- Posts VIP.
- Publicaciones recientes.
- CTA.
- Directorio dinámico de ubicaciones.
- Modal de mayoría de edad.

## Publicar anuncio

Ruta: `/publicar-anuncio`

Explica:

- Tipos de anuncio estándar y destacado.
- Beneficios de la publicación.
- Ejemplo visual de una ficha.
- Proceso básico de publicación.
- Contacto mediante integraciones activas de WhatsApp y Telegram.

Todos los botones públicos “Publicar anuncio” enlazan a esta vista.

## Cards de listado

`listing-card` es un enlace completo, no depende de un botón “Ver post”.

Muestra:

- Imagen.
- Badges de verificado/destacado.
- Ubicación.
- Categoría.
- Título.
- Antigüedad.

## Buscador

Ruta: `/buscar`

Criterios opcionales:

- Ubicación.
- Categoría.
- Palabra clave.

Todos se combinan con `AND`. Si los tres están vacíos se muestran todos los posts públicos.

Los selects solo contienen valores con posts visibles, evitando opciones muertas.

## Directorios

### Ubicación

```text
/u
/u/{slug}
```

### Etiqueta

```text
/t
/t/{slug}
```

El detalle del post enlaza ubicación y tags hacia estos módulos.

## Directorio de tres cards

`PublicLocationDirectory` toma ubicaciones de posts activos, agrupa y reparte en:

- Zonas con actividad.
- Más ubicaciones.
- Otros lugares.

## Visibilidad

Los módulos globales exigen:

- Post públicamente visible.
- Categoría activa.

## Componentes principales

```text
hero
search-bar
category-card
listing-card
listing-cards-marquee
latest-publications-marquee
location-directory
navbar
footer
age-confirmation-modal
```

El componente `footer` recibe sus columnas y enlaces desde
`SiteSetting::footerGroups()`, por lo que todas las vistas públicas comparten la
configuración administrada desde el dashboard.
