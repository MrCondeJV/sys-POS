<?php

namespace App\Actions\Ventas;

use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoVenta;
use App\Enums\MetodoPagoVenta;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoMovimientoCaja;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\VentaPago;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarVentaAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $inventarioAction,
        protected RegistrarMovimientoCajaAction $cajaAction,
        protected RegistrarCuentaPorCobrarAction $carteraAction
    ) {}

    /**
     * Registra una venta completa con actualización de existencias, caja y cartera.
     *
     * @param array<int, array{producto_id: int, cantidad: float, precio_unitario: float, descuento?: float, impuesto_porcentaje?: float}> $items
     * @param array<int, array{metodo_pago: string, monto: float, referencia?: string}>|null $pagos
     */
    public function execute(
        int $empresaId,
        int $sucursalId,
        int $userId,
        array $items,
        ?int $clienteId = null,
        TipoPago $tipoPago = TipoPago::CONTADO,
        string $metodoPago = 'EFECTIVO',
        TipoComprobanteVenta $tipoComprobante = TipoComprobanteVenta::TICKET,
        ?int $cajaSesionId = null,
        ?float $pagoCon = null,
        ?array $pagos = null,
        ?string $observaciones = null,
        ?int $listaPrecioId = null
    ): Venta {
        if (empty($items)) {
            throw new InvalidArgumentException('La venta debe contener al menos un producto.');
        }

        return DB::transaction(function () use (
            $empresaId,
            $sucursalId,
            $userId,
            $items,
            $clienteId,
            $tipoPago,
            $metodoPago,
            $tipoComprobante,
            $cajaSesionId,
            $pagoCon,
            $pagos,
            $observaciones,
            $listaPrecioId
        ) {
            // 1. Validar Cliente si la venta es a Crédito
            $cliente = null;
            if ($clienteId) {
                $cliente = Cliente::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->findOrFail($clienteId);
            }

            if ($tipoPago === TipoPago::CREDITO) {
                if (! $cliente) {
                    throw new InvalidArgumentException('Para registrar una venta a crédito se debe seleccionar un cliente válido.');
                }
                if (! $cliente->tieneCredito()) {
                    throw new InvalidArgumentException('El cliente seleccionado no tiene crédito comercial habilitado.');
                }
            }

            // 2. Generar Consecutivo de Venta correlativo (VTA-00001)
            $ultimoNumero = Venta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->max('numero_venta');

            if ($ultimoNumero && preg_match('/VTA-(\d+)/', $ultimoNumero, $matches)) {
                $consecutivo = (int) $matches[1] + 1;
            } else {
                $consecutivo = 1;
            }
            $numeroVenta = sprintf('VTA-%05d', $consecutivo);

            // 3. Calcular totales e ítems
            $subtotalVenta = 0.0;
            $descuentoVenta = 0.0;
            $impuestoVenta = 0.0;
            $detallesParaInsertar = [];

            foreach ($items as $item) {
                $cantidad = (float) $item['cantidad'];
                if ($cantidad <= 0) {
                    throw new InvalidArgumentException('La cantidad de cada ítem debe ser estrictamente mayor a cero.');
                }

                $precioUnitario = (float) $item['precio_unitario'];
                if ($precioUnitario < 0) {
                    throw new InvalidArgumentException('El precio unitario no puede ser negativo.');
                }

                $descuentoItem = isset($item['descuento']) ? (float) $item['descuento'] : 0.0;
                $impuestoPorcentaje = isset($item['impuesto_porcentaje']) ? (float) $item['impuesto_porcentaje'] : 0.0;

                $producto = Producto::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->findOrFail($item['producto_id']);

                $subtotalLinea = round(($cantidad * $precioUnitario) - $descuentoItem, 2);
                $impuestoLinea = round($subtotalLinea * ($impuestoPorcentaje / 100), 2);
                $totalLinea = round($subtotalLinea + $impuestoLinea, 2);

                $subtotalVenta += ($cantidad * $precioUnitario);
                $descuentoVenta += $descuentoItem;
                $impuestoVenta += $impuestoLinea;

                $detallesParaInsertar[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'costo_unitario' => (float) ($producto->precio_costo ?? 0),
                    'descuento' => $descuentoItem,
                    'impuesto_porcentaje' => $impuestoPorcentaje,
                    'impuesto_monto' => $impuestoLinea,
                    'subtotal' => $subtotalLinea,
                    'total' => $totalLinea,
                ];
            }

            $totalGeneral = round(($subtotalVenta - $descuentoVenta) + $impuestoVenta, 2);

            // Validar cupo si es crédito
            if ($tipoPago === TipoPago::CREDITO && $cliente) {
                $cupoDisponible = $cliente->cupoDisponible();
                if ($totalGeneral > $cupoDisponible) {
                    throw new InvalidArgumentException(sprintf(
                        'El total de la venta ($%s) supera el cupo de crédito disponible del cliente ($%s).',
                        number_format($totalGeneral, 2),
                        number_format($cupoDisponible, 2)
                    ));
                }
            }

            // Calcular cambio si paga con efectivo superior
            $cambio = 0.0;
            if ($pagoCon !== null && $pagoCon >= $totalGeneral) {
                $cambio = round($pagoCon - $totalGeneral, 2);
            }

            if ($listaPrecioId === null && $cliente && $cliente->lista_precio_id) {
                $listaPrecioId = $cliente->lista_precio_id;
            }

            // 4. Crear Cabecera de Venta
            $venta = Venta::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'cliente_id' => $clienteId,
                'lista_precio_id' => $listaPrecioId,
                'user_id' => $userId,
                'caja_sesion_id' => $cajaSesionId,
                'numero_venta' => $numeroVenta,
                'tipo_comprobante' => $tipoComprobante,
                'fecha' => now(),
                'tipo_pago' => $tipoPago,
                'metodo_pago' => strtoupper($metodoPago),
                'subtotal' => $subtotalVenta,
                'descuento' => $descuentoVenta,
                'impuesto' => $impuestoVenta,
                'total' => $totalGeneral,
                'pago_con' => $pagoCon,
                'cambio' => $cambio,
                'estado' => EstadoVenta::COMPLETADA,
                'observaciones' => $observaciones,
            ]);

            // 5. Crear Detalles y Descontar Inventario
            foreach ($detallesParaInsertar as $det) {
                VentaDetalle::create([
                    'empresa_id' => $empresaId,
                    'venta_id' => $venta->id,
                    'producto_id' => $det['producto']->id,
                    'cantidad' => $det['cantidad'],
                    'precio_unitario' => $det['precio_unitario'],
                    'costo_unitario' => $det['costo_unitario'],
                    'descuento' => $det['descuento'],
                    'impuesto_porcentaje' => $det['impuesto_porcentaje'],
                    'impuesto_monto' => $det['impuesto_monto'],
                    'subtotal' => $det['subtotal'],
                    'total' => $det['total'],
                ]);

                // Disminuir existencias físicas en la sucursal
                $this->inventarioAction->execute(new MovimientoInventarioDTO(
                    productoId: $det['producto']->id,
                    sucursalId: $sucursalId,
                    tipo: TipoMovimientoInventario::SALIDA_VENTA,
                    cantidad: $det['cantidad'],
                    referencia: $numeroVenta,
                    costoUnitario: $det['costo_unitario'],
                    userId: $userId,
                    notas: "Venta {$numeroVenta}"
                ));
            }

            // 6. Registrar Pagos
            if (! empty($pagos)) {
                foreach ($pagos as $p) {
                    VentaPago::create([
                        'empresa_id' => $empresaId,
                        'venta_id' => $venta->id,
                        'metodo_pago' => strtoupper($p['metodo_pago']),
                        'monto' => (float) $p['monto'],
                        'referencia' => $p['referencia'] ?? null,
                    ]);
                }
            } else {
                VentaPago::create([
                    'empresa_id' => $empresaId,
                    'venta_id' => $venta->id,
                    'metodo_pago' => strtoupper($metodoPago),
                    'monto' => $totalGeneral,
                    'referencia' => null,
                ]);
            }

            // 7. Impactar Caja si hay turno abierto y el pago es al Contado
            if ($tipoPago === TipoPago::CONTADO && $cajaSesionId) {
                $cajaSesion = CajaSesion::withoutGlobalScopes()->find($cajaSesionId);
                if ($cajaSesion && $cajaSesion->estaAbierta()) {
                    // Si hubo pagos múltiples, registrar los correspondientes
                    $montoEfectivo = 0.0;
                    if (! empty($pagos)) {
                        foreach ($pagos as $p) {
                            if (strtoupper($p['metodo_pago']) === 'EFECTIVO') {
                                $montoEfectivo += (float) $p['monto'];
                            }
                        }
                    } elseif (strtoupper($metodoPago) === 'EFECTIVO') {
                        $montoEfectivo = $totalGeneral;
                    }

                    if ($montoEfectivo > 0) {
                        $this->cajaAction->execute(
                            sesion: $cajaSesion,
                            tipo: TipoMovimientoCaja::INGRESO,
                            concepto: "Venta {$numeroVenta}",
                            monto: $montoEfectivo,
                            metodoPago: 'EFECTIVO',
                            comprobante: $numeroVenta,
                            origen: $venta
                        );
                    }
                }
            }

            // 8. Impactar Cartera si la venta fue a Crédito
            if ($tipoPago === TipoPago::CREDITO && $cliente) {
                $plazoDias = $cliente->plazo_dias > 0 ? $cliente->plazo_dias : 30;
                $this->carteraAction->execute(
                    empresaId: $empresaId,
                    sucursalId: $sucursalId,
                    clienteId: $cliente->id,
                    montoTotal: $totalGeneral,
                    fechaEmision: now()->toDateString(),
                    fechaVencimiento: now()->addDays($plazoDias)->toDateString(),
                    concepto: "Factura a Crédito {$numeroVenta}",
                    userId: $userId
                );
            }

            return $venta->load(['detalles.producto', 'cliente', 'sucursal', 'usuario', 'pagos']);
        });
    }
}
