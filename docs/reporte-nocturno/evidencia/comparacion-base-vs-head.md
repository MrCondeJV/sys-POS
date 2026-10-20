# Comparación de Suite de Pruebas: Base (`c007231` / `pre-fase-4`) vs `HEAD`

## Resumen Ejecutivo
- **Commit previo a cambios de diseño:** `c007231` (*Fase 30: Pulido visual corporativo completo y mejoras de UI*)
- **Tag `pre-fase-4`:** `2d6a2ae` (*feat(design-system): completar fase 3 de componentes blade y script global check:styles*)
- **Estado tras Lote 3A (`b57a8c7`):** 295 pasadas, 1 fallada, 0 omitidas (1103 aserciones).
- **Estado tras corrección de regresión en Dashboard y pruebas Parte 1B (`1d30ace`+):** 299 pasadas, 2 `known-bugs` marcados como `incomplete` (`BUG-001`, `BUG-002`), 0 fallos reales.

## Tabla Comparativa de Suites y Fallos

| Suite / Caso de Prueba | Estado en Base (`c007231`) | Estado en `pre-fase-4` (`2d6a2ae`) | Estado en `b57a8c7` (Lote 3A) | Estado en `noche-checkpoint-1B` | Clasificación y Causa |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `Tests\Feature\Fase4\ProductoTest > dashboard renderiza metricas y articulos del tenant` | **PASS** | **PASS** | **FAIL** | **PASS** | **(b) Regresión menor de copy en rediseño (Corregida):** La prueba verificaba `$response->assertSee('Estado de Artículos en Catálogo')` (`ProductoTest.php:368`). En Lote 1 (`90b8326`), el título de la tarjeta en `dashboard.blade.php` se había cambiado a `'Artículos con Stock Crítico' / 'Artículos Recientes en Catálogo'`. Se restauró `title="Estado de Artículos en Catálogo"` en `dashboard.blade.php` sin tocar el test. |
| Resto de suites originales (285 tests en `Unit` + `Feature`) | **PASS** (285/285) | **PASS** (285/285) | **PASS** (285/285) | **PASS** (285/285) | Sin regresiones funcionales ni visuales en las pruebas existentes. |
| `Tests\Feature\DesignSystem\ComponentTest` (9 tests) | N/A | **PASS** (9/9) | **PASS** (9/9) | **PASS** (9/9) | Suite de componentes Blade creada en Fase 3. |
| `Tests\Feature\Seguridad\ModalConsequencesVerificationTest` (5 tests) | N/A | N/A | N/A | **3 PASS, 2 KNOWN-BUG** | Nuevas pruebas de la Parte 1B verificando consecuencias reales de modales, comprobante de cierre e integridad de rutas en cajas. |
