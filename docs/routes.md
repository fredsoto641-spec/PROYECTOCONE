# Rutas y flujos HTTP

## Públicas

| Método | Ruta | Nombre | Propósito |
| --- | --- | --- | --- |
| GET | `/` | — | Inicio |
| GET | `/buscar` | `posts.search` | Búsqueda combinada |
| GET | `/publicar-anuncio` | `advertise` | Información y contacto para publicar |
| GET | `/u` | `posts.locations.index` | Directorio de ubicaciones |
| GET | `/u/{location}` | `posts.locations.show` | Posts por ubicación |
| GET | `/t` | `posts.tags.index` | Directorio de etiquetas |
| GET | `/t/{tag}` | `posts.tags.show` | Posts por etiqueta |
| GET | `/{category}` | `categories.public.show` | Posts de categoría |
| GET | `/{category}/{post}` | `posts.public.show` | Detalle del post |

Las rutas dinámicas de categoría se declaran al final para no capturar `/buscar`, `/u`, `/t` y rutas del sistema.

## Dashboard

Las rutas administrativas requieren `auth`, `verified` y el permiso de la
acción. El rol admin tiene bypass global; editor y viewer dependen de permisos.

### Categorías

CRUD bajo `/dashboard/categories` y:

```text
PATCH /dashboard/categories/{category}/toggle-visibility
```

Permisos: `categories.view`, `categories.create`, `categories.edit`,
`categories.delete` y `categories.publish`.

### Posts

CRUD bajo `/dashboard/posts` y:

```text
PATCH /dashboard/posts/{post}/toggle-visibility
PATCH /dashboard/posts/{post}/toggle-vip
```

Permisos: `posts.view`, `posts.create`, `posts.edit`, `posts.delete` y
`posts.publish`.

### Cards

CRUD de plantillas bajo `/dashboard/post-cards` y toggle de visibilidad.

Permisos: `cards.view`, `cards.create`, `cards.edit`, `cards.delete` y
`cards.publish`.

### Integraciones

CRUD bajo `/dashboard/integrations` y toggle de visibilidad.

Permisos: `integrations.view`, `integrations.create`, `integrations.edit`,
`integrations.delete` y `integrations.publish`.

### Configuración

```text
GET    /dashboard/settings
PUT    /dashboard/settings
POST   /dashboard/settings/locations
PUT    /dashboard/settings/locations/{location}
DELETE /dashboard/settings/locations/{location}
```

Lectura usa `site-settings.view`; cambios de configuración y ubicaciones usan
`site-settings.edit`.

## Perfil

Requiere autenticación:

```text
GET    /profile
PATCH  /profile
DELETE /profile
```

## Autenticación

Laravel Breeze proporciona:

- Login/logout.
- Registro.
- Confirmación de contraseña.
- Recuperación y actualización de contraseña.
- Verificación de correo.

## Resolución de modelos

- Categorías públicas usan `{category:slug}`.
- Posts públicos usan `{post:slug}`.
- El detalle valida además que el post pertenezca a la categoría recibida.
