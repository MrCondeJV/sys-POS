# Auditoría de Recursos Externos (CDNs) y URLs Fijas en Vistas Blade

## 1. Recursos Externos (`<script src="https://...">`, `<link href="https://...">`, CDNs)

Escaneo completo realizado sobre los 108 archivos en `resources/views/**/*.blade.php`:

| Archivo | Línea | Recurso Externo Detectado | Estado / Acción |
| :--- | :---: | :--- | :--- |
| `resources/views/cajas/comprobante_cierre.blade.php` | — | Ninguno (usa `@vite`) | **Cumplido en Lote 3A** (verificado con test automático). |
| `resources/views/ventas/ticket.blade.php` | 7 | `<script src="https://cdn.tailwindcss.com"></script>` | Se reemplaza por `@vite` en **Lote 4 (Ventas)**. |
| `resources/views/cartera/estado_cuenta_print.blade.php` | 7 | `<script src="https://cdn.tailwindcss.com"></script>` | Se reemplaza por `@vite` en **Lote 6 (Cartera)**. |
| `resources/views/cartera/recibo.blade.php` | 7 | `<script src="https://cdn.tailwindcss.com"></script>` | Se reemplaza por `@vite` en **Lote 6 (Cartera)**. |
| `resources/views/layouts/app.blade.php` | 9–10 | `<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />` | Fuente tipográfica Inter con fallback local `system-ui, -apple-system, sans-serif` definido en `--font-sans` de `app.css`. |
| `resources/views/layouts/guest.blade.php` | 9–10 | `<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />` | Mismo fallback seguro `system-ui` si no hay internet. |

## 2. URLs Concatenadas en Duro en JavaScript / Alpine (`'/ruta/' + id`)

Escaneo completo en `resources/views/**/*.blade.php`:

| Archivo | Línea | Patrón Original | Estado / Corrección |
| :--- | :---: | :--- | :--- |
| `resources/views/cajas/index.blade.php` | 241, 297 | `'/cajas/' + cajaParaAbrir.id + '/abrir'` y `'/movimiento'` | **Corregido en Parte 1B:** `route('cajas.abrir', '__CAJA__')` y `route('cajas.movimiento', '__CAJA__')` con `.replace('__CAJA__', ...)`. |
| `resources/views/pos/sin_caja.blade.php` | 77 | `'/cajas/' + (cajaSeleccionada ? cajaSeleccionada.id : '') + '/abrir'` | Se corrige en **Lote 3B (POS)** con `route('cajas.abrir', '__CAJA__')`. |
| `resources/views/cartera/index.blade.php` | 21 | `urlAbono: '/cartera/' + cuenta.id + '/abono'` | Se corrige en **Lote 6 (Cartera)** con `route('cartera.abono', '__CUENTA__')`. |
| `resources/views/proveedores/index.blade.php` | 46 | `this.formAction = '/proveedores/' + item.id;` | Se corrige en **Lote 7 (Compras/Proveedores)** con `route('proveedores.update', '__PROVEEDOR__')`. |
| `resources/views/sucursales/index.blade.php` | 31 | `this.formAction = '/sucursales/' + item.id;` | Se corrige en **Lote 9 (Resto)** con `route('sucursales.update', '__SUCURSAL__')`. |
