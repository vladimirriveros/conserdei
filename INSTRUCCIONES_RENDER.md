# Actualización en Render

## Cambios incluidos

- Compilación automática de CSS y JavaScript con Vite dentro de Docker.
- Configuración de proxy HTTPS para evitar que el navegador bloquee estilos y scripts.
- Botón **Ingresar como invitado** en el login.
- Usuario invitado protegido y en modo solo lectura.
- Bloqueo global de operaciones POST, PUT, PATCH y DELETE para el rol `invitado`.

## Variables necesarias en Render

Además de las variables de la base de datos, agrega:

```env
APP_URL=https://conserdei.onrender.com
ASSET_URL=https://conserdei.onrender.com
SESSION_SECURE_COOKIE=true
```

Mantén también:

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stderr
```

## Subir los cambios a GitHub

Reemplaza los archivos del proyecto por esta versión y ejecuta:

```bash
git add .
git commit -m "Corregir assets de Render y agregar modo invitado"
git push origin main
```

Render iniciará un despliegue automático.

## Base de datos de demostración

Como la base de Supabase es nueva y de demostración, ejecuta una sola vez:

```bash
php artisan migrate:fresh --seed --force
```

Esto crea también el usuario invitado. El ingreso se realiza con el botón del login, sin escribir credenciales.

> No ejecutes `migrate:fresh` nuevamente cuando ya tengas datos que quieras conservar.
