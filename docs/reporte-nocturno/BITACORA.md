# Bitácora de Ejecución Nocturna Autónoma (`BITACORA.md`)

- **Rama de trabajo:** `noche/auditoria-y-rediseno`
- **Entorno:** Windows / PowerShell, PHP 8.4.26 (`pdo_sqlite` activo), Node.js, Google Chrome Headless disponible.

---

## [2026-10-19 22:22 -05:00] Inicio y Parte 1 — Desbloquear y Medir (`noche-checkpoint-0`)
- **Acciones:**
  - Creación de rama `noche/auditoria-y-rediseno` desde `1d30ace` y tag inicial `noche-checkpoint-0`.
  - Respaldo de `database/database.sqlite` en `docs/reporte-nocturno/backup/database.sqlite.bak` y adición de `/docs/reporte-nocturno/backup` a `.gitignore`.
  - Verificación de `composer install` (`phpunit 11.5.56`, `laravel/pint 1.30.4`) y ejecución completa de `php artisan test`.
  - Comparación contra `pre-fase-4` (`2d6a2ae`) y commit previo al rediseño (`c007231`).
  - Único fallo detectado en `b57a8c7` (`ProductoTest > dashboard renderiza metricas y articulos del tenant`) corregido restaurando `title="Estado de Artículos en Catálogo"` en `resources/views/dashboard.blade.php`.
- **Evidencia generada:**
  - `docs/reporte-nocturno/evidencia/phpunit-parte1-head.txt`
  - `docs/reporte-nocturno/evidencia/comparacion-base-vs-head.md`

---

## [2026-10-19 22:34 -05:00] Parte 1B — Cierre de Pendientes del Lote 3A (`noche-checkpoint-1B`)
- **Acciones:**
  1. **Pruebas de consecuencias de modales (`tests/Feature/Seguridad/ModalConsequencesVerificationTest.php`):**
     - `1B.1(a)`: Confirmado que inactivar un usuario con sesión abierta lo expulsa en su siguiente petición HTTP (`SetCompanyContext`).
     - `1B.1(b)`: Reproducido `BUG-001` (Severidad Alta: usuario de `Empresa` en estado `INACTIVO` puede iniciar sesión y operar). Marcado con `#[Group('known-bugs')]` + `markTestIncomplete` y creado parche sin aplicar en `docs/reporte-nocturno/parches-propuestos/BUG-001.patch`.
     - `1B.1(c)`: Reproducido `BUG-002` (Severidad Media: colisión con índice único físico `users_email_unique` al reutilizar email de usuario con `soft delete`). Marcado con `#[Group('known-bugs')]` + `markTestIncomplete` y creado parche en `docs/reporte-nocturno/parches-propuestos/BUG-002.patch`.
  2. **Comprobante de cierre (`cajas/comprobante_cierre.blade.php`) y auditoría de CDNs:**
     - Prueba automática `test_comprobante_cierre_caja_renderiza_sin_urls_externas_y_con_reglas_print` pasando (0 URLs externas, `@media print`, `size: letter portrait` y `size: 80mm auto`).
     - Generados PDFs reales con Chrome Headless en `docs/reporte-nocturno/evidencia/impresion/comprobante-cierre-carta.pdf` y `comprobante-cierre-80mm.pdf`.
     - Inventario de CDNs y URLs fijas documentado en `docs/reporte-nocturno/evidencia/auditoria-cdns-y-urls-fijas.md`.
  3. **Rutas dinámicas en modales de caja (`cajas/index.blade.php`):**
     - Reemplazadas URLs concatenadas por `route('cajas.abrir', '__CAJA__')` y `route('cajas.movimiento', '__CAJA__')` y verificadas con `test_modales_de_cajas_usan_rutas_nombradas_y_endpoints_funcionan`.
  4. **Calidad y CI:**
     - Configurado script `composer test` en `composer.json` y creado workflow `.github/workflows/ci.yml` (incluyendo job de concurrencia con servicio MySQL 8.0).
  5. **Evidencia visual (capturas Chrome Headless):**
     - Generadas 10 capturas PNG en `docs/reporte-nocturno/evidencia/capturas/` usando BD SQLite en memoria (`:memory:`).

---

## [2026-10-19 22:48 -05:00] Parte 2 — Auditoría y Pruebas Intensivas A0–A9 (`noche-checkpoint-2`)
- **Acciones:**
  - **A0:** Documentación de cobertura (`docs/reporte-nocturno/evidencia/cobertura-a0.md`) e invariantes formales por módulo (`docs/reglas-de-negocio.md`).
  - **A1:** Workflow `.github/workflows/ci.yml` y script `composer test`.
  - **A2/A4/A8:** Verificación de invariantes financieros, Kardex, descuentos, impuestos, cupo de cartera y sincronización offline POS.
  - **A3 (Prioritaria):** Creada y ejecutada `tests/Feature/AuditoriaNocturna/BarridoMultiempresaRutasTest.php` (39 aserciones cubriendo productos, catálogos, inventario, clientes, proveedores, sucursales, cajas, sesiones, ventas, compras, cartera, devoluciones, listas de precios, impuestos, resoluciones DIAN, traslados y usuarios). Detectado `BUG-005`.
  - **A5 (Concurrencia):** Creada `tests/Concurrency/BloqueoConcurrenciaRealTest.php` (`#[Group('concurrency')]`) conectada al servicio MySQL 8.0 en CI y omitida con mensaje explícito en SQLite local.
  - **A6 (Prioritaria):** Creada y ejecutada `tests/Feature/AuditoriaNocturna/MatrizRolesPermisosRutasTest.php` (134 aserciones verificando `Guest`, `SUPER_ADMIN` sin/con negocio, `ADMIN_EMPRESA`, `CAJERO`, `VENDEDOR` y `CONTADOR`). Detectado `BUG-006` y generado parche `BUG-006.patch`.
  - **A7 (E2E):** Creados `tests/E2E/README.md` y `tests/E2E/pos-flujo-critico.spec.ts`.
  - **A9 (Integridad, Mutación y Carga):** Creado comando de solo lectura `php artisan sistema:verificar-integridad` (`app/Console/Commands/VerificarIntegridadSistema.php`) con su suite `tests/Feature/AuditoriaNocturna/VerificarIntegridadCommandTest.php` (2 tests, 4 aserciones), más `infection.json5` y `tests/Load/k6-pos-load.js`.

---

## [2026-10-19 23:30 -05:00] Parte 3 / Lote 4 — Migración de `ventas/*` (`noche-checkpoint-lote4`)
- **Vistas migradas:** `ventas/index.blade.php`, `ventas/show.blade.php`, `ventas/cotizaciones.blade.php`.
- **Acciones:**
  - Sustitución de colores no corporativos y alias `.card-banking`, `.btn-*-banking`, `.input-base`, `.table-corporate` por utilidades Tailwind de tokens institucionales (`bg-surface-card`, `border-border-subtle`, `text-text-primary`, `text-text-muted`, `bg-brand-600`, `hover:bg-brand-700`).
  - Estandarización de badges con `<x-badge>` y `<x-status-badge>`.
  - Reemplazo de botones y estilos de impresión con clases `@media print` semánticas.
  - Ejecución de build y suite de pruebas (`310 passed`).

---

## [2026-10-20 00:15 -05:00] Parte 3 / Lote 5 — Migración de `productos/*`, `inventario/*`, `traslados/*` (`noche-checkpoint-lote5`)
- **Vistas migradas:**
  - `productos/index.blade.php`, `productos/create.blade.php`, `productos/edit.blade.php`, `productos/show.blade.php`, `productos/importar.blade.php`.
  - `inventario/index.blade.php`, `inventario/kardex.blade.php`, `inventario/ajuste.blade.php`.
  - `traslados/index.blade.php`, `traslados/create.blade.php`, `traslados/show.blade.php`.
- **Acciones:**
  - Reemplazo sistemático de badges manuales y bordes hex literales.
  - Formateo monetario uniforme con tabular-nums en tablas y formularios de catálogo e inventario.
  - Reemplazo de confirmaciones nativas por `<x-modal-confirm>`.
  - Verificación con `npm run check:styles` y `php artisan test` (`310 passed`).

---

## [2026-10-20 01:00 -05:00] Parte 3 / Lote 6 — Migración de `cartera/*`, `clientes/*` (`noche-checkpoint-lote6`)
- **Vistas migradas:**
  - `cartera/index.blade.php`, `cartera/show.blade.php`.
  - `clientes/index.blade.php`, `clientes/create.blade.php`, `clientes/edit.blade.php`.
- **Acciones:**
  - Migración de modales de abono a cartera a rutas nombradas Blade `route('cartera.abono', '__CUENTA__')`.
  - Eliminación de colores `indigo`, `rose`, `teal`, `sky` y gradientes en tarjetas de saldos y cupos de crédito.
  - Indicadores de estado de cuenta y semáforos de mora alineados con tokens `success`, `warning` y `danger`.

---

## [2026-10-20 01:45 -05:00] Parte 3 / Lote 7 — Migración de `compras/*`, `proveedores/*` (`noche-checkpoint-lote7`)
- **Vistas migradas:**
  - `compras/index.blade.php`, `compras/create.blade.php`, `compras/show.blade.php`.
  - `proveedores/index.blade.php`, `proveedores/create.blade.php`, `proveedores/edit.blade.php`.
- **Acciones:**
  - Corrección de modal de eliminación en `proveedores/index.blade.php` con `route('proveedores.destroy', '__PROVEEDOR__')`.
  - Unificación visual de selectores de proveedores, cálculo de subtotales, retenciones e impuestos.
  - Migración de confirmaciones de compras y recepción de mercancía a modales accesibles.

---

## [2026-10-20 02:30 -05:00] Parte 3 / Lote 8 — Migración de `reportes/*` (`noche-checkpoint-lote8`)
- **Vistas migradas:**
  - Vistas interactivas: `reportes/index.blade.php`, `reportes/ventas.blade.php`, `reportes/inventario.blade.php`, `reportes/financiero.blade.php`, `reportes/cajas.blade.php`, `reportes/clientes.blade.php`, `reportes/utilidades.blade.php`.
  - Vistas de impresión: `reportes/ventas_imprimir.blade.php`, `reportes/inventario_imprimir.blade.php`, `reportes/financiero_imprimir.blade.php`, `reportes/cajas_imprimir.blade.php`, `reportes/clientes_imprimir.blade.php`, `reportes/utilidades_imprimir.blade.php`.
- **Acciones:**
  - Migración exhaustiva de tarjetas analíticas, barras de filtro y tablas de exportación.
  - Verificación de las 6 vistas de impresión: aislamiento de recursos, tipografía monospace/tabular para cifras financieras, estilos de salto de página (`break-inside-avoid`, `@page`) y eliminación de cualquier referencia externa.

---

## [2026-10-20 03:30 -05:00] Parte 3 / Lote 9 — Migración del Resto de Módulos (`noche-checkpoint-lote9`)
- **Módulos y vistas migradas:**
  - `sucursales/` (`index.blade.php`, `create.blade.php`, `edit.blade.php`).
  - `catalogos/` (`categorias/`, `marcas/`, `unidades/`).
  - `impuestos/` (`index.blade.php`, `create.blade.php`, `edit.blade.php`).
  - `listas-precios/` (`index.blade.php`, `create.blade.php`, `edit.blade.php`).
  - `devoluciones/` (`index.blade.php`, `create.blade.php`, `show.blade.php`).
  - `documentos/` (`index.blade.php`, `resoluciones_dian/`).
  - `facturacion-electronica/` (`index.blade.php`, `configuracion.blade.php`).
  - `notificaciones/` (`index.blade.php`).
  - `auditoria/` (`index.blade.php`).
  - `impresion/` (`configuracion.blade.php`).
  - `ferreteria/` (`conversion-unidades.blade.php`).
  - `auth/` (`login.blade.php`, `register.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`).
- **Acciones:**
  - Reemplazo de formularios y modales de configuración fiscal y catálogos secundarios.
  - Estandarización de modales con rutas nombradas Blade en `sucursales/index.blade.php`.
  - Aseguramiento de estilos en vistas de autenticación respetando los tokens institucionales.

---

## [2026-10-20 04:30 -05:00] Parte 3 / Lote 3B — Migración del POS y Retiro de Alias Temporales (`noche-checkpoint-lote3B`)
- **Vistas migradas:** `pos/index.blade.php`, `pos/sin_caja.blade.php`.
- **Archivo de estilos limpiado:** `resources/css/app.css`.
- **Acciones:**
  - Migración visual meticulosa del encabezado, buscador de productos, panel numérico, grid de categorías, selector de clientes y panel de totales en `pos/index.blade.php`.
  - **Inviolabilidad de JavaScript:** Verificado con `git diff -U0 resources/views/pos/index.blade.php` que la función `posTerminal(config)` y todas sus rutinas JS internas (atajos de teclado, IndexedDB, sincronización, escáner de código de barras) se mantuvieron 100% intactas.
  - Corrección en `pos/sin_caja.blade.php` de modal de apertura de caja usando `route('cajas.abrir', '__CAJA__')`.
  - Retiro definitivo de todas las clases alias temporales en `resources/css/app.css` (`.input-base`, `.input-select`, `.table-corporate`, `.card-banking`, `.btn-*-banking`, `.kpi-card`).
  - Ejecución de `npm run check:styles`: 0 alias en uso, 0 clases dinámicas no compiladas.

---

## [2026-10-20 05:15 -05:00] Parte 4 — Wompi Fase B0, Auditoría de Planes/Suscripciones y Cierre (`noche-checkpoint-4`)
- **Acciones:**
  1. **Documentación Wompi Colombia (Fase B0):**
     - Creado documento exhaustivo `docs/wompi/DISENO-B0.md` estructurado en 7 secciones: Modelo de Datos, Arquitectura de Integración, Firma de Integridad, Webhooks & Idempotencia, Máquina de Estados, Política de Reintentos y Fallbacks, y Plan de Migración/Activación por Fases.
  2. **Auditoría de Suscripciones y Límites de Plan:**
     - Creada suite `tests/Feature/AuditoriaNocturna/LimitesPlanYSuscripcionTest.php` (4 tests: 2 pasando, 2 marcados como known-bugs).
     - Identificado `BUG-007`: `UserController@store` no evalúa `$empresa->puedeCrearUsuario()`.
     - Identificado `BUG-008`: `Empresa::suscripcionActual()` retorna `null` para estados `VENCIDA` o `SUSPENDIDA`, provocando fallback a `return true;` (recursos ilimitados) en `puedeCrearSucursal()` y `puedeCrearUsuario()`.
     - Generado parche propuesto en `docs/reporte-nocturno/parches-propuestos/BUG-007-008-limites-plan-y-suscripcion.patch`.
  3. **Verificación y Calidad Final:**
     - `npm run build`: compilación limpia en 315 ms (`public/build/assets/app-*.css` y `app-*.js`).
     - `npm run check:styles`: **795/795 clases estáticas presentes en CSS compilado (100%)**, 0 alias temporales en uso, 0 clases dinámicas Blade.
     - `php artisan sistema:verificar-integridad`: **0 anomalías detectadas** (11/11 verificaciones OK).
     - Grep de patrones prohibidos: **0 coincidencias** en las 108 vistas Blade del proyecto.
     - `php artisan test`: **313 passed (1314 assertions)**, 0 failed, 3 incomplete (`known-bugs`), 2 skipped (`known-bugs`).
  4. **Documentación y Cierre:**
     - Registro actualizado en `BUGS.md`.
     - Creado informe ejecutivo final en `docs/reporte-nocturno/INFORME-FINAL.md`.
     - Creación de commit y tag `noche-checkpoint-4`.
