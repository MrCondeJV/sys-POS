# Reglas de Negocio e Invariantes por Módulo (`sys-POS`)

Este documento formaliza los invariantes contables, operativos y de seguridad multiempresa que rigen `sys-POS` y que son verificados por las suites de pruebas automatizadas y por el comando `php artisan sistema:verificar-integridad`.

---

## 1. Tenencia y Aislamiento Multiempresa (`CompanyContext` / `BelongsToCompany`)
1. **Aislamiento Estricto por `empresa_id`:** Todo registro operativo (`Sucursal`, `Almacen`, `Producto`, `Categoria`, `Marca`, `UnidadMedida`, `Impuesto`, `ListaPrecio`, `Cliente`, `Proveedor`, `Caja`, `CajaSesion`, `Venta`, `Compra`, `CuentaPorCobrar`, `TrasladoInventario`, `DevolucionVenta`, `DocumentoVenta`, `ResolucionFacturacion`, `FacturaElectronica`) pertenece exactamente a una `Empresa` (`empresa_id NOT NULL`).
2. **Prohibición de Acceso Cruzado (IDOR):** Ningún usuario de la `Empresa A` puede consultar (`GET`), modificar (`PUT`/`PATCH`/`POST`) ni eliminar (`DELETE`) recursos de la `Empresa B`. El middleware `EnsureValidTenant` y el `CompanyScope` deben rechazar cualquier intento con `403 Forbidden` o `404 Not Found`.
3. **Contexto de Super Administrador:** El rol global `SUPER_ADMIN` (`empresa_id = NULL`) opera por defecto en el panel de plataforma (`/empresas`, `/usuarios`, `/auditoria`). Solo puede operar rutas de negocio si inicia explícitamente sesión contextual en un tenant (`session('superadmin_empresa_id')`).
4. **Estado del Usuario:** Si un usuario pasa a `EstadoGeneral::INACTIVO`, el middleware `SetCompanyContext` invalida su sesión en la siguiente petición HTTP. *(Nota: el bloqueo equivalente cuando la `Empresa` pasa a `INACTIVO` está registrado como `BUG-001` con parche propuesto).*

---

## 2. Roles, Permisos y Autorización (Spatie Permission con `team_id = empresa_id`)
1. **Separación por Equipos (`teams => true`):** Los roles de negocio se asignan bajo el ámbito de `empresa_id`.
2. **Matriz de Roles del Sistema (`RolSistema`):**
   - `SUPER_ADMIN`: Administración de plataforma (`empresas.*`, `usuarios.*`, `auditoria.*`) e ingreso supervisado a negocios.
   - `ADMIN_EMPRESA`: Acceso total a todos los módulos operativos y administrativos de su empresa.
   - `CAJERO`: Operación de `pos.*`, `cajas.*`, `ventas.*`, `clientes.*`, `cartera.*` y `devoluciones.*`. Sin acceso a `compras.*`, `usuarios.*`, `empresa.perfil`, ni `sucursales.*`.
   - `VENDEDOR`: Operación de `pos.*`, `ventas.*`, `clientes.*` y consulta de `productos.*`. Sin acceso a `cajas.*`, `compras.*`, ni configuración.
   - `BODEGUERO`: Gestión de `productos.*`, `catalogos.*`, `inventario.*`, `traslados.*` y `compras.*`. Sin acceso a `pos.*` ni `cajas.*`.
   - `CONTADOR`: Acceso de consulta y auditoría a `reportes.*`, `ventas.*`, `compras.*`, `cartera.*`, `documentos.*`, `facturacion-electronica.*`, `impuestos.*` y `auditoria.*`.

---

## 3. Ventas, POS y Cálculos Financieros (`RegistrarVentaAction` / `AnularVentaAction`)
1. **Invariante de Totales de Línea y Cabecera:**
   - Para cada ítem: $\text{SubtotalBruto}_i = \text{Cantidad}_i \times \text{PrecioUnitario}_i$.
   - $\text{Descuento}_i \in [0, \text{SubtotalBruto}_i]$. No se permiten descuentos negativos ni mayores al subtotal bruto.
   - $\text{BaseGravable}_i = \text{SubtotalBruto}_i - \text{Descuento}_i$.
   - $\text{Impuesto}_i = \text{round}(\text{BaseGravable}_i \times \frac{\text{PorcentajeIVA}_i}{100}, 2)$.
   - $\text{TotalLinea}_i = \text{BaseGravable}_i + \text{Impuesto}_i$.
   - En cabecera: $\text{TotalVenta} = \sum \text{TotalLinea}_i$.
2. **Restricciones de Estado y Stock:**
   - No se puede vender un producto con `estado = INACTIVO`.
   - En productos que controlan stock (`controla_stock = true`), la cantidad vendida (convertida a unidad base) no puede superar el `stock_actual` del almacén activo.
3. **Medios de Pago y Caja:**
   - Toda venta desde el POS requiere una `CajaSesion` en estado `ABIERTA` perteneciente a la misma empresa y sucursal.
   - En pago `EFECTIVO` o `MIXTO`, la suma de los pagos recibidos debe cubrir al menos el total de la venta ($\text{MontoRecibido} \ge \text{TotalVenta}$).
   - En venta a `CREDITO`, el cliente no puede ser "Consumidor Final" y el monto financiado no puede exceder el cupo disponible ($\text{CupoCredito} - \text{SaldoCarteraActual}$).
4. **Anulación de Venta:**
   - Solo ventas en estado `COMPLETADA` pueden pasar a `ANULADA`.
   - La anulación revierte exactamente las existencias al inventario (movimiento Kardex de entrada por anulación), descuenta el ingreso en la sesión de caja (si el turno sigue abierto y fue en efectivo) y anula/ajusta la cuenta por cobrar si fue a crédito.

---

## 4. Cajas y Arqueos (`AbrirCajaAction` / `CerrarCajaAction` / `RegistrarMovimientoCajaAction`)
1. **Unicidad de Turno Abierto:** Una `Caja` solo puede tener a lo sumo **una** `CajaSesion` en estado `ABIERTA` simultáneamente.
2. **Invariante de Saldo Esperado en Efectivo:**
   $$\text{SaldoEsperadoEfectivo} = \text{MontoApertura} + \text{VentasEfectivo} + \text{IngresosManualesEfectivo} - \text{EgresosManualesEfectivo}$$
3. **No Negatividad de Efectivo:** Ningún `EGRESO` manual en efectivo puede superar el saldo esperado en efectivo disponible en la sesión activa.
4. **Cierre y Diferencia de Arqueo:**
   $$\text{Diferencia} = \text{MontoCierreContado} - \text{MontoCierreEsperado}$$
   Una vez cerrada (`EstadoSesionCaja::CERRADA`), la sesión es inmutable y no admite nuevos movimientos ni ventas.

---

## 5. Inventario, Kardex y Traslados (`RegistrarMovimientoInventarioAction` / `TrasladoInventario`)
1. **Invariante de Existencias:** Para todo par `(producto_id, almacen_id)`, `existencias.stock_actual` debe ser $\ge 0$ y coincidir con la suma algebraica de `movimientos_inventario` (`ENTRADA` / `AJUSTE_POSITIVO` / `TRASLADO_ENTRADA` menos `SALIDA` / `AJUSTE_NEGATIVO` / `TRASLADO_SALIDA`).
2. **Costo Promedio Ponderado:** Las entradas por compra actualizan el costo promedio; las salidas por venta descargan al costo vigente sin alterarlo.
3. **Máquina de Estados de Traslados entre Sucursales:**
   - `BORRADOR / PENDIENTE` $\rightarrow$ `EN_TRANSITO` (descuenta stock en almacén origen).
   - `EN_TRANSITO` $\rightarrow$ `RECIBIDO` (incrementa stock en almacén destino) o `RECHAZADO` (reintegra stock al almacén origen).
   - Almacén origen y destino deben pertenecer a la misma `Empresa` y ser distintos entre sí.

---

## 6. Cartera y Cuentas por Cobrar (`RegistrarAbonoCarteraAction`)
1. **Invariante de Saldo de Cuenta por Cobrar:**
   $$\text{SaldoPendiente} = \text{MontoTotal} - \text{MontoPagado}, \quad \text{con } 0 \le \text{MontoPagado} \le \text{MontoTotal}$$
2. **Prohibición de Sobrepago:** Ningún abono (`AbonoCartera`) puede tener monto $\le 0$ ni superar el `saldo_pendiente` actual de la cuenta.
3. **Transición de Estado:**
   - Si $\text{SaldoPendiente} == 0 \Rightarrow \text{EstadoCuentaCobrar::PAGADA}$.
   - Si $0 < \text{SaldoPendiente} < \text{MontoTotal} \Rightarrow \text{EstadoCuentaCobrar::PARCIAL}$ (o `VENCIDA` si superó `fecha_vencimiento`).
