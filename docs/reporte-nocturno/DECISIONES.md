# Registro de Decisiones Autónomas (`DECISIONES.md`)

Cada decisión tomada durante la ejecución autónoma nocturna sigue el criterio conservador y reversible establecido en las Reglas de Seguridad.

---

## DEC-001: Manejo de `BUG-001` (Acceso de usuarios cuya Empresa está en estado `INACTIVO`)
- **Contexto:** La prueba `ModalConsequencesVerificationTest` demostró que suspender una empresa (`estado = INACTIVO`) no impide que sus usuarios activos inicien sesión u operen.
- **Alternativas:**
  1. Modificar `SetCompanyContext` y `LoginController` inmediatamente para expulsar y bloquear usuarios de empresas inactivas.
  2. No tocar lógica de negocio (Regla 5 y directriz explícita de Parte 1B.1b), marcar la prueba con `#[Group('known-bugs')]` + `markTestIncomplete`, ajustar el texto del modal en `empresas/index.blade.php` para que no prometa un bloqueo inexistente, y dejar el parche listo en `docs/reporte-nocturno/parches-propuestos/BUG-001.patch`.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Cumple estrictamente la Regla 5 ("Si una prueba revela un bug de severidad Alta o Crítica, NO lo corrijas: deja la prueba que lo reproduce marcada como `known bug` y guarda un parche propuesto").

---

## DEC-002: Manejo de `BUG-002` (Colisión de índice único `users_email_unique` tras `soft delete`)
- **Contexto:** `UserController@store` valida `Rule::unique('users', 'email')->whereNull('deleted_at')`, pero la tabla `users` tiene un índice único simple sobre `email`, causando `UniqueConstraintViolationException` al reutilizar el correo de un usuario eliminado.
- **Alternativas:**
  1. **Opción A (Validación estricta):** Quitar `->whereNull('deleted_at')` en `UserController@store` y `update` para que Laravel devuelva un error de validación 422 amigable indicando que el correo ya está registrado en el historial.
  2. **Opción B (Permitir reutilización en BD):** Crear una migración que cambie el índice único de `email` o modifique el email al hacer soft-delete.
- **Decisión tomada:** No tocar el controlador ni las migraciones durante la noche; registrar `BUG-002` con su prueba `known-bug`, dejar el parche de la Opción A en `docs/reporte-nocturno/parches-propuestos/BUG-002.patch` y documentar ambas opciones en `PREGUNTAS-PENDIENTES.md`. Además, se ajustó el texto del modal de eliminación en `usuarios/index.blade.php` para advertir que el email queda reservado.

---

## DEC-003: Alcance de `pint --test` en `composer test` y CI
- **Contexto:** Al instalar `laravel/pint` y correr `./vendor/bin/pint --test` sobre todo el repositorio, 62 archivos PHP legados en `app/`, `database/`, `routes/` y `tests/` (no tocados por el rediseño visual) presentaron diferencias de estilo previas.
- **Alternativas:**
  1. Ejecutar `./vendor/bin/pint` masivamente modificando 62 archivos de backend (`app/Actions`, `app/Http/Controllers`, etc.).
  2. Configurar `@php vendor/bin/pint --test --dirty` en `composer test` y formatear con Pint únicamente los archivos PHP de pruebas creados/modificados en esta rama, evitando contaminar el diff con 62 archivos de lógica de negocio que las reglas prohíben tocar.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Respeta la prohibición de no modificar controladores/acciones/rutas y mantiene `composer test` funcional. El usuario puede autorizar un commit dedicado de formateo global (`./vendor/bin/pint`) por la mañana.

---

## DEC-004: Generación de PDFs y Capturas Visuales sin tocar `.env` ni `database.sqlite`
- **Contexto:** La Regla 3 prohíbe tocar `.env` o cualquier base de datos real (`database/database.sqlite`). La Parte 1B.2 y 1B.5 solicitan PDFs de impresión y capturas de pantalla con datos sembrados.
- **Alternativas:**
  1. Levantar `php artisan serve` contra `database/database.sqlite` y sembrar datos allí.
  2. Renderizar las vistas desde un arnés PHP con `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` (base de datos 100% en memoria RAM) e invocar Google Chrome Headless (`--headless=new --print-to-pdf` y `--screenshot`) para generar los PDFs y PNGs reales.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Garantiza **cero impacto** sobre `database/database.sqlite` y `.env`, a la vez que produce evidencia real renderizada por el motor Blink de Google Chrome en `docs/reporte-nocturno/evidencia/impresion/` y `docs/reporte-nocturno/evidencia/capturas/`.

---

## DEC-005: Inviolabilidad del Motor JavaScript del POS (`posTerminal()`) en el Lote 3B
- **Contexto:** Las reglas del prompt maestro y las condiciones de desbloqueo exigieron expresamente que la función `posTerminal(config)` y su lógica interna en `resources/views/pos/index.blade.php` no sufrieran alteraciones.
- **Alternativas:**
  1. Refactorizar el script de Alpine para integrar componentes adicionales.
  2. Migrar únicamente el marcado Blade/HTML periférico (encabezado corporativo, tarjetas de cobro, badges de estado, panel numérico y modales) manteniendo el bloque `<script>function posTerminal(config)` con exactamente 0 líneas de diferencia en git diff.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Verificado mediante `git diff -U0 resources/views/pos/index.blade.php`: el diff no contiene una sola línea dentro del bloque de script. Toda la lógica de atajos, IndexedDB, sincronización y escáner opera sin modificaciones.

---

## DEC-006: Manejo de `BUG-007` y `BUG-008` (Límites de Plan y Suscripciones)
- **Contexto:** La auditoría de la Parte 4 detectó que `UserController@store` no verifica `$empresa->puedeCrearUsuario()` y que `Empresa::suscripcionActual()` retorna `null` cuando la suscripción está vencida o suspendida, permitiendo operaciones y creación de recursos sin límite.
- **Alternativas:**
  1. Modificar `app/Http/Controllers/UserController.php` y `app/Models/Empresa.php` de inmediato.
  2. Cumplir estrictamente la Regla 5: caracterizar ambos hallazgos con pruebas automatizadas (`LimitesPlanYSuscripcionTest.php`) marcadas con `#[Group('known-bugs')]`, redactar la arquitectura correctiva en `docs/wompi/DISENO-B0.md` y entregar el parche listo en `docs/reporte-nocturno/parches-propuestos/BUG-007-008-limites-plan-y-suscripcion.patch`.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Preserva la estabilidad del backend y permite al usuario revisar y aprobar las reglas exactas de bloqueo de escritura antes de aplicarlas.

---

## DEC-007: Alcance Estrictamente Documental de Wompi Colombia (Fase B0)
- **Contexto:** La Parte 4 del prompt maestro estipuló que la integración con la pasarela de pagos Wompi Colombia debía realizarse en fase de diseño documental (Fase B0), prohibiendo cualquier transacción de dinero real o llamada HTTP a endpoints externos de pago.
- **Alternativas:**
  1. Instalar SDKs externos de pasarelas o crear migraciones y controladores parciales.
  2. Diseñar la arquitectura completa en `docs/wompi/DISENO-B0.md` (modelo relacional de 4 tablas, generación de firmas SHA-256 de integridad, webhook idempotente, máquina de estados y plan de migración por fases B1–B4), sin alterar archivos productivos ni realizar llamadas de red.
- **Decisión tomada:** **Alternativa 2.**
- **Razón:** Se respeta la prohibición de llamadas externas y se entrega un plan de ingeniería exhaustivo y listo para implementación inmediata tras la aprobación matutina.
