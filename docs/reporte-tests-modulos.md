# Reporte de errores encontrados en los tests de módulos

- **Fecha:** 2026-10-29
- **Resultado final:** los 9 errores encontrados en las dos revisiones están corregidos y la suite completa pasa (416 tests, 1724 aserciones).

| Parte | # | Error | Severidad |
|---|---|---|---|
| 1 | 1 | Kardex con saldo, operador y referencia incorrectos | Alta |
| 1 | 2 | Cualquier usuario podía administrar las resoluciones DIAN | Alta |
| 2 | 3 | Siete módulos sin control de permisos (traslados, presentaciones, reporte multisucursal, facturación electrónica, impresión de cierre de caja, suscripción, sincronización offline) | Alta |
| 2 | 4 | El comprobante impreso del cierre de caja mostraba $0 en esperado y contado | Alta |
| 2 | 5 | El POS mostraba "UND" como unidad de todos los productos | Media |
| 2 | 6 | Las listas de precios mostraban siempre 0 precios específicos | Media |
| 2 | 7 | El estado de cuenta impreso salía sin nombre de la empresa | Media |
| 2 | 8 | La URL de detalle de un impuesto daba error 500 | Baja |
| 2 | 9 | El despliegue no compilaba el CSS/JS (menú mini y columnas rotas en producción) | Alta |

---

## Parte 1 — Revisión por módulos

Se crearon tests por módulo en `tests/Feature/Modulos/` sobre un escenario comercial completo (`Concerns/EscenarioComercial.php`), generado con las mismas Actions que usa la aplicación: empresa con dos sedes, compra, ventas de contado y crédito, abono, devolución, traslado, lista de precios, impuesto, resolución DIAN, factura electrónica y pago de suscripción.

| Archivo | Qué prueba |
|---|---|
| `ListadosConDatosTest` | Los listados cargan con datos reales (tabla y tarjetas móviles) |
| `DetallesYFormulariosTest` | Detalles, edición, formularios, tickets, comprobantes, XML y reportes |
| `FiltrosYBusquedaTest` | Buscadores y filtros con valores inválidos sin errores 500 |
| `ValidacionesFormulariosTest` | Formularios vacíos y datos imposibles devuelven errores de validación |
| `AislamientoEntreEmpresasTest` | Los datos de otra empresa no se ven ni se modifican; permisos del cajero |

Esta revisión encontró 2 errores.

### Error 1 — El kardex mostraba saldo, operador y referencia incorrectos

- **Severidad:** Alta (información de inventario incorrecta).
- **Estado:** ✅ Corregido.
- **Archivo:** `resources/views/inventario/kardex.blade.php`

#### Qué pasaba

En el kardex de cualquier producto, **todas** las filas mostraban:

- la columna "Saldo Sucursal" en `0.00`;
- el operador como "Sistema", aunque el movimiento lo hubiera hecho un usuario;
- sin referencia. Por ejemplo, la compra guarda "Factura de Compra #FC-MOD-001", pero no aparecía.

La página cargaba sin error, así que el problema pasaba desapercibido y el usuario veía información de inventario incorrecta.

#### Causa

La vista leía campos que no existen en el modelo `MovimientoInventario`. Eloquent devuelve `null` en silencio para atributos inexistentes, por eso no aparecía ningún error.

| La vista usaba | El modelo tiene |
|---|---|
| `$mov->usuario` | relación `user` |
| `$mov->saldo_resultante` | `stock_posterior` |
| `$mov->motivo` | `notas` |
| `$mov->referencia_tipo` / `$mov->referencia_id` | `referencia` |

#### Solución

- La vista ahora usa los campos reales: `user` para el operador, `stock_posterior` para el saldo, `referencia` como texto principal y `notas` como texto secundario.
- La referencia larga pasa a la siguiente línea en vez de cortarse.
- Se revisaron las otras vistas que usan `->usuario` (auditoría, recibos de cartera, dashboard) y sus modelos sí tienen esa relación, así que no tenían el mismo problema.

#### Verificación

Test `DetallesYFormulariosTest::test_kardex_muestra_saldo_operador_y_referencia_de_cada_movimiento`. Con una compra de +50, ventas de −4 y −10, una devolución de +1 y un traslado de −5, el kardex de la sede principal muestra el saldo final de **32**, el nombre de cada operador y la referencia de cada documento.

---

### Error 2 — Cualquier usuario de la empresa podía administrar las resoluciones DIAN

- **Severidad:** Alta (seguridad / cumplimiento fiscal).
- **Estado:** ✅ Corregido.
- **Archivos:** `routes/web.php`, `app/Http/Controllers/ResolucionFacturacionController.php`, `app/Policies/ResolucionFacturacionPolicy.php` (nuevo), `resources/views/facturacion-electronica/resoluciones/index.blade.php`

#### Qué pasaba

Un usuario con rol **cajero** podía crear, editar y eliminar resoluciones de facturación DIAN. Se comprobó así:

| Acción del cajero | Resultado antes de la corrección |
|---|---|
| Abrir el formulario de creación / edición | Permitido |
| Crear una resolución | **Creada** |
| Eliminar una resolución | **Eliminada** |
| Editar una resolución | Permitido |

Es decir, cualquier empleado podía cambiar prefijos, rangos o vigencias de la numeración autorizada por la DIAN.

#### Causa

Las rutas de resoluciones no tenían middleware de permisos y el controlador no autorizaba ninguna acción. Otros módulos, como `ImpuestoController`, sí lo hacen con `Gate::authorize`.

#### Solución

- **Nueva policy** `ResolucionFacturacionPolicy`:
  - **Ver** el listado requiere `documentos.ver` (coherente con el menú).
  - **Crear, editar y eliminar** requieren `empresa.gestionar`, que hoy solo tiene `ADMIN_EMPRESA`, y que la resolución pertenezca a la misma empresa.
  - Se usaron permisos existentes para no tener que resincronizar los roles de las empresas en producción.
- **El controlador** ahora llama a `Gate::authorize` en todas sus acciones.
- **El listado** oculta los botones *Nueva*, *Editar* y *Eliminar* a quien no tiene permiso, tanto en la tabla como en las tarjetas móviles.

En el mismo módulo también se corrigieron tres problemas relacionados:

| Problema | Solución |
|---|---|
| La URL `resoluciones/{id}` daba error 500 porque la ruta existía pero el controlador no tenía método `show` | Se quitó esa ruta (`->except('show')`) |
| El modal advierte "solo puede eliminarse si no tiene documentos emitidos", pero no se verificaba | La eliminación se bloquea, con mensaje de error, si la resolución tiene documentos electrónicos |
| Se podía asignar a una resolución una sede de otra empresa | La validación de `sucursal_id` solo acepta sedes de la propia empresa |

#### Verificación

| Test | Qué confirma |
|---|---|
| `AislamientoEntreEmpresasTest::test_cajero_no_puede_crear_editar_ni_eliminar_resoluciones_dian` | El cajero recibe 403 al crear, editar o eliminar, y la resolución no cambia |
| `ValidacionesFormulariosTest::test_admin_puede_crear_y_eliminar_resolucion_sin_documentos` | El administrador conserva el acceso |
| `ValidacionesFormulariosTest::test_resolucion_con_documentos_emitidos_no_se_puede_eliminar` | No se puede eliminar una resolución con documentos emitidos |
| `ValidacionesFormulariosTest::test_resolucion_no_acepta_sucursal_de_otra_empresa` | No se acepta una sede de otra empresa |

---

## Parte 2 — Revisión sistemática

Con los errores 1 y 2 corregidos, se buscaron más errores del mismo tipo con tres barridos sobre toda la aplicación, en vez de revisar módulo por módulo:

1. **Modo estricto de Eloquent** (`Model::preventAccessingMissingAttributes()`) en toda la suite: hace fallar cualquier lectura de un atributo que no existe, en vez de devolver `null` en silencio. Quedó **activado de forma permanente** en `tests/TestCase.php`, así que un error como el del kardex ya no puede pasar desapercibido.
2. **Análisis estático** de vistas y controladores: compara cada `->propiedad` contra las columnas reales de la base de datos y las relaciones de los modelos. Detecta los casos que el modo estricto no ve porque usan `?? 'valor'`.
3. **Recorrido de todas las rutas GET** (unas 130) con cada rol: administrador de empresa, administrador de sede, cajero, vendedor, contador y super admin con y sin empresa. Usa datos reales y no acepta errores 500 (`RecorridoRutasPorRolTest`).

Además, se revisó qué controladores no verifican permisos y se escribió un test por permiso esperado (`AutorizacionPorPermisoTest`).

Esta revisión encontró 6 errores (3 a 8). El error 9 no salió de los tests: se detectó al revisar un problema reportado en producción (el menú mini incompleto) y se incluye aquí porque se corrigió en la misma revisión.

### Error 3 — Siete módulos no verificaban permisos

- **Severidad:** Alta (seguridad).
- **Estado:** ✅ Corregido.

#### Qué pasaba

Estos controladores no tenían ningún control de permisos, así que cualquier usuario de la empresa podía usarlos. Por ejemplo, un cajero podía despachar, recibir o rechazar traslados de inventario entre sedes; crear o borrar presentaciones de producto, que definen precios de venta; y ver el reporte financiero consolidado de todas las sedes.

#### Solución

Cada acción exige ahora el permiso que le corresponde del catálogo `PermisoSistema`. El super admin conserva el acceso porque la verificación usa `can()`, que respeta su bypass.

| Módulo | Consultar | Modificar |
|---|---|---|
| Traslados entre sedes | `inventario.ver` | `inventario.ajustar` (despachar, recibir, rechazar) |
| Presentaciones de producto | `productos.ver` (vía `ProductoPolicy`) | `productos.editar` (vía `ProductoPolicy`) |
| Reporte multisucursal | `reportes.ver` | — |
| Facturación electrónica | `documentos.ver` (listado, detalle, XML) | `documentos.emitir` (emitir); `documentos.anular` (nota crédito) |
| Impresión de cierre de caja | `caja.ver` | — |
| Suscripción y pagos | `empresa.gestionar` | `empresa.gestionar` (checkout) |
| Sincronización de ventas offline | — | `ventas.crear` |

Además:

- Los botones de esas acciones (Nuevo traslado, Recibir/Rechazar, Ver consolidado, Emitir DIAN, Emitir nota crédito, formulario y borrado de presentaciones) se ocultan a quien no tiene el permiso.
- Con la suscripción vencida, el middleware ya no envía a la página de Suscripción a quien no puede renovarla: lo devuelve con un aviso para que contacte a un administrador.

**Decisión tomada sobre las notas crédito:** antes, el cajero podía emitirlas. Ahora requieren `documentos.anular`, el mismo permiso que anular un documento, que tienen el administrador de empresa y el administrador de sede, pero no el cajero. Si se prefiere otro criterio, basta con cambiar ese permiso en `FacturacionElectronicaController::notaCredito`.

#### Verificación

`AutorizacionPorPermisoTest` (7 tests): el rol sin permiso recibe 403 y no se crea ni modifica ningún registro, y el rol con permiso conserva el acceso.

---

### Error 4 — El comprobante impreso del cierre de caja mostraba $0

- **Severidad:** Alta (el arqueo impreso no sirve como soporte).
- **Estado:** ✅ Corregido.
- **Archivo:** `resources/views/impresion/caja_cierre.blade.php`

**Qué pasaba:** "Saldo Esperado" y "Efectivo Real" siempre salían en $0.00 en el comprobante impreso del cierre.

**Causa:** la vista leía `saldo_esperado` y `monto_final_real`, pero las columnas de `CajaSesion` son `monto_cierre_esperado` y `monto_cierre_contado`.

**Solución:** la vista usa las columnas reales.

**Verificación:** `DetallesYFormulariosTest::test_comprobante_impreso_de_cierre_muestra_esperado_y_contado` cierra una caja con un faltante de $5.000 y comprueba que el comprobante muestra ambos valores.

---

### Error 5 — El POS mostraba "UND" como unidad de todos los productos

- **Severidad:** Media (productos vendidos por metro o kilo aparecían como unidades).
- **Estado:** ✅ Corregido.
- **Archivo:** `app/Http/Controllers/PosController.php`

**Causa:** el POS leía `unidadMedida->abreviatura`, campo que no existe (el modelo tiene `codigo`), y el `?? 'UND'` lo ocultaba. También se eliminó el campo `sku`, que no existe en productos; el POS ya buscaba por `codigo`.

**Solución:** el POS envía `unidadMedida->codigo`.

**Verificación:** `DetallesYFormulariosTest::test_pos_envia_la_unidad_de_medida_real_de_cada_producto` asigna la unidad "MT" a un producto y comprueba que el POS la recibe.

---

### Error 6 — Las listas de precios mostraban siempre 0 precios específicos

- **Severidad:** Media.
- **Estado:** ✅ Corregido.
- **Archivo:** `resources/views/listas_precios/index.blade.php`

**Causa:** el controlador carga el conteo como `detalles_count`, pero la vista leía `precios_count` y la relación inexistente `precios`.

**Solución:** la vista usa `detalles_count` en la tabla y en las tarjetas móviles.

**Verificación:** `ListadosConDatosTest::test_listas_de_precios_cuentan_los_precios_especificos`.

---

### Error 7 — El estado de cuenta impreso salía sin nombre de la empresa

- **Severidad:** Media (documento entregado al cliente).
- **Estado:** ✅ Corregido.
- **Archivo:** `resources/views/cartera/estado_cuenta_print.blade.php`

**Causa:** usaba `$cliente->empresa->nombre`; el modelo `Empresa` tiene `nombre_comercial`.

**Solución:** se usa `nombre_comercial`.

**Verificación:** `DetallesYFormulariosTest::test_estado_de_cuenta_impreso_muestra_nombre_de_la_empresa`.

---

### Error 8 — La URL de detalle de un impuesto daba error 500

- **Severidad:** Baja (no hay enlaces a esa URL, pero respondía con error de servidor).
- **Estado:** ✅ Corregido.

**Causa:** igual que en resoluciones (error 2): `Route::resource('impuestos', …)` registraba la ruta `show`, pero `ImpuestoController` no tiene ese método.

**Solución:** `->except('show')` en `routes/web.php`.

**Verificación:** `RecorridoRutasPorRolTest`, que recorre todas las rutas sin errores 500.

---

### Error 9 — El despliegue no compilaba el CSS/JS

- **Severidad:** Alta (afecta a todos los usuarios de producción).
- **Estado:** ✅ Corregido en `deploy.sh`. Requiere ejecutar el build una vez en el servidor.

**Qué pasaba:** en producción, el menú mini solo mostraba el botón Dashboard. Además, las columnas que se ocultan según el ancho de la tabla quedaban ocultas siempre.

**Causa:** `public/build` está en `.gitignore` y `deploy.sh` no ejecutaba `npm run build`. Producción servía el HTML nuevo con el CSS de un build anterior, en el que no existen las clases nuevas.

**Solución:** `deploy.sh` ejecuta `npm ci` y `npm run build` antes de las migraciones. En el servidor actual hay que ejecutarlos una vez manualmente.

---

### Tests agregados en la parte 2

| Archivo | Tests | Qué cubre |
|---|---:|---|
| `RecorridoRutasPorRolTest` | 7 | Todas las rutas GET con cada rol, sin errores 500 |
| `AutorizacionPorPermisoTest` | 7 | Permisos de traslados, presentaciones, reportes, facturación electrónica, impresiones, suscripción y POS offline |
| `DetallesYFormulariosTest` (nuevos) | 3 | Comprobante de cierre, estado de cuenta impreso, unidad de medida en el POS |
| `ListadosConDatosTest` (nuevo) | 1 | Conteo de precios específicos en listas de precios |
| `tests/TestCase.php` | — | Modo estricto de atributos activo en toda la suite |
