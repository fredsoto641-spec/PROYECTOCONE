# Arquitectura

## Visión general

La aplicación sigue la arquitectura MVC de Laravel, complementada por clases de soporte para consultas o transformaciones reutilizables.

```text
Solicitud HTTP
    │
    ▼
routes/web.php o routes/auth.php
    │
    ▼
Controller / closure de ruta
    │
    ├── validación
    ├── modelos Eloquent
    ├── clases App\Support
    └── transacciones
    │
    ▼
Vista Blade + componentes
    │
    ├── Alpine.js: interacción local
    ├── Tailwind: presentación
    └── Vite: compilación
    │
    ▼
HTML público o administrativo
```

## Capas

### Rutas

`routes/web.php` contiene:

- Inicio público.
- Buscador.
- Directorios por ubicación y etiqueta.
- Detalle de categoría y post.
- Dashboard y recursos administrativos.
- Perfil autenticado.

`routes/auth.php` contiene autenticación, recuperación de contraseña y verificación de correo.

### Controladores

- Los controladores administrativos encapsulan CRUD, validación y mensajes de sesión.
- `PublicPostSearchController` resuelve filtros públicos combinables.
- `PublicPostBrowseController` resuelve directorios y listados por ubicación o tag.
- Algunas vistas públicas históricas todavía se sirven mediante closures en `routes/web.php`.

### Modelos

Los modelos usan atributos `#[Fillable]`, casts y relaciones Eloquent:

- `Category` tiene muchos posts.
- `Post` pertenece a una categoría y tiene muchas cards.
- `PostCard` puede pertenecer a un post o funcionar como plantilla cuando `post_id` es nulo.
- `Location` es el catálogo válido para `Post.location`.
- `SiteSetting` y `AgeGateSetting` funcionan como registros singleton.
- `User` usa roles de Spatie.

### Soporte reutilizable

`app/Support` evita duplicar lógica pública:

- `PostBodyRenderer`
- `PublicLocationDirectory`
- `PublicNavigation`
- `PublicSearchOptions`
- `SecureImageUploader`

### Vistas

- `resources/views/components` concentra componentes reutilizables.
- `resources/views/posts`, `categories`, `settings`, etc. agrupan vistas por módulo.
- Alpine maneja galería, modales, navegación del panel de configuración y formularios dinámicos.

## Flujos críticos

### Creación de post

1. El administrador abre `/dashboard/posts/create`.
2. El controlador carga categorías, ubicaciones, integraciones y plantillas de cards.
3. El formulario valida ubicación obligatoria, fechas, imágenes, tags y contactos.
4. `SecureImageUploader` re-codifica los archivos aceptados y genera URLs públicas.
5. `PostController` combina URLs de galería y archivos subidos, y construye contactos.
6. Post y cards se guardan dentro de una transacción.

### Consulta pública

1. Se filtran categorías activas.
2. Se aplica `publiclyVisible()`.
3. El post se transforma al contrato esperado por `listing-card`.
4. La card completa enlaza al detalle.

### Configuración

La vista usa un panel 1/4–3/4:

- Menú lateral: portada, colores, servidor, footer, edad y ubicaciones.
- Contenido: una sección visible por vez.
- El hash conserva la sección actual.
- Los errores y la paginación abren la sección adecuada.

## Decisiones de diseño

- Las ubicaciones se gestionan localmente; no dependen de una API externa.
- `Post.location` sigue almacenando el nombre, no un `location_id`. Esto mantiene URLs y datos legibles, pero exige actualizar posts al renombrar ubicaciones.
- La búsqueda usa lógica `AND` entre filtros opcionales.
- La galería preserva entradas repetidas porque cada línea representa una posición navegable.
- Los settings singleton se crean automáticamente con valores por defecto.

## Deuda técnica conocida

- Extraer las closures públicas de `routes/web.php` a controladores dedicados.
- Centralizar la transformación de `Post` a `listing-card`; actualmente existe en más de un controlador/closure.
- Migrar `posts.location` a clave foránea si se requiere integridad relacional estricta.
- Añadir policies por modelo si en el futuro se requiere autorización por
  propiedad del registro; actualmente las acciones usan permisos granulares.
- Evitar consultas completas en memoria en directorios públicos si el volumen crece significativamente.
