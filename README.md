# 🛒 sys-POS — Sistema de Punto de Venta Comercial Multiempresa

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

**sys-POS** es una solución web moderna, transaccional y robusta diseñada para comercios minoristas en Colombia (ferreterías, minimercados, droguerías, almacenes y tiendas especializadas). Construido bajo una arquitectura **Monolito Modular Multiempresa (Multi-tenancy)** de alto rendimiento, ofrece soporte nativo para múltiples sucursales, control estricto de caja, trazabilidad de inventario mediante Kardex inmutable, cartera de créditos, facturación electrónica DIAN e identidad visual personalizable por empresa.

---

## 👥 Usuarios de Prueba para Demostración

El comando `php artisan migrate --seed` o `php artisan db:seed` genera dos empresas de ejemplo listas para operar: una **Ferretería comercial real** y un **Comercio demo general**, además de una cuenta global de Super Administrador:

### 🔨 Empresa Demo: *Ferretería y Construcciones El Maestro S.A.S.*
> **NIT:** `901.234.567-8` · **Sedes:** Sede Principal Centro y Sede Norte Suba · **Catálogo:** ~65 productos reales con código de barras, Kardex y stock en ambas sedes.

| Rol | Nombre | Correo Electrónico | Contraseña | Sede Asignada |
|---|---|---|---|---|
| **Admin Empresa (Gerente)** | Ricardo Maestro | `admin@ferreteria.com` | `password` | Principal Centro |
| **Cajera Principal** | Valentina Ríos | `cajero1@ferreteria.com` | `password` | Principal Centro |
| **Cajero Sede Norte** | Jorge Bermúdez | `cajero2@ferreteria.com` | `password` | Sede Norte Suba |
| **Asesor de Ventas** | Andrés Mora | `vendedor@ferreteria.com` | `password` | Principal Centro |

### 🏢 Plataforma Global & Comercio Demo
| Rol | Nombre | Correo Electrónico | Contraseña | Descripción |
|---|---|---|---|---|
| **Super Administrador** | Plataforma Global | `superadmin@pos.com` | `password` | Control total SaaS de todos los comercios |
| **Admin Empresa Demo** | Carlos Administrador | `admin@pos.com` | `password` | Comercio Demo POS S.A.S. |
| **Cajera Demo** | Ana Cajera | `cajero@pos.com` | `password` | Sede Centro Comercio Demo |

---

## 🧭 Módulos del Sistema

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                              MÓDULOS SYS-POS                                │
├─────────────────────────┬─────────────────────────┬─────────────────────────┤
│ 🛒 Punto de Venta (POS) │ 📦 Inventario & Kardex  │ 🏦 Control de Cajas     │
│ 👥 Cartera y Crédito    │ 🚚 Multisucursal        │ 🧾 Facturación DIAN     │
│ 🎨 Marca e Identidad    │ 🏢 Gestión SaaS Tenants │ 📊 Reportes & Auditoría │
└─────────────────────────┴─────────────────────────┴─────────────────────────┘
```

### 🛒 1. Punto de Venta (POS Ultrarrápido)
- Interfaz reactiva optimizada para cajeros con lector de código de barras o teclado numérico.
- Carrito de compras con cálculo automático de bases gravables e IVA (19%, 5%, Exento y Excluido).
- Múltiples formas de pago: Efectivo, Tarjeta débito/crédito, Transferencias (Nequi, Daviplata, Bancolombia) y Pagos Mixtos fraccionados.
- Cálculo automático de cambio/vueltos e impresión directa de tickets térmicos.

### 📦 2. Inventario, Catálogos y Kardex Inmutable
- Principio contable estricto: **el stock nunca se altera con `UPDATE` directo**. Todo cambio genera una fila en `movimientos_inventario` (Entrada por compra, Salida por venta, Ajuste, Devolución o Traslado).
- Alertas visuales de stock mínimo y desabastecimiento.
- Clasificación completa por Categorías, Marcas y Unidades de medida (UND, MT, KG, BTO, GL, etc.).
- Listas de precios personalizables (precios minoristas, mayoristas y distribuidores).

### 🏦 3. Control de Cajas y Arqueos
- Apertura obligatoria de turno con fondo de caja inicial verificado.
- Registro detallado de ingresos y egresos manuales con justificación.
- **Arqueo Ciego de Cierre:** El cajero ingresa el conteo físico de gaveta sin conocer el cálculo del sistema para evitar sesgos, determinando faltantes o sobrantes auditados.

### 👥 4. Clientes, Créditos y Cartera
- Base de datos de clientes con diferenciación entre persona Natural y Jurídica (NIT / Cédula).
- Cliente predeterminado homologado ante la DIAN: `CONSUMIDOR FINAL (222222222222)`.
- Límites y cupos de crédito asignados por cliente con plazos en días.
- Registro de cuentas por cobrar, abonos parciales, recibos de caja y cartera vencida.

### 🚚 5. Multisucursal y Traslados entre Sedes
- Gestión de múltiples puntos de venta físicos bajo una misma empresa.
- **Módulo de Traslados de Inventario:** Solicitud, despacho y confirmación de recepción de mercancía entre sedes con actualización atómica de existencias.
- Reporte consolidado comparativo de ventas y stock matriz multisucursal.

### 🧾 6. Facturación Electrónica DIAN (Arquitectura Desacoplada)
- Desacoplamiento conceptual entre el hecho de la Venta y la emisión del Documento Fiscal.
- Transmisión asíncrona mediante colas de trabajo para garantizar que la lentitud o caídas de los servidores de la DIAN nunca detengan la fila de clientes en caja.

### 🎨 7. Identidad Visual y Personalización Temática
- **Logotipo de la Empresa:** Carga de imagen PNG/JPG/SVG desde el perfil de la empresa con previsualización en vivo. Se muestra en el Sidebar lateral, el Header superior y los comprobantes.
- **Color Temático de la Plataforma:** El administrador puede seleccionar el color de la interfaz entre 8 opciones predefinidas (Índigo, Azul, Esmeralda, Violeta, Carmín, Naranja, Ámbar o Slate), adaptando acentos, menús y botones automáticamente.

### 📊 8. Reportes, Analítica y Auditoría
- Informes de ventas por período, usuario cajero, método de pago y producto más vendido.
- Reportes de utilidad bruta y márgenes de ganancia.
- Bitácora inmutable de auditoría para operaciones sensibles (eliminaciones, anulaciones y cambios de configuración).

---

## 🧰 Stack Tecnológico

| Capa | Tecnología |
|---|---|
| **Backend** | PHP 8.2+ / Laravel 12 |
| **Frontend** | Blade, Livewire, Alpine.js, Tailwind CSS v4 |
| **Base de Datos** | MySQL 8.x (producción) / SQLite (entorno local) |
| **Autenticación & API** | Laravel Sanctum con tokens personales REST `/api/v1` |
| **Roles y Permisos** | `spatie/laravel-permission` con segregación por empresa |
| **Compilador de Assets** | Vite 6 |
| **Colas y Tareas** | Laravel Queue (`database` / `redis`) |

---

## 🚀 Puesta en Marcha Rápida

### 1. Clonar el proyecto
```bash
git clone https://github.com/MrCondeJV/sys-POS.git
cd sys-POS
```

### 2. Instalar dependencias PHP y Node
```bash
composer install
npm install
```

### 3. Configurar el archivo de entorno `.env`
```bash
cp .env.example .env
php artisan key:generate
```

Configura tu conexión a MySQL en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_db
DB_USERNAME=root
DB_PASSWORD=tu_contrasena
```

### 4. Crear enlace simbólico para imágenes y logos
```bash
php artisan storage:link
```

### 5. Ejecutar migraciones con datos demo
```bash
php artisan migrate:fresh --seed
```

### 6. Compilar assets y levantar el servidor
```bash
# Compilar frontend
npm run build

# Iniciar servidor local
php artisan serve
```

Ingresa en tu navegador a: **[http://localhost:8000](http://localhost:8000)** e inicia sesión con cualquiera de los [Usuarios de Prueba](#-usuarios-de-prueba-para-demostración).

---

## ⚙️ Comandos de Utilidad

```bash
# Ejecutar servidor con recarga en vivo de frontend (HMR)
npm run dev

# Ejecutar el seeder específico de ferretería en cualquier momento
php artisan db:seed --class=FerreteriaDemoSeeder

# Procesar cola de eventos y facturas en segundo plano
php artisan queue:work

# Limpiar todas las cachés
php artisan optimize:clear
```

---

## 📄 Licencia

Este proyecto se distribuye bajo la licencia **[MIT](LICENSE)**. Desarrollado con estándares de alta concurrencia e integridad transaccional para el comercio colombiano.
