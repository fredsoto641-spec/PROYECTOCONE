# Desarrollo, instalación y operación

## Requisitos

- PHP 8.3 o superior.
- Composer 2.
- Node.js compatible con Vite 8.
- npm.
- Extensiones PHP requeridas por Laravel y el driver de base de datos.

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
```

Iniciar el servidor:

```bash
php artisan serve
```

O iniciar servidor, cola, logs y Vite conjuntamente:

```bash
composer dev
```

## Base de datos

El entorno de ejemplo usa SQLite:

```dotenv
DB_CONNECTION=sqlite
```

Para MySQL/PostgreSQL deben definirse las variables estándar de Laravel y probar especialmente:

- Cambios de nulabilidad en migraciones.
- Comparación de mayúsculas/minúsculas.
- Consultas `LIKE` del buscador.

## Assets

Desarrollo:

```bash
npm run dev
```

Producción:

```bash
npm run build
```

Los puntos de entrada son:

```text
resources/css/app.css
resources/js/app.js
```

## Cachés

```bash
php artisan optimize:clear
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

Durante desarrollo se recomienda `optimize:clear` después de cambios de configuración o rutas.

## Migraciones y datos iniciales

```bash
php artisan migrate
php artisan db:seed
```

Reconstrucción completa local:

```bash
php artisan migrate:fresh --seed
```

Este comando elimina todos los datos. No debe ejecutarse sobre producción.

## Usuario sembrado

`DatabaseSeeder` crea:

```text
admin@test.com
Vidarte;123
```

Cambiar o eliminar en ambientes compartidos.

## Diagnóstico

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan tinker
tail -f storage/logs/laravel.log
```

## Flujo recomendado de cambios

1. Revisar worktree con `git status`.
2. Implementar migración antes del código que depende del esquema.
3. Añadir o actualizar pruebas funcionales.
4. Ejecutar validación PHP/Blade.
5. Ejecutar `npm run build`.
6. Revisar `git diff --check`.
