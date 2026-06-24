# Módulo de categorías

## Administración

CRUD bajo `/dashboard/categories`.

Campos:

- Nombre obligatorio.
- Slug opcional.
- Descripción.
- URL de imagen.
- Archivo de imagen JPEG, PNG o WebP como alternativa a la URL.
- Orden.
- Estado activo.

## Slugs

Si el slug está vacío se usa el nombre. Se normaliza con `Str::slug` y, ante colisión, se añaden sufijos incrementales:

```text
categoria
categoria-2
categoria-3
```

## Visibilidad

Una categoría inactiva:

- No aparece en el inicio.
- No aparece en opciones del buscador.
- Devuelve 404 en su vista pública.
- Excluye sus posts de consultas públicas globales.

## Eliminación

La FK de posts usa `cascadeOnDelete`; eliminar una categoría elimina sus posts.

## Vista pública

Ruta:

```text
/{category-slug}
```

Lista posts visibles y reutiliza el directorio dinámico de ubicaciones.

Si se envían URL y archivo, el archivo subido tiene prioridad.
