# Autenticación, roles y permisos

## Autenticación

Laravel Breeze proporciona:

- Registro.
- Login/logout.
- Recuperación de contraseña.
- Confirmación de contraseña.
- Verificación de correo.
- Perfil.

## Roles

`DatabaseSeeder` crea:

### Admin

Recibe todos los permisos.

### Editor

Recibe permisos de contenido para posts, categorías, cards e integraciones.

### Viewer

Recibe permisos de lectura.

## Autorización efectiva

Todas las rutas administrativas exigen primero:

```text
auth
verified
```

Cada acción añade un permiso específico:

| Acción | Permiso |
| --- | --- |
| Listar | `*.view` |
| Abrir formulario y crear | `*.create` |
| Abrir formulario y actualizar | `*.edit` |
| Eliminar | `*.delete` |
| Activar, ocultar o marcar VIP | `*.publish` |

El rol `admin` actúa como superusuario mediante `Gate::before`. Editor y viewer
se autorizan exclusivamente según los permisos asignados.

## Permisos

Se crean permisos para:

- Posts.
- Categorías.
- Cards.
- Integraciones.
- Configuración.
- Usuarios.
- Roles.

Las ubicaciones forman parte de configuración y usan `site-settings.edit`.

## Navegación

El menú y los botones de acciones usan `@can`, por lo que no muestran enlaces a
acciones que el usuario no puede ejecutar. La ruta continúa siendo la autoridad
final y responde `403` ante accesos manuales no autorizados.

## Usuario de desarrollo

```text
admin@test.com
Vidarte;123
```

Nunca usar estas credenciales en producción.
