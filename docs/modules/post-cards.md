# Módulo de cards

Las cards agregan información estructurada al lateral del detalle de un post.

## Plantillas

Una card con `post_id = NULL` es una plantilla administrable.

Ruta:

```text
/dashboard/post-cards
```

Campos:

- Título.
- Color.
- Rellenar fondo.
- Orden.
- Estado.
- Pares clave/valor.

## Cards de post

Al crear o editar un post se pueden:

- Añadir manualmente.
- Copiar desde una plantilla activa.
- Reordenar.
- Activar/desactivar.
- Elegir color y fondo.

## Restricciones

El controlador de plantillas rechaza editar, eliminar o alternar cards asociadas directamente a posts.

## Sincronización de presentación

Al actualizar una plantilla se propagan color y `fill_background` a copias que coinciden con:

- Título anterior.
- Color anterior.

Los campos de contenido no se sincronizan automáticamente.

## Render público

Solo se cargan cards activas:

```php
$post->cards()->active()->get();
```

Si `fill_background` está activo, el color llena la card; de lo contrario se usa como acento superior.
