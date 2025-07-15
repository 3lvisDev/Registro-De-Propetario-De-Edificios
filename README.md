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

## 👤 Usuario de prueba (opcional)

```txt
Email: admin@example.com
Contraseña: 12345678
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

