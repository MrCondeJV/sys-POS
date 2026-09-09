<?php

namespace App\Actions\Devoluciones;

use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoDevolucion;
use App\Enums\EstadoVenta;
use App\Enums\TipoDevolucion;
use App\Enums\TipoMovimientoCaja;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoReintegroDevolucion;
use App\DTOs\MovimientoInventarioDTO;
use App\Models\CajaSesion;
use App\Models\CuentaPorCobrar;
use App\Models\Devolucion;
use App\Models\DevolucionDetalle;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarDevolucionAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $inventarioAction,
        protected RegistrarMovimientoCajaAction $cajaAction
    ) {}

    /**
     * Registra una devolución (total o parcial) de una venta.
     *
     * @param array<int, array{venta_detalle_id: int, cantidad: float, reingresa_inventario?: bool}> $items
     */
    public function execute(
        Venta $venta,
        int $userId,
        array $items,
        TipoReintegroDevolucion $tipoReintegro = TipoReintegroDevolucion::EFECTIVO,
        ?int $cajaSesionId = null,
        ?string $motivo = null
    ): Devolucion {
        if ($venta->estado !== EstadoVenta::COMPLETADA) {
            throw new InvalidArgumentException("No se pueden registrar devoluciones sobre ventas en estado {$venta->estado->value}.");
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Debes seleccionar al menos un producto para la devolución.');
        }

        return DB::transaction(function () use (
            $venta,
            $userId,
            $items,
            $tipoReintegro,
            $cajaSesionId,
            $motivo
        ) {
            $empresaId = $venta->empresa_id;
            $sucursalId = $venta->sucursal_id;

            // Bloquear la venta para actualización concurrente
            $ventaFresca = Venta::where('id', $venta->id)->lockForUpdate()->first();

            $subtotalDevolucion = 0.0;
            $impuestoDevolucion = 0.0;
            $totalDevolucion = 0.0;
            $detallesParaProcesar = [];

            foreach ($items as $item) {
                $cantidadADevolver = (float) $item['cantidad'];
                if ($cantidadADevolver <= 0) {
                    continue;
                }

                $detalle = VentaDetalle::where('id', $item['venta_detalle_id'])
                    ->where('venta_id', $venta->id)
                    ->lockForUpdate()
                    ->first();

                if (! $detalle) {
                    throw new InvalidArgumentException("El item #{$item['venta_detalle_id']} no pertenece a esta venta.");
                }

                $disponibleParaDevolver = max(0, (float) $detalle->cantidad - (float) $detalle->cantidad_devuelta);

                if ($cantidadADevolver > $disponibleParaDevolver) {
                    throw new InvalidArgumentException(sprintf(
                        'La cantidad a devolver (%.2f) supera las unidades restantes disponibles (%.2f) del producto %s.',
                        $cantidadADevolver,
                        $disponibleParaDevolver,
                        $detalle->producto->nombre ?? 'ID '.$detalle->producto_id
                    ));
                }

                $precioUnitario = (float) $detalle->precio_unitario;
                $impuestoPorcentaje = (float) ($detalle->impuesto_porcentaje ?? 0);

                $subtotalLinea = round($cantidadADevolver * $precioUnitario, 2);
                $impuestoLinea = round($subtotalLinea * ($impuestoPorcentaje / 100), 2);
                $totalLinea = round($subtotalLinea + $impuestoLinea, 2);

                $subtotalDevolucion += $subtotalLinea;
                $impuestoDevolucion += $impuestoLinea;
                $totalDevolucion += $totalLinea;

                $detallesParaProcesar[] = [
                    'venta_detalle' => $detalle,
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $cantidadADevolver,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalLinea,
                    'impuesto_porcentaje' => $impuestoPorcentaje,
                    'impuesto_monto' => $impuestoLinea,
                    'total' => $totalLinea,
                    'reingresa_inventario' => (bool) ($item['reingresa_inventario'] ?? true),
                ];
            }

            if (empty($detallesParaProcesar)) {
                throw new InvalidArgumentException('No se especificaron cantidades válidas mayores a 0 para devolver.');
            }

            // Generar número consecutivo DEV-XXXXXX
            $ultimoNumero = Devolucion::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->count();
            $numeroDevolucion = 'DEV-' . str_pad((string) ($ultimoNumero + 1), 6, '0', STR_PAD_LEFT);

            // Determinar si es devolución TOTAL o PARCIAL
            $totalUnidadesVendidas = $venta->detalles()->sum('cantidad');
            $totalUnidadesDevueltasPrevias = $venta->detalles()->sum('cantidad_devuelta');
            $totalUnidadesEstaDevolucion = array_sum(array_column($detallesParaProcesar, 'cantidad'));

            $esTotal = ($totalUnidadesDevueltasPrevias + $totalUnidadesEstaDevolucion) >= $totalUnidadesVendidas;
            $tipoDevolucion = $esTotal ? TipoDevolucion::TOTAL : TipoDevolucion::PARCIAL;

            // 1. Crear cabecera Devolucion
            $devolucion = Devolucion::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'venta_id' => $venta->id,
                'user_id' => $userId,
                'caja_sesion_id' => $cajaSesionId,
                'numero_devolucion' => $numeroDevolucion,
                'tipo_devolucion' => $tipoDevolucion,
                'tipo_reintegro' => $tipoReintegro,
                'subtotal' => $subtotalDevolucion,
                'impuesto' => $impuestoDevolucion,
                'total' => $totalDevolucion,
                'motivo' => $motivo,
                'estado' => EstadoDevolucion::COMPLETADA,
            ]);

            // 2. Crear detalles y actualizar stock / venta_detalles
            foreach ($detallesParaProcesar as $d) {
                DevolucionDetalle::create([
                    'empresa_id' => $empresaId,
                    'devolucion_id' => $devolucion->id,
                    'venta_detalle_id' => $d['venta_detalle']->id,
                    'producto_id' => $d['producto_id'],
                    'cantidad' => $d['cantidad'],
                    'precio_unitario' => $d['precio_unitario'],
                    'subtotal' => $d['subtotal'],
                    'impuesto_porcentaje' => $d['impuesto_porcentaje'],
                    'impuesto_monto' => $d['impuesto_monto'],
                    'total' => $d['total'],
                    'reingresa_inventario' => $d['reingresa_inventario'],
                ]);

                // Incrementar cantidad_devuelta en el detalle de la venta
                $d['venta_detalle']->increment('cantidad_devuelta', $d['cantidad']);

                // Reingresar stock al inventario
                if ($d['reingresa_inventario']) {
                    $this->inventarioAction->execute(new MovimientoInventarioDTO(
                        productoId: $d['producto_id'],
                        sucursalId: $sucursalId,
                        tipo: TipoMovimientoInventario::DEVOLUCION_CLIENTE,
                        cantidad: $d['cantidad'],
                        referencia: $numeroDevolucion,
                        costoUnitario: (float) $d['precio_unitario'],
                        userId: $userId,
                        notas: "Devolución {$numeroDevolucion} de Venta #{$venta->numero_venta}"
                    ));
                }
            }

            // 3. Actualizar la Venta
            $ventaFresca->total_devuelto = round((float) $ventaFresca->total_devuelto + $totalDevolucion, 2);
            $ventaFresca->tiene_devolucion = true;
            $ventaFresca->save();

            // 4. Actualizar Caja si el reintegro es en EFECTIVO y hay caja abierta
            if ($tipoReintegro === TipoReintegroDevolucion::EFECTIVO && $cajaSesionId) {
                $cajaSesion = CajaSesion::withoutGlobalScopes()->find($cajaSesionId);
                if ($cajaSesion && $cajaSesion->estaAbierta()) {
                    $this->cajaAction->execute(
                        sesion: $cajaSesion,
                        tipo: TipoMovimientoCaja::EGRESO,
                        concepto: "Reintegro por Devolución {$numeroDevolucion} (Venta #{$venta->numero_venta})",
                        monto: $totalDevolucion,
                        metodoPago: 'EFECTIVO',
                        comprobante: $numeroDevolucion,
                        usuario: User::find($userId),
                        origen: $devolucion
                    );
                }
            }

            // 5. Ajustar Cartera si la venta fue a crédito o se eligió ajuste de cartera
            if ($tipoReintegro === TipoReintegroDevolucion::AJUSTE_CARTERA || ($venta->isCredito() && $tipoReintegro !== TipoReintegroDevolucion::EFECTIVO)) {
                $cuenta = CuentaPorCobrar::where('empresa_id', $empresaId)
                    ->where('cliente_id', $venta->cliente_id)
                    ->where('concepto', 'like', "%{$venta->numero_venta}%")
                    ->where('estado', '!=', EstadoCuentaCobrar::PAGADA->value)
                    ->first();

                if ($cuenta) {
                    $nuevoSaldo = max(0.0, (float) $cuenta->saldo_pendiente - $totalDevolucion);
                    $cuenta->saldo_pendiente = $nuevoSaldo;
                    if ($nuevoSaldo <= 0) {
                        $cuenta->estado = EstadoCuentaCobrar::PAGADA;
                    }
                    $cuenta->save();
                }
            }

            return $devolucion;
        });
    }
}
