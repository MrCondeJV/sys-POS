# 🛒 sys-POS — Sistema de Punto de Venta Comercial Multiempresa

Sistema POS (Point of Sale) para comercios minoristas colombianos. Soporta múltiples empresas y sucursales, control de caja, inventario por Kardex, cartera de clientes, compras, reportes y facturación electrónica DIAN.

---

## 🧰 Stack Tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+ / Laravel 12 |
| Frontend | Livewire 4, Alpine.js, Tailwind CSS 4 |
| Base de datos | MySQL (producción) / SQLite (desarrollo) |
| Autenticación | Laravel Sanctum |
| Roles y permisos | spatie/laravel-permission |
| Assets | Vite 6 |
| Colas | Laravel Queue (driver `database`) |
| API | REST `/api/v1` con Sanctum tokens |

---

## ✅ Requisitos Previos

- **PHP** >= 8.2 (recomendado 8.4)
- **Composer** >= 2.x
- **Node.js** >= 20.x y **npm** >= 10.x
- **MySQL** 8.x (o SQLite para entorno local rápido)
- Extensiones PHP requeridas: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`

---

## 🚀 Instalación y Puesta en Marcha

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/sys-POS.git
cd sys-POS
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Crear el archivo de entorno

```bash
cp .env.example .env
```

### 4. Configurar la base de datos en `.env`

**Opción A — SQLite** *(más rápido para desarrollo local, sin servidor de BD)*

```env
DB_CONNECTION=sqlite
```

Luego crea el archivo de la base de datos:

```bash
touch database/database.sqlite
```

> En Windows (PowerShell): `New-Item -Path "database/database.sqlite" -ItemType File -Force`

**Opción B — MySQL** *(recomendado para producción)*

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sys_pos
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 5. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y datos iniciales (seed)

```bash
php artisan migrate --seed
```

Esto crea todas las tablas y carga los datos de prueba: empresa demo, sucursal, productos, proveedores, clientes y los usuarios listados abajo.

### 7. Instalar dependencias frontend

```bash
npm install
```

### 8. Levantar el entorno de desarrollo

El siguiente comando levanta **todo en paralelo** (servidor PHP, colas, logs y Vite) con un solo comando:

```bash
composer run dev
```

Equivale a ejecutar simultáneamente:

| Proceso | Comando |
|---|---|
| Servidor web | `php artisan serve` |
| Cola de trabajos | `php artisan queue:listen --tries=1` |
| Logs en tiempo real | `php artisan pail --timeout=0` |
| Assets (Vite HMR) | `npm run dev` |

La aplicación estará disponible en **[http://localhost:8000](http://localhost:8000)**.

---

## 👤 Usuarios de Prueba

| Nombre | Email | Contraseña | Rol |
|---|---|---|---|
| Super Administrador | `superadmin@pos.com` | `password` | SUPER_ADMIN |
| Carlos Administrador | `admin@pos.com` | `password` | ADMIN_EMPRESA |
| Ana Cajera | `cajero@pos.com` | `password` | CAJERO |

---

## 🔐 Roles del Sistema

| Rol | Función |
|---|---|
| `SUPER_ADMIN` | Administrador técnico global. Acceso total a todas las empresas. |
| `ADMIN_EMPRESA` | Dueño/gerente. Control completo de su empresa: ventas, compras, caja, inventario, usuarios y configuración. |
| `ADMIN_SUCURSAL` | Encargado de punto de venta. Administra inventario, caja y compras de su sucursal. |
| `CAJERO` | Operador del POS. Cobra, abre/cierra su caja. No puede anular ventas ni modificar inventario. |
| `VENDEDOR` | Asesor de ventas. Genera ventas y cotizaciones. Sin acceso a caja ni dinero. |
| `CONTADOR` | Auditor financiero. Solo lectura sobre ventas, compras, reportes, cartera e impuestos. |

---

## 📦 Módulos del Sistema

```
Empresas · Sucursales · Usuarios · Seguridad (Roles/Permisos)
Catálogos · Productos · Inventario (Kardex) · Proveedores
Compras · Clientes · Cartera · Caja · Ventas · POS
Pagos · Devoluciones · Listas de Precios · Impuestos
Documentos · Reportes · Auditoría · Facturación Electrónica DIAN
```

---

## ⚙️ Comandos Útiles

```bash
# Refrescar BD y volver a sembrar datos
php artisan migrate:fresh --seed

# Limpiar caché de configuración
php artisan config:clear && php artisan cache:clear

# Procesar colas manualmente (sin composer run dev)
php artisan queue:work

# Compilar assets para producción
npm run build

# Ejecutar tests
php artisan test
```

---

## 🏗️ Arquitectura

El sistema sigue una arquitectura de **Monolito Modular** con separación estricta por capas:

```
Presentación  →  Aplicación  →  Dominio  →  Infraestructura  →  Persistencia
(Blade/Livewire)  (Controllers)  (Services/Actions)  (Jobs/Queues)  (Eloquent/MySQL)
```

**Multi-tenancy:** Base de datos única con aislamiento lógico por `empresa_id`. Cada query aplica automáticamente `WHERE empresa_id = ?` mediante Global Scopes.

**Inventario:** El stock nunca se modifica directamente. Todo cambio es un registro inmutable en `movimientos_inventario` (patrón Kardex).

---

## 🌐 API REST

Base URL: `http://localhost:8000/api/v1`

Autenticación mediante **Laravel Sanctum** (Bearer Token). Formato de respuesta unificado:

```json
{
  "success": true,
  "data": {},
  "message": "Operación exitosa"
}
```

---

## 📋 Requisitos de Producción

Ver [`.env.production.example`](.env.production.example) para la configuración completa de producción.

Pasos adicionales para producción:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

---

## 📄 Licencia

Este proyecto está licenciado bajo la [MIT License](https://opensource.org/licenses/MIT).
