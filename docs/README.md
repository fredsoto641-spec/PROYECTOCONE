# Documentación del sistema

Este directorio organiza la documentación desde una vista general hasta referencias de implementación.

## Lectura recomendada

1. [Arquitectura](architecture.md): componentes y flujo de datos.
2. [Módulos](modules/README.md): comportamiento funcional por área.
3. [Desarrollo](development.md): instalación, comandos y operación local.
4. [Configuración](configuration.md): variables de entorno y ajustes administrables.
5. [Referencia técnica](technical-reference.md): scopes, helpers y transformaciones.
6. [Rutas](routes.md): endpoints públicos y administrativos.
7. [Base de datos](database.md): esquema, migraciones y seeders.
8. [Pruebas](testing.md): cobertura y estrategias de verificación.

## Documentación por módulo

| Módulo | Documento |
| --- | --- |
| Experiencia pública, búsqueda y directorios | [public-experience.md](modules/public-experience.md) |
| Posts y galería | [posts.md](modules/posts.md) |
| Categorías | [categories.md](modules/categories.md) |
| Cards reutilizables | [post-cards.md](modules/post-cards.md) |
| Integraciones y contacto | [integrations.md](modules/integrations.md) |
| Configuración y ubicaciones | [settings-locations.md](modules/settings-locations.md) |
| Usuarios, roles y permisos | [auth-permissions.md](modules/auth-permissions.md) |

## Convenciones

- “Público” significa que no requiere autenticación.
- “Post visible” se refiere a `Post::scopePubliclyVisible()`.
- Los slugs públicos se generan con `Str::slug`.
- Las rutas administrativas viven bajo `/dashboard`.
- Las referencias de archivos son relativas a la raíz del repositorio.
