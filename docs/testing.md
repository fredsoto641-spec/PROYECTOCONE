# Pruebas

## Configuración

`phpunit.xml` usa:

```text
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
```

## Ejecutar

```bash
php artisan test
```

Un archivo:

```bash
php artisan test tests/Feature/PublicPostSearchTest.php
```

Un método:

```bash
php artisan test --filter=test_all_filter_combinations
```

## Cobertura funcional relevante

### Autenticación y perfil

- Login correcto e incorrecto.
- Logout.
- Verificación de correo.
- Password reset/update/confirmation.
- Actualización y eliminación de perfil.

### Ubicaciones

- CRUD administrativo.
- Renombrado propagado a posts.
- Protección contra eliminación en uso.
- Ubicación obligatoria.
- Rechazo de ubicación fuera del catálogo.
- Paginación.
- Rechazo de duplicados case-insensitive y con espacios.

### Experiencia pública

- Directorios por ubicación/tag.
- Exclusión de posts inactivos.
- Tres cards dinámicas de ubicaciones.
- Enlaces públicos correctos.

### Relacionados y galería

- Máximo tres posts relacionados.
- Solo posts visibles.
- Render de galería con URLs multilínea.

### Carga de imágenes

- Re-codificación a WebP.
- Conservación de la proporción original sin padding ni lienzo cuadrado.
- Rechazo de scripts renombrados como imagen.
- Diagnóstico de `UPLOAD_ERR_INI_SIZE` mediante modal.
- Prioridad del archivo sobre la URL.
- Combinación de URLs por línea y archivos múltiples en la galería.
- Carga de banner desde configuración.

### Búsqueda

Se prueban las ocho combinaciones:

1. Ningún filtro.
2. Solo ubicación.
3. Solo categoría.
4. Solo palabra clave.
5. Ubicación + categoría.
6. Ubicación + palabra.
7. Categoría + palabra.
8. Los tres.

Los filtros se combinan con lógica `AND`.

## Validaciones complementarias

```bash
php -l path/to/file.php
php artisan view:cache
npm run build
git diff --check
```

## Criterio para nuevas pruebas

Cada cambio debe cubrir:

- Caso feliz.
- Entrada inválida.
- Visibilidad/autorización.
- Datos inactivos o vencidos.
- Regresión del comportamiento solicitado.
