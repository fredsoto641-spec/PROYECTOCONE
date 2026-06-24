# Módulo de configuración y ubicaciones

## Interfaz

Ruta:

```text
/dashboard/settings
```

Layout:

- 1/4 menú de secciones.
- 3/4 contenido.
- Responsive en móvil.
- Hash para conservar sección.

Secciones:

- Portada.
- Colores.
- Servidor.
- Confirmación de edad.
- Ubicaciones.

## Settings singleton

`SiteSetting` y `AgeGateSetting` usan registro ID 1 y defaults de código.

## Catálogo de ubicaciones

El catálogo local evita depender de una API externa.

Operaciones:

- Agregar.
- Editar.
- Eliminar si no está en uso.
- Paginar, 15 por página.

## Duplicados

La validación rechaza:

```text
Miraflores
miraflores
 Miraflores
```

como el mismo nombre.

## Renombrado

La relación con posts es textual. Por ello, renombrar ejecuta una transacción que:

1. Actualiza `locations.name`.
2. Actualiza `posts.location` con el nuevo nombre.

## Eliminación

Si un post usa la ubicación, se rechaza la eliminación.

## Seeder

`LocationSeeder` carga distritos comunes de 25 departamentos. Usa `updateOrCreate`.

## Formulario de posts

La ubicación:

- Es obligatoria.
- Debe existir en `locations.name`.
- Se selecciona en un `<select>` agrupado por departamento.
