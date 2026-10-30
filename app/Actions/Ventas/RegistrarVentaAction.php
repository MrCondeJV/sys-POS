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
use App\Enums\EstadoGeneral;
use App\Models\Cliente;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
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
        ?int $listaPrecioId = null,
        float $descuentoGlobal = 0.0,
        ?string $motivoDescuento = null
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
            $listaPrecioId,
            $descuentoGlobal,
            $motivoDescuento
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
                // Cualquier cliente identificado puede comprar a crédito; el consumidor final genérico no, porque la deuda no tendría a quién cobrarse
                if ($cliente->es_predeterminado) {
                    throw new InvalidArgumentException('No se puede vender a crédito al Consumidor Final. Selecciona un cliente identificado.');
                }
            }

            // 1.1. Validar Turno de Caja si fue provisto
            $cajaSesion = null;
            if ($cajaSesionId) {
                $cajaSesion = CajaSesion::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->find($cajaSesionId);

                if (! $cajaSesion) {
                    throw new InvalidArgumentException('El turno de caja especificado no existe o no pertenece a la empresa.');
                }

                if (! $cajaSesion->estaAbierta()) {
                    throw new InvalidArgumentException('El turno de caja especificado se encuentra cerrado. Debe abrir un nuevo turno de caja para operar.');
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

            // 2.1. Resolver la lista de precios aplicable (explícita o la asignada al cliente)
            if ($listaPrecioId === null && $cliente && $cliente->lista_precio_id) {
                $listaPrecioId = $cliente->lista_precio_id;
            }

            $listaPrecio = null;
            if ($listaPrecioId) {
                $listaPrecio = ListaPrecio::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->where('estado', EstadoGeneral::ACTIVO->value)
                    ->with('detalles')
                    ->find($listaPrecioId);
            }

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

                $producto = Producto::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->with('impuesto')
                    ->findOrFail($item['producto_id']);

                if ($producto->estado !== EstadoGeneral::ACTIVO) {
                    throw new InvalidArgumentException("El producto '{$producto->nombre}' se encuentra inactivo y no puede ser vendido.");
                }

                $presentacionId = $item['presentacion_id'] ?? null;
                $presentacion = null;
                $factorConversion = 1.0;
                if ($presentacionId) {
                    $presentacion = ProductoPresentacion::where('empresa_id', $empresaId)
                        ->where('producto_id', $producto->id)
                        ->findOrFail($presentacionId);
                    $factorConversion = (float) $presentacion->factor_conversion;
                }

                // Precio e IVA se toman del catálogo (nunca del cliente) para evitar manipulación de montos
                $precioUnitario = $this->resolverPrecioUnitario($producto, $presentacion, $listaPrecio);
                $impuestoPorcentaje = $producto->porcentaje_iva;

                // Venta por medida: si el precio de la unidad base (ej. m²) aún no se define, solo se vende por presentación
                if (! $presentacion && $precioUnitario <= 0 && $producto->presentaciones()->exists()) {
                    $unidad = $producto->unidadMedida?->codigo ?? 'unidad base';
                    throw new InvalidArgumentException("El producto '{$producto->nombre}' aún no tiene precio por {$unidad}. Véndelo por su presentación (ej. caja) o define el precio por {$unidad}.");
                }

                $descuentoItem = isset($item['descuento']) ? (float) $item['descuento'] : 0.0;
                if ($descuentoItem < 0) {
                    throw new InvalidArgumentException('El descuento de un ítem no puede ser negativo.');
                }
                $montoBrutoItem = round($cantidad * $precioUnitario, 2);
                if ($descuentoItem > $montoBrutoItem) {
                    throw new InvalidArgumentException(sprintf(
                        'El descuento ($%s) no puede superar el valor total del ítem ($%s).',
                        number_format($descuentoItem, 2),
                        number_format($montoBrutoItem, 2)
                    ));
                }

                $subtotalLinea = round(($cantidad * $precioUnitario) - $descuentoItem, 2);
                $impuestoLinea = round($subtotalLinea * ($impuestoPorcentaje / 100), 2);
                $totalLinea = round($subtotalLinea + $impuestoLinea, 2);

                $subtotalVenta += ($cantidad * $precioUnitario);
                $descuentoVenta += $descuentoItem;
                $impuestoVenta += $impuestoLinea;

                $loteId = $item['lote_id'] ?? null;
                $lote = null;
                if ($loteId) {
                    $lote = \App\Models\ProductoLote::where('empresa_id', $empresaId)
                        ->where('sucursal_id', $sucursalId)
                        ->where('producto_id', $producto->id)
                        ->findOrFail($loteId);
                }

                $detallesParaInsertar[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'factor_conversion' => $factorConversion,
                    'cantidad_base' => $cantidad * $factorConversion,
                    'presentacion_id' => $presentacionId,
                    'lote_id' => $loteId,
                    'lote' => $lote,
                    'precio_unitario' => $precioUnitario,
                    // Costo por unidad vendida (una caja cuesta factor × costo de la unidad base)
                    'costo_unitario' => round((float) ($producto->precio_costo ?? 0) * $factorConversion, 2),
                    'costo_base' => (float) ($producto->precio_costo ?? 0),
                    'descuento' => $descuentoItem,
                    'impuesto_porcentaje' => $impuestoPorcentaje,
                    'impuesto_monto' => $impuestoLinea,
                    'subtotal' => $subtotalLinea,
                    'total' => $totalLinea,
                ];
            }

            // 3.0. Descuento en valor fijo sobre el total (solo contado). Se reparte entre las líneas para que
            // IVA, caja, utilidad, devoluciones y documentos reflejen lo realmente cobrado.
            $descuentoGlobal = round(max(0.0, $descuentoGlobal), 2);
            if ($descuentoGlobal > 0) {
                if ($tipoPago === TipoPago::CREDITO) {
                    throw new InvalidArgumentException('El descuento sobre el total solo aplica a ventas de contado.');
                }

                $motivoDescuento = trim((string) $motivoDescuento);
                if ($motivoDescuento === '') {
                    throw new InvalidArgumentException('Debes indicar el motivo del descuento.');
                }

                $totalAntesDescuento = round(array_sum(array_column($detallesParaInsertar, 'total')), 2);
                if ($descuentoGlobal >= $totalAntesDescuento) {
                    throw new InvalidArgumentException(sprintf(
                        'El descuento ($%s) debe ser menor al total de la venta ($%s).',
                        number_format($descuentoGlobal, 2),
                        number_format($totalAntesDescuento, 2)
                    ));
                }

                $detallesParaInsertar = $this->repartirDescuentoGlobal($detallesParaInsertar, $descuentoGlobal, $totalAntesDescuento);
                $descuentoVenta = array_sum(array_column($detallesParaInsertar, 'descuento'));
                $impuestoVenta = array_sum(array_column($detallesParaInsertar, 'impuesto_monto'));
            } else {
                $motivoDescuento = null;
            }

            $totalGeneral = round(($subtotalVenta - $descuentoVenta) + $impuestoVenta, 2);

            // 3.1. Validar suficiencia de pagos para ventas al contado y calcular cambio
            $cambio = 0.0;
            if ($tipoPago === TipoPago::CONTADO) {
                if (! empty($pagos)) {
                    $totalPagado = round((float) collect($pagos)->sum('monto'), 2);
                    if ($totalPagado < $totalGeneral) {
                        throw new InvalidArgumentException(sprintf(
                            'La suma de los pagos ($%s) no cubre el total de la venta ($%s).',
                            number_format($totalPagado, 2),
                            number_format($totalGeneral, 2)
                        ));
                    }
                    if ($totalPagado > $totalGeneral) {
                        $cambio = round($totalPagado - $totalGeneral, 2);
                    }
                } elseif ($pagoCon !== null) {
                    if ($pagoCon < $totalGeneral) {
                        throw new InvalidArgumentException(sprintf(
                            'El monto entregado ($%s) es insuficiente para cubrir el total de la venta ($%s).',
                            number_format($pagoCon, 2),
                            number_format($totalGeneral, 2)
                        ));
                    }
                    $cambio = round($pagoCon - $totalGeneral, 2);
                }
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
                'descuento' => round($descuentoVenta, 2),
                'descuento_global' => $descuentoGlobal,
                'motivo_descuento' => $motivoDescuento,
                'impuesto' => round($impuestoVenta, 2),
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
                    'factor_conversion' => $det['factor_conversion'],
                    'presentacion_id' => $det['presentacion_id'],
                    'lote_id' => $det['lote_id'],
                    'precio_unitario' => $det['precio_unitario'],
                    'costo_unitario' => $det['costo_unitario'],
                    'descuento' => $det['descuento'],
                    'impuesto_porcentaje' => $det['impuesto_porcentaje'],
                    'impuesto_monto' => $det['impuesto_monto'],
                    'subtotal' => $det['subtotal'],
                    'total' => $det['total'],
                ]);

                // Descontar del lote si aplica
                if ($det['lote']) {
                    $det['lote']->descontarStock($det['cantidad_base']);
                }

                // Disminuir existencias físicas en la sucursal (en unidad base)
                $this->inventarioAction->execute(new MovimientoInventarioDTO(
                    productoId: $det['producto']->id,
                    sucursalId: $sucursalId,
                    tipo: TipoMovimientoInventario::SALIDA_VENTA,
                    cantidad: $det['cantidad_base'],
                    referencia: $numeroVenta,
                    costoUnitario: $det['costo_base'],
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
            if ($tipoPago === TipoPago::CONTADO && $cajaSesion) {
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

            // 8. Impactar Cartera si la venta fue a Crédito
            if ($tipoPago === TipoPago::CREDITO && $cliente) {
                $this->carteraAction->execute(
                    empresaId: $empresaId,
                    sucursalId: $sucursalId,
                    clienteId: $cliente->id,
                    montoTotal: $totalGeneral,
                    fechaEmision: now()->toDateString(),
                    concepto: "Factura a Crédito {$numeroVenta}",
                    userId: $userId,
                    // Plazo acordado opcional con el cliente; sin plazo la deuda no tiene fecha de pago
                    fechaVencimiento: $cliente->plazo_dias > 0 ? now()->addDays($cliente->plazo_dias)->toDateString() : null
                );
            }

            // 8.1. Dejar rastro del descuento autorizado en la auditoría
            if ($descuentoGlobal > 0) {
                \App\Actions\Auditoria\RegistrarAuditoriaAction::execute(
                    accion: 'DESCUENTO_VENTA',
                    modulo: 'VENTAS',
                    model: $venta,
                    datosAnteriores: null,
                    datosNuevos: [
                        'numero_venta' => $numeroVenta,
                        'descuento' => $descuentoGlobal,
                        'motivo' => $motivoDescuento,
                        'total_cobrado' => $totalGeneral,
                    ],
                    descripcion: "Descuento de \${$descuentoGlobal} en la venta {$numeroVenta}. Motivo: {$motivoDescuento}"
                );
            }

            // 9. Emitir Documento Comercial Formal (Fase 18)
            $tipoDoc = match ($tipoComprobante) {
                TipoComprobanteVenta::FACTURA => \App\Enums\TipoDocumentoVenta::FACTURA,
                default => \App\Enums\TipoDocumentoVenta::TICKET,
            };
            app(\App\Actions\Documentos\EmitirDocumentoVentaAction::class)->execute(
                venta: $venta,
                tipo: $tipoDoc,
                observaciones: $observaciones
            );

            return $venta->load(['detalles.producto', 'cliente', 'sucursal', 'usuario', 'pagos', 'documentoVenta']);
        });
    }

    /**
     * Reparte un descuento en valor fijo (sobre el total con IVA) entre las líneas, proporcional a su total.
     * Cada parte se convierte a base antes de IVA, de modo que el total cobrado baje exactamente el valor indicado.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array<int, array<string, mixed>>
     */
    protected function repartirDescuentoGlobal(array $detalles, float $descuento, float $totalAntes): array
    {
        $restante = $descuento;
        $ultimo = array_key_last($detalles);

        foreach ($detalles as $i => $det) {
            $parte = $i === $ultimo ? $restante : round($descuento * ((float) $det['total'] / $totalAntes), 2);
            $parte = min($parte, (float) $det['total']);
            $restante = round($restante - $parte, 2);

            $tasa = (float) $det['impuesto_porcentaje'] / 100;
            $reduccionBase = round($parte / (1 + $tasa), 2);

            $subtotal = round((float) $det['subtotal'] - $reduccionBase, 2);
            $impuesto = round($subtotal * $tasa, 2);

            $detalles[$i]['descuento'] = round((float) $det['descuento'] + $reduccionBase, 2);
            $detalles[$i]['subtotal'] = $subtotal;
            $detalles[$i]['impuesto_monto'] = $impuesto;
            $detalles[$i]['total'] = round($subtotal + $impuesto, 2);
        }

        // Ajuste de centavos por redondeo: el total debe quedar exactamente en (total anterior - descuento)
        $objetivo = round($totalAntes - $descuento, 2);
        $diferencia = round(array_sum(array_column($detalles, 'total')) - $objetivo, 2);
        if ($diferencia != 0.0) {
            $detalles[$ultimo]['descuento'] = round($detalles[$ultimo]['descuento'] + $diferencia, 2);
            $detalles[$ultimo]['subtotal'] = round($detalles[$ultimo]['subtotal'] - $diferencia, 2);
            $detalles[$ultimo]['total'] = round($detalles[$ultimo]['total'] - $diferencia, 2);
        }

        return $detalles;
    }

    /**
     * Determina el precio unitario oficial de un ítem: presentación > lista de precios > precio base.
     */
    protected function resolverPrecioUnitario(Producto $producto, ?ProductoPresentacion $presentacion, ?ListaPrecio $listaPrecio): float
    {
        if ($presentacion) {
            return (float) $presentacion->precio_venta > 0
                ? (float) $presentacion->precio_venta
                : round((float) $producto->precio_venta * (float) $presentacion->factor_conversion, 2);
        }

        if ($listaPrecio) {
            return $listaPrecio->calcularPrecio($producto);
        }

        return (float) $producto->precio_venta;
    }
}
