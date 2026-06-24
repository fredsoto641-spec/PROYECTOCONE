# Configuración

## Variables de entorno

Las variables parten de `.env.example`.

### Aplicación

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
```

En producción:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` debe ser la URL HTTPS real.

### Base de datos

El proyecto usa SQLite por defecto:

```dotenv
DB_CONNECTION=sqlite
```

### Sesión, cache y colas

```dotenv
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Las tablas necesarias se crean mediante las migraciones base de Laravel.

### Correo

Por defecto el correo se escribe en logs:

```dotenv
MAIL_MAILER=log
```

Para recuperación de contraseña y verificación real debe configurarse un proveedor SMTP.

## Configuración administrable

Ruta: `/dashboard/settings`

La pantalla se divide en:

### Datos generales

- Texto principal del logo.
- Texto destacado del logo.
- País de contacto comercial.
- Número de WhatsApp comercial.
- Usuario de Telegram comercial.
- Título del sitio.
- Subtítulo.
- URL de imagen de portada.
- Archivo de imagen para el banner como alternativa a la URL.

El nombre de marca se guarda en dos partes para respetar el diseño bicolor del
header. El texto principal usa `--site-text` y el destacado usa
`--site-primary`; el footer concatena ambas partes y genera sus iniciales
automáticamente.

Cuando las integraciones de WhatsApp o Telegram están activas, la vista
`/publicar-anuncio` combina su URL base con estos datos generales. El prefijo de
WhatsApp se obtiene del país seleccionado. Un canal sin dato de contacto no se
muestra.

### Colores

Gestiona variables CSS públicas y administrativas:

```text
--site-primary
--site-primary-hover
--site-text
--site-muted
--site-bg
--admin-ink
--admin-ink-hover
--admin-muted
--admin-danger
--admin-focus
```

`SiteSetting::inlineCssVariableBlock()` genera el bloque `:root`.

### Servidor

- País.
- Código ISO.
- Offset UTC.

`SiteSetting::SERVER_COUNTRIES` también contiene el prefijo telefónico usado como fallback al construir contactos.

### Confirmación de edad

- Activación.
- Clave de `localStorage`.
- Badge, título y descripción.
- Botones de confirmar y salir.
- URL de salida.
- Texto legal.

El componente `age-confirmation-modal` recuerda la confirmación en el navegador.

### Footer

El footer público se configura como columnas dinámicas. Cada columna contiene:

- Título.
- Uno o más elementos con texto y enlace.

La estructura se almacena en `site_settings.footer_columns` como JSON:

```json
[
  {
    "title": "Información",
    "items": [
      {"label": "Categorías", "href": "/#categorias"},
      {"label": "Publicar anuncio", "href": "/publicar-anuncio"}
    ]
  }
]
```

El editor permite hasta 8 columnas y 12 enlaces por columna. Acepta rutas internas,
anclas, URLs HTTP/HTTPS y enlaces `mailto:`, `tel:` o `sms:`. La columna titulada
`Legal` también alimenta los enlaces legales de la franja inferior.

`FooterSeeder` carga la estructura inicial al ejecutar `php artisan migrate --seed`
o `php artisan db:seed`. Solo rellena el footer cuando no existe o está vacío, de
modo que una configuración personalizada no se pierde.

### Ubicaciones

- Alta, edición y eliminación.
- Paginación de 15 elementos.
- Rechazo de duplicados ignorando espacios y mayúsculas.
- No se permite eliminar una ubicación usada por posts.
- Renombrar una ubicación actualiza los posts asociados.

## Integraciones de contacto

Proveedores:

- WhatsApp.
- Telegram.
- SMS.
- Personalizado.

Cada integración configura:

- Nombre.
- Proveedor.
- URL base.
- Color.
- Icono.
- Credenciales JSON opcionales.
- Estado activo.

Los providers no personalizados son únicos; se permiten varias integraciones `custom`.

## Seguridad

No guardar secretos sensibles en `credentials` sin cifrado adicional. El campo tiene cast JSON, no cifrado automático.

## Carga segura de imágenes

Categorías, portada del sitio, portada del post y galería aceptan JPEG, PNG y
WebP. Se conserva la opción de URL externa.

El flujo de carga:

1. Valida error de carga, tamaño, MIME detectado, extensión y dimensiones.
2. Rechaza SVG y formatos no permitidos.
3. Decodifica y vuelve a codificar el contenido como WebP.
4. Elimina metadatos y cualquier contenido adicional incrustado.
5. Conserva la proporción original y solo reduce si supera los límites máximos;
   no crea lienzos cuadrados, relleno ni padding.
6. Genera el nombre mediante UUID y almacena en el disco público.

El procesador usa Imagick o GD cuando están disponibles, con ImageMagick como
fallback. Los límites se configuran mediante variables `IMAGE_UPLOAD_*`.

El disco predeterminado es `public`. En una instalación nueva debe existir el
enlace público:

```bash
php artisan storage:link
```

Variables disponibles:

```dotenv
IMAGE_UPLOAD_DISK=public
IMAGE_UPLOAD_MAX_KB=10240
IMAGE_UPLOAD_MAX_WIDTH=6000
IMAGE_UPLOAD_MAX_HEIGHT=6000
IMAGE_UPLOAD_MAX_PIXELS=25000000
IMAGE_UPLOAD_OUTPUT_MAX_WIDTH=2400
IMAGE_UPLOAD_OUTPUT_MAX_HEIGHT=2400
IMAGE_UPLOAD_WEBP_QUALITY=85
```

### Diagnóstico de errores de carga

El dashboard incluye un middleware que detecta fallos de transporte antes de la
validación normal. Cuando una carga falla, muestra un modal con:

- Mensaje normalizado para la persona administradora.
- Acción recomendada.
- Bloque técnico desplegable con campo, archivo, código PHP, límites efectivos,
  `CONTENT_LENGTH` y SAPI.

Los límites de la aplicación no pueden ampliar los límites de PHP. En el entorno
local actual, `/etc/php.ini` debe ajustarse si se desean archivos mayores de 2 MB:

```ini
upload_max_filesize = 10M
post_max_size = 128M
```

Después del cambio debe reiniciarse el servidor PHP.
