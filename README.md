# Gatitas Hot

Marketplace de publicaciones clasificadas construido con Laravel, Blade, Alpine.js y Tailwind CSS. El sistema combina una experiencia pública orientada al descubrimiento de posts con un panel administrativo para gestionar contenido, ubicaciones, integraciones, cards reutilizables y configuración visual.

## Capacidades principales

- Portada pública con categorías, posts VIP, publicaciones recientes y directorio dinámico de ubicaciones.
- Buscador con tres filtros opcionales y acumulativos: ubicación, categoría y palabra clave.
- Navegación pública por categoría, ubicación (`/u/...`) y etiqueta (`/t/...`).
- Detalle de post con galería modal circular, cards informativas y botones de contacto.
- Publicación inmediata, programada y con fecha de finalización.
- Panel administrativo protegido por autenticación, verificación de correo y rol `admin`.
- Catálogo administrable de ubicaciones distritales del Perú.
- Configuración de colores, portada, servidor y confirmación de mayoría de edad.
- Roles y permisos mediante `spatie/laravel-permission`.

## Stack

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| UI | Blade, Alpine.js, Tailwind CSS |
| Assets | Vite |
| Base de datos | SQLite por defecto; compatible con conexiones Laravel |
| Autenticación | Laravel Breeze |
| Autorización | Spatie Laravel Permission |
| Iconos | Blade Heroicons |
| Pruebas | PHPUnit |

## Inicio rápido

```bash
composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm install
npm run build
php artisan serve
```

Para desarrollo concurrente:

```bash
composer dev
```

El seeder crea un usuario administrador local:

```text
Email: admin@test.com
Contraseña: Vidarte;123
```

Estas credenciales son exclusivamente de desarrollo y deben cambiarse o eliminarse en cualquier despliegue real.

## Reglas centrales del dominio

Un post es públicamente visible cuando:

1. Está activo.
2. Su fecha de publicación es nula o ya ocurrió.
3. Su fecha de finalización es nula o todavía no ocurrió.
4. Su categoría está activa cuando se consulta desde módulos públicos globales.

La ubicación del post es obligatoria y debe existir en el catálogo `locations`.

El buscador combina filtros con lógica `AND`:

- Sin filtros: muestra todos los posts públicos.
- Con uno, dos o tres filtros: aplica únicamente los criterios enviados.
- La palabra clave revisa título, subtítulo, cuerpo y tags.

## Estructura documental

La documentación profunda vive en [`docs/`](docs/README.md):

- [Arquitectura](docs/architecture.md)
- [Instalación y operación](docs/development.md)
- [Rutas y flujos HTTP](docs/routes.md)
- [Configuración técnica](docs/configuration.md)
- [Funciones y servicios internos](docs/technical-reference.md)
- [Base de datos, migraciones y seeders](docs/database.md)
- [Pruebas](docs/testing.md)
- [Módulos funcionales](docs/modules/README.md)

## Comandos útiles

```bash
php artisan route:list
php artisan migrate:status
php artisan db:seed
php artisan view:cache
php artisan test
npm run dev
npm run build
```

## Directorios relevantes

```text
app/Http/Controllers/   Casos de uso HTTP y validación
app/Models/             Modelos Eloquent
app/Support/            Consultas y transformaciones reutilizables
database/migrations/    Evolución del esquema
database/seeders/       Datos iniciales
resources/views/        Vistas y componentes Blade
resources/js/           Inicialización de Alpine
resources/css/          Tailwind y estilos globales
routes/                 Rutas públicas, administrativas y de autenticación
tests/                  Pruebas unitarias y funcionales
docs/                   Documentación técnica y funcional
```

## Seguridad operativa

- No conservar las credenciales sembradas en producción.
- Configurar `APP_DEBUG=false` en producción.
- Servir el sitio con HTTPS.
- Ejecutar migraciones con respaldo previo.
- Revisar que el catálogo de integraciones no contenga secretos expuestos.
- Actualmente las rutas administrativas se autorizan por `role:admin`; los permisos sembrados preparan una autorización más granular, pero no sustituyen ese middleware.
