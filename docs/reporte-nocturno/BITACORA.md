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
