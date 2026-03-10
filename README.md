<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<h2 align="center">🏢 Sistema de Registro de Copropietarios</h2>

<p align="center">
  Proyecto desarrollado en Laravel para administrar edificios con múltiples departamentos y copropietarios.
</p>

---

## 🚀 Acerca del Proyecto

Este sistema fue desarrollado para gestionar fácilmente la información de los copropietarios de un edificio, permitiendo:

* Registrar nuevos copropietarios con nombre, teléfono, correo, departamento, bodega y estacionamiento.
* Buscar por nombre, patente, departamento y más.
* Visualizar de forma ordenada la información por número de departamento.
* Administrar usuarios autenticados (login).
* Panel limpio, moderno y responsivo con Bootstrap.

## 🧠 Tecnologías Utilizadas

* Laravel 10
* PHP 8.4
* MySQL/MariaDB
* Bootstrap 5
* Tailwind (opcional)
* GitHub Actions (pendiente)
* Nginx / Apache (según servidor)

## 📷 Capturas de Pantalla

> *Aquí puedes agregar imágenes del panel copropietario, búsqueda, registro, etc.*

## 🔧 Instalación

```bash
# Clonar el repositorio
git clone https://github.com/3lvisDev/Registro-De-Propetario-De-Edificios.git

cd laravel-panel

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install && npm run build

# Crear archivo .env
cp .env.example .env

# Configurar base de datos
php artisan key:generate
php artisan migrate
```

## 📋 Convenciones de Nombres

Este proyecto sigue las convenciones estándar de Laravel para mantener consistencia y facilitar el mantenimiento:

### Nombres de Campos en Base de Datos
* **snake_case** para todos los nombres de columnas
* Ejemplos: `nombre_completo`, `numero_departamento`, `rut_pasaporte`

### Nombres de Tablas
* **Plural en snake_case**
* Ejemplos: `copropietarios`, `persona_autorizadas`, `users`

### Propiedades de Modelos
* **snake_case** (Laravel convierte automáticamente desde la base de datos)
* Acceso: `$copropietario->nombre_completo`

### Métodos de Modelos
* **camelCase** para métodos y relaciones
* Ejemplos: `getNombreCompletoAttribute()`, `personasAutorizadas()`

### Campos Estandarizados Importantes

#### Campo `rut_pasaporte`
Este campo fue estandarizado en toda la aplicación para identificar a personas autorizadas:
* **Migración**: columna `rut_pasaporte`
* **Modelo**: incluido en `$fillable` como `rut_pasaporte`
* **Validación**: reglas definidas para `rut_pasaporte`
* **Vistas**: formularios usan `name="rut_pasaporte"`

#### Campo `numero_departamento`
Identifica el departamento asociado a copropietarios:
* Usado consistentemente en toda la aplicación
* Tipo: string (permite formatos como "101", "A-5", etc.)

### Campos Protegidos
Los siguientes campos **NO** pueden ser asignados masivamente (protección contra mass assignment):
* `id` - Clave primaria
* `created_at` - Timestamp de creación
* `updated_at` - Timestamp de actualización

### Tipos de Copropietario
El campo `tipo` acepta solo dos valores:
* `"Propietario"` - Dueño legal del departamento
* `"Arrendatario"` - Persona que arrienda el departamento

## 👤 Usuario de prueba 

```txt
📧 Email: publico@demo.com  
🔐 Contraseña: demo1234
🌎 Link: http://panel-apexcode.duckdns.org/
```

## ✨ Funcionalidades futuras

* [ ] Módulo de administración de gastos comunes.
* [ ] Historial de pagos por copropietario.
* [ ] Panel con estadísticas y gráficos.
* [ ] Exportación en PDF/Excel.
* [ ] Soporte multi-edificio.

## 🧑‍💻 Autor

**Elvis Da Silva**
📧 [xxelvisdsxx@gmail.com](mailto:xxelvisdsxx@gmail.com)
🐙 [GitHub](https://github.com/3lvisDev)

## 📄 Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT).

---

