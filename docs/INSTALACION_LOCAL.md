# Instalación local en Windows

## Requisitos

- PHP 8.1 o superior, con las extensiones `pdo_sqlite` y `sqlite3`.
- Composer.
- Node.js 20 y npm (para compilar los recursos visuales).

## Instalación

Desde PowerShell, en la carpeta del proyecto:

```powershell
.\install-local.ps1
php artisan serve --host=127.0.0.1
```

Abra `http://127.0.0.1:8000`. La primera pantalla solicitará correo y contraseña; esa cuenta será el administrador.
Una vez creada, la instalación se bloquea y los usuarios adicionales se crean desde **Usuarios**.

El servidor usa `127.0.0.1` deliberadamente para no exponer la aplicación a otros equipos de la red.
