# Reporte de errores encontrados en los tests de módulos

- **Fecha:** 2026-10-29
- **Resultado final:** los 15 errores encontrados en las tres revisiones están corregidos y la suite completa pasa (441 tests, 1809 aserciones) tanto en SQLite como en MySQL 8.4.

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
| 3 | 10 | La API v1 no verificaba ningún permiso | Alta |
| 3 | 11 | Los endpoints de compras y kardex de la API daban error 500 | Alta |
| 3 | 12 | Tres formularios aceptaban IDs de registros de otra empresa | Alta |
| 3 | 13 | Eliminar una presentación de producto no la eliminaba | Media |
| 3 | 14 | Listado de productos y catálogos sin control de permisos | Media |
| 3 | 15 | Impresión de recibos de abono sin control de permisos | Baja |

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

---

## Parte 3 — Validación entre empresas, flujos de escritura, permisos y MySQL

Esta revisión cubrió cuatro frentes que las anteriores no habían probado:

1. **IDs de otra empresa en formularios y API.** Había 19 reglas `exists:tabla,id` que no filtraban por empresa. Para cada una se escribió un test que envía el ID de un registro de otra empresa (`IdsDeOtraEmpresaTest`).
2. **Acciones de escritura con datos válidos.** Se listaron las rutas POST/PUT/DELETE que ningún test ejecutaba y se probaron con datos correctos, verificando que el cambio quede guardado (`FlujosDeEscrituraTest`). Además, un script comparó por reflexión los parámetros de **todas** las rutas con los argumentos de sus controladores.
3. **Permisos correctos en los controladores que sí verifican.** Se generó un mapa de qué chequeo hace cada método de cada controlador y se revisó que cada policy use el permiso correcto. La API v1 se probó por rol con tokens (`AutorizacionApiTest`).
4. **MySQL.** La suite completa se ejecutó contra MySQL 8.4 en modo estricto (`ONLY_FULL_GROUP_BY`), usando una base exclusiva para tests (`pos_db_testing`), porque los tests normalmente corren en SQLite y producción usa MySQL.

Esta revisión encontró 6 errores (10 a 15).

**Corrección en los propios tests:** al crear el super admin con el contexto de una empresa activo, el modelo le asignaba `empresa_id` y dejaba de ser super admin. Por eso los tests del recorrido de rutas no estaban probando realmente a ese rol. Se agregó el helper `crearSuperAdmin()` al escenario, que limpia el contexto antes de crearlo, y los tests verifican que `isSuperAdmin()` sea verdadero.

### Error 10 — La API v1 no verificaba ningún permiso

- **Severidad:** Alta (seguridad).
- **Estado:** ✅ Corregido.
- **Archivos:** `app/Http/Controllers/Api/V1/*Controller.php`

#### Qué pasaba

Ningún endpoint de la API verificaba permisos: bastaba con tener un token válido. Con un usuario **vendedor** se comprobó que podía:

- ver compras, proveedores, inventario y kardex;
- ver el resumen financiero y el estado y movimientos de caja;
- **cerrar la caja de otro usuario**.

Y un **contador** podía registrar ventas y crear clientes, aunque no tiene esos permisos.

#### Solución

Cada endpoint aplica ahora las **mismas policies y permisos que la interfaz web**, así que un token tiene exactamente los permisos del rol de su usuario:

| Endpoint | Autorización |
|---|---|
| Ventas: listar, ver, registrar | `VentaPolicy` (`viewAny`, `view`, `create`) |
| Clientes: listar, ver, crear | `ClientePolicy` |
| Compras: listar, ver | `CompraPolicy` |
| Proveedores: listar, ver | `ProveedorPolicy` |
| Productos: listar, ver | `productos.ver` / `ProductoPolicy` |
| Inventario y kardex | `InventarioPolicy` / `inventario.ver` |
| Resumen de reportes | `reportes.ver` |
| Caja: estado, movimientos, abrir, cerrar | `caja.ver` / `CajaPolicy` (`view`, `abrir`, `cerrar`) |

Empresa, sucursales y datos del usuario autenticado (`/auth/me`) siguen disponibles para cualquier usuario de la empresa.

#### Verificación

`AutorizacionApiTest` (4 tests): el vendedor recibe 403 en los 9 endpoints que no le corresponden, el contador no puede registrar ventas ni clientes, nadie sin permiso puede cerrar una caja, y los roles con permiso conservan el acceso. Los 10 tests originales de la API (`Fase21/ApiTest`) siguen pasando.

### Error 11 — Los endpoints de compras y kardex de la API daban error 500

- **Severidad:** Alta (funcionalidad rota).
- **Estado:** ✅ Corregido.
- **Archivos:** `Api/V1/CompraController.php`, `Api/V1/InventarioController.php`

**Qué pasaba:** `GET /api/v1/compras`, `GET /api/v1/compras/{id}` y `GET /api/v1/inventario/kardex` respondían siempre con error 500.

**Causa:** el mismo tipo de error que el del kardex web (error 1): cargaban la relación `usuario`, pero en `Compra` y `MovimientoInventario` se llama `user`.

**Solución:** se usa la relación `user`. Para descartar otros casos, un script revisó por reflexión las 109 relaciones usadas en `with()`, `withCount()` y `whereHas()` de toda la aplicación, resolviendo cada una (incluidas las anidadas) contra su modelo real. No quedan relaciones inexistentes.

**Verificación:** `AutorizacionApiTest::test_roles_con_permiso_conservan_el_acceso_por_api` consulta compras e inventario por API y exige respuesta 200.

### Error 12 — Tres formularios aceptaban IDs de registros de otra empresa

- **Severidad:** Alta (aislamiento entre empresas).
- **Estado:** ✅ Corregido.

#### Qué pasaba

De las 19 reglas sin filtro de empresa, 16 ya quedaban protegidas más adelante por el scope de empresa de los modelos o por las Actions. Estas 3 **sí aceptaban** el registro ajeno:

| Caso | Efecto |
|---|---|
| Venta en el POS con la lista de precios de otra empresa | La venta se registraba aplicando esa lista (por ejemplo, con 90 % de descuento) |
| Crear o editar un usuario con una sede de otra empresa | El usuario quedaba asignado a esa sede |
| Crear una presentación de producto con una unidad de medida de otra empresa | La presentación se creaba |

#### Solución

- Los campos de POS, ventas web, API de ventas, API de caja y presentaciones usan la regla existente `BelongsToActiveCompany`, que exige que el registro pertenezca a la empresa activa. Se aplicó también a las demás reglas como defensa adicional, aunque ya estuvieran protegidas más adelante.
- En usuarios, la sede debe pertenecer a la **empresa del usuario** (la elegida por el super admin al crear, o la del usuario al editar), con `Rule::exists(...)->where('empresa_id', …)`.
- Quedan sin cambio dos reglas que no lo necesitan: la selección de empresa del super admin y los ítems de una devolución, que la Action ya valida contra la venta.

#### Verificación

`IdsDeOtraEmpresaTest` (9 tests): POS (producto, cliente, caja y lista ajenos), venta web, API de ventas (sede, producto, cliente y caja ajenos), API de caja (abrir y cerrar caja ajena), usuarios y presentaciones. En todos los casos se rechaza la petición y no se crea ni modifica nada.

### Error 13 — Eliminar una presentación de producto no la eliminaba

- **Severidad:** Media.
- **Estado:** ✅ Corregido.
- **Archivos:** `routes/web.php`, `ProductoPresentacionController.php`

**Qué pasaba:** al eliminar una presentación aparecía el mensaje "Presentación eliminada", pero el registro seguía existiendo. Tampoco se verificaba que la presentación perteneciera al producto de la URL.

**Causa:** Laravel nombró el parámetro de la ruta `{presentacione}` (singulariza "presentaciones" como si fuera inglés), mientras el controlador recibe `$presentacion`. Como los nombres no coinciden, Laravel no busca el registro e inyecta un modelo vacío, y `delete()` sobre un modelo vacío no hace nada.

**Solución:** la ruta declara el parámetro como `presentacion` y el controlador responde 404 si la presentación no pertenece al producto. Un script comparó los parámetros de todas las rutas con los argumentos de sus controladores y no encontró otros casos.

**Verificación:** `FlujosDeEscrituraTest::test_admin_elimina_presentacion_de_producto` y `test_no_se_puede_eliminar_presentacion_usando_otro_producto_en_la_url`.

### Error 14 — Listado de productos y catálogos sin control de permisos

- **Severidad:** Media.
- **Estado:** ✅ Corregido.
- **Archivos:** `ProductoController.php`, `CatalogoController.php`

**Qué pasaba:** cualquier usuario podía abrir el listado de productos (con precios de compra y venta) y la página de catálogos, aunque no tuviera `productos.ver`. Por ejemplo, el contador.

**Solución:** ambas pantallas exigen `productos.ver`, el mismo permiso con el que el menú muestra esos enlaces.

**Verificación:** `AutorizacionPorPermisoTest::test_listado_de_productos_y_catalogos_requieren_permiso_de_productos`.

### Error 15 — Impresión de recibos de abono sin control de permisos

- **Severidad:** Baja (hoy todos los roles tienen `cartera.ver`).
- **Estado:** ✅ Corregido.

**Solución:** `ImpresionController::imprimirAbono` usa `PagoClientePolicy::view`, igual que la pantalla del recibo.

### Revisión de permisos de los demás controladores

Se generó un mapa de qué chequeo hace cada método de cada controlador web y se revisaron las 21 policies. Todas usan el permiso que corresponde a su acción (por ejemplo, `CajaPolicy::abrir` → `caja.abrir`, `CompraPolicy::anular` → `compras.anular`). Hay dos observaciones que no son errores:

- `UserPolicy` usa el rol `ADMIN_EMPRESA` en lugar de los permisos `usuarios.ver` y `usuarios.gestionar`. Hoy el resultado es el mismo porque solo ese rol tiene esos permisos, pero si se asignan a otro rol no tendrán efecto.
- Existen dos controladores de usuarios: `UserController`, que es el que usan las rutas, y `UsuarioController`, que no está conectado a ninguna ruta.

### Resultado en MySQL

La suite completa pasa en MySQL 8.4 con `ONLY_FULL_GROUP_BY` y `STRICT_TRANS_TABLES`: **441 tests, 1809 aserciones**, igual que en SQLite. No apareció ninguna diferencia entre los dos motores: los reportes con agrupaciones, las fechas y las búsquedas funcionan igual.

**Para ejecutar los tests contra MySQL** (nunca contra la base de desarrollo, porque los tests borran y recrean las tablas):

```powershell
$env:DB_CONNECTION = 'mysql'; $env:DB_DATABASE = 'pos_db_testing'; php artisan test
```

**Observación:** la base de desarrollo `pos_db` tiene pendiente la migración `2026_10_20_000000_create_wompi_pagos_and_webhook_events_tables`. Se ejecuta con `php artisan migrate`.

### Tests agregados en la parte 3

| Archivo | Tests | Qué cubre |
|---|---:|---|
| `IdsDeOtraEmpresaTest` | 9 | IDs de otra empresa en POS, ventas, API, usuarios y presentaciones |
| `FlujosDeEscrituraTest` | 8 | Crear, editar y suspender empresas; editar listas y quitar precios; eliminar presentaciones |
| `AutorizacionApiTest` | 4 | Permisos de la API v1 por rol |
| `AutorizacionPorPermisoTest` (nuevo) | 1 | Productos y catálogos |
