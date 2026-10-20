# Preguntas y Decisiones Pendientes para el Usuario (`PREGUNTAS-PENDIENTES.md`)

A continuación se listan las decisiones de negocio, infraestructura y arquitectura que requieren tu confirmación por la mañana:

1. **Aplicación del parche `BUG-001` (Empresas en estado `INACTIVO`):**
   - Actualmente, suspender una empresa desde el panel Super Admin no impide que sus usuarios inicien sesión ni operen.
   - ¿Autorizas aplicar `docs/reporte-nocturno/parches-propuestos/BUG-001.patch` en `SetCompanyContext` y `LoginController` (y actualizar entonces el texto del modal en `empresas/index.blade.php`)?

2. **Política sobre correos de usuarios eliminados (`BUG-002`):**
   - Cuando se aplica `soft delete` a un usuario, ¿prefieres la **Opción A** (mantener el email reservado por auditoría y quitar `->whereNull('deleted_at')` de `Rule::unique` para mostrar error 422 en vez de 500, según `BUG-002.patch`) o la **Opción B** (permitir reutilizar el email mediante migración de índice / sufijo al eliminar)?

3. **Formateo global con Laravel Pint:**
   - Existen 62 archivos PHP legados en `app/`, `routes/` y `database/` con detalles de formato preexistentes. ¿Deseas ejecutar `./vendor/bin/pint` en un commit exclusivo de estilo para quitar `--dirty` de `composer test`?

4. **Motor de Base de Datos en Producción (Auditoría Parte 2 / Concurrencia):**
   - `.env.example` configura `DB_CONNECTION=sqlite` por defecto, pero `config/database.php` soporta MySQL/MariaDB y PostgreSQL, y las acciones transaccionales usan `lockForUpdate()`.
   - ¿Cuál será el motor definitivo en producción (MySQL 8 / MariaDB / PostgreSQL) para ajustar el servicio de CI y las pruebas de bloqueo de fila?

5. **Definiciones de Negocio para Integración con Wompi Colombia (Parte 4 — Fase B0):**
   - ¿Cuáles son los precios mensuales/anuales en COP de cada plan (`BASICO`, `PROFESIONAL`, `EMPRESARIAL`) y si incluyen IVA?
   - ¿Cuántos días de período de gracia (`dias_gracia`) deseas otorgar tras un fallo de cobro antes de pasar la suscripción a `VENCIDA` / restringir escritura (recomendado en `DISENO-B0.md`: 5 días con 3 reintentos)?
   - Cuando una suscripción vence, ¿el tenant entra en modo **solo lectura** (puede consultar/exportar reportes y pagar suscripción, pero no abrir caja ni vender) o **bloqueo total**?

6. **Aplicación del parche `BUG-006` (Protección de `GET /empresa/perfil` y `GET /sucursales`):**
   - Actualmente cajeros y vendedores pueden ver la vista de configuración fiscal y lista de sucursales (aunque los métodos POST/PUT bloquean con 403).
   - ¿Autorizas aplicar `docs/reporte-nocturno/parches-propuestos/BUG-006.patch` para restringir el acceso con `Gate::authorize('configuracion.editar')` y `Gate::authorize('sucursales.ver')`?

7. **Aplicación del parche `BUG-007-008` (Límites de plan y suscripciones vencidas):**
   - Actualmente `UserController@store` no comprueba `$empresa->puedeCrearUsuario()`, y `Empresa::puedeCrearSucursal()` / `puedeCrearUsuario()` retornan `true` si la suscripción está vencida o suspendida.
   - ¿Autorizas aplicar `docs/reporte-nocturno/parches-propuestos/BUG-007-008-limites-plan-y-suscripcion.patch`?

8. **Rutas `show` no implementadas (`BUG-005`):**
   - En `routes/web.php`, `Route::resource('productos')` y `Route::resource('clientes')` registran `productos.show` y `clientes.show`, pero sus controladores carecen del método `show()`.
   - ¿Deseas añadir `->except(['show'])` en `routes/web.php` o prefieres implementar vistas de ficha técnica detallada para cada uno?
