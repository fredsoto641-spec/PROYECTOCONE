# Módulo de integraciones

## Objetivo

Configura canales que se muestran como botones de contacto en el detalle del post.

## Proveedores

```text
whatsapp
telegram
sms
custom
```

## Reglas

- WhatsApp, Telegram y SMS son únicos por provider.
- Se permiten múltiples integraciones personalizadas.
- Solo integraciones activas participan en formularios y vistas públicas.
- El icono debe pertenecer al catálogo de Heroicons admitido.
- El color debe ser hexadecimal de seis dígitos.

## URLs

### WhatsApp

```text
{base_url}/{country_code}{number}
```

Fallback de base: `https://wa.me`.

### Telegram

```text
{base_url}/{username}
```

Fallback de base: `https://t.me`.

### SMS

Construye un enlace con prefijo y número normalizado.

### Custom

Usa directamente `base_url`.

## Credenciales

`credentials` acepta JSON y se almacena como JSON. No se cifra automáticamente.

## Render

El detalle:

1. Consulta integraciones activas.
2. Resuelve la URL correspondiente.
3. Elimina botones sin URL.
4. Aplica color e icono configurados.
