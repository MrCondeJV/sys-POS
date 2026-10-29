# Reporte de errores encontrados en los tests de módulos

- **Fecha:** 2026-10-29
- **Origen:** tests de módulos en `tests/Feature/Modulos/`
- **Resultado final:** los 2 errores están corregidos y la suite completa pasa (398 tests, 1676 aserciones).

---

## Error 1 — El kardex mostraba saldo, operador y referencia incorrectos

- **Severidad:** Alta (información de inventario incorrecta).
- **Estado:** ✅ Corregido.
- **Archivo:** `resources/views/inventario/kardex.blade.php`

### Qué pasaba

En el kardex de cualquier producto, **todas** las filas mostraban:

- la columna "Saldo Sucursal" en `0.00`;
- el operador como "Sistema", aunque el movimiento lo hubiera hecho un usuario;
- sin referencia. Por ejemplo, la compra guarda "Factura de Compra #FC-MOD-001", pero no aparecía.

La página cargaba sin error, así que el problema pasaba desapercibido y el usuario veía información de inventario incorrecta.

### Causa

La vista leía campos que no existen en el modelo `MovimientoInventario`. Eloquent devuelve `null` en silencio para atributos inexistentes, por eso no aparecía ningún error.

| La vista usaba | El modelo tiene |
|---|---|
| `$mov->usuario` | relación `user` |
| `$mov->saldo_resultante` | `stock_posterior` |
| `$mov->motivo` | `notas` |
| `$mov->referencia_tipo` / `$mov->referencia_id` | `referencia` |

### Solución

- La vista ahora usa los campos reales: `user` para el operador, `stock_posterior` para el saldo, `referencia` como texto principal y `notas` como texto secundario.
- La referencia larga pasa a la siguiente línea en vez de cortarse.
- Se revisaron las otras vistas que usan `->usuario` (auditoría, recibos de cartera, dashboard) y sus modelos sí tienen esa relación, así que no tenían el mismo problema.

### Verificación

Test `DetallesYFormulariosTest::test_kardex_muestra_saldo_operador_y_referencia_de_cada_movimiento`. Con una compra de +50, ventas de −4 y −10, una devolución de +1 y un traslado de −5, el kardex de la sede principal muestra el saldo final de **32**, el nombre de cada operador y la referencia de cada documento.

---

## Error 2 — Cualquier usuario de la empresa podía administrar las resoluciones DIAN

- **Severidad:** Alta (seguridad / cumplimiento fiscal).
- **Estado:** ✅ Corregido.
- **Archivos:** `routes/web.php`, `app/Http/Controllers/ResolucionFacturacionController.php`, `app/Policies/ResolucionFacturacionPolicy.php` (nuevo), `resources/views/facturacion-electronica/resoluciones/index.blade.php`

### Qué pasaba

Un usuario con rol **cajero** podía crear, editar y eliminar resoluciones de facturación DIAN. Se comprobó así:

| Acción del cajero | Resultado antes de la corrección |
|---|---|
| Abrir el formulario de creación / edición | Permitido |
| Crear una resolución | **Creada** |
| Eliminar una resolución | **Eliminada** |
| Editar una resolución | Permitido |

Es decir, cualquier empleado podía cambiar prefijos, rangos o vigencias de la numeración autorizada por la DIAN.

### Causa

Las rutas de resoluciones no tenían middleware de permisos y el controlador no autorizaba ninguna acción. Otros módulos, como `ImpuestoController`, sí lo hacen con `Gate::authorize`.

### Solución

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

### Verificación

| Test | Qué confirma |
|---|---|
| `AislamientoEntreEmpresasTest::test_cajero_no_puede_crear_editar_ni_eliminar_resoluciones_dian` | El cajero recibe 403 al crear, editar o eliminar, y la resolución no cambia |
| `ValidacionesFormulariosTest::test_admin_puede_crear_y_eliminar_resolucion_sin_documentos` | El administrador conserva el acceso |
| `ValidacionesFormulariosTest::test_resolucion_con_documentos_emitidos_no_se_puede_eliminar` | No se puede eliminar una resolución con documentos emitidos |
| `ValidacionesFormulariosTest::test_resolucion_no_acepta_sucursal_de_otra_empresa` | No se acepta una sede de otra empresa |

---

## Pendiente de decisión

El cajero todavía puede emitir **notas crédito electrónicas**, porque tiene el permiso `documentos.emitir`. Puede ser intencional, pero una nota crédito revierte ingresos ante la DIAN y normalmente se limita a supervisores o administradores.
