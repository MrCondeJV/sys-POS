<?php

namespace App\Actions\Ventas;

use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoVenta;
use App\Enums\TipoMovimientoCaja;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Models\CuentaPorCobrar;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnularVentaAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $inventarioAction,
        protected RegistrarMovimientoCajaAction $cajaAction
    ) {}

    /**
     * Anula una venta previamente completada, reponiendo existencias y revirtiendo efectos en caja/cartera.
     */
    public function execute(
        Venta $venta,
        User $usuarioAnulacion,
        string $motivo
    ): Venta {
        if (trim($motivo) === '') {
            throw new InvalidArgumentException('El motivo de anulación es obligatorio.');
        }

        return DB::transaction(function () use ($venta, $usuarioAnulacion, $motivo) {
            $ventaBloqueada = Venta::withoutGlobalScopes()
                ->with(['detalles.producto', 'cajaSesion', 'pagos'])
                ->lockForUpdate()
                ->findOrFail($venta->id);

            if ($ventaBloqueada->estado === EstadoVenta::ANULADA) {
                throw new InvalidArgumentException('Esta venta ya se encuentra anulada.');
            }

            // 1. Reingresar existencias al inventario físico
            foreach ($ventaBloqueada->detalles as $detalle) {
                $this->inventarioAction->execute(new MovimientoInventarioDTO(
                    productoId: $detalle->producto_id,
                    sucursalId: $ventaBloqueada->sucursal_id,
                    tipo: TipoMovimientoInventario::DEVOLUCION_CLIENTE,
                    cantidad: (float) $detalle->cantidad,
                    referencia: "ANULACIÓN {$ventaBloqueada->numero_venta}",
                    costoUnitario: (float) $detalle->costo_unitario,
                    userId: $usuarioAnulacion->id,
                    notas: "Anulación venta {$ventaBloqueada->numero_venta}: {$motivo}"
                ));
            }

            // 2. Si la venta impactó caja en efectivo y la sesión sigue abierta, registrar egreso
            if ($ventaBloqueada->tipo_pago === TipoPago::CONTADO && $ventaBloqueada->caja_sesion_id) {
                $sesion = $ventaBloqueada->cajaSesion;
                if ($sesion && $sesion->estaAbierta()) {
                    $montoEfectivo = (float) $ventaBloqueada->pagos
                        ->where('metodo_pago', 'EFECTIVO')
                        ->sum('monto');

                    if ($montoEfectivo == 0 && strtoupper($ventaBloqueada->metodo_pago) === 'EFECTIVO') {
                        $montoEfectivo = (float) $ventaBloqueada->total;
                    }

                    if ($montoEfectivo > 0) {
                        $this->cajaAction->execute(
                            sesion: $sesion,
                            tipo: TipoMovimientoCaja::EGRESO,
                            concepto: "Devolución por anulación de Venta {$ventaBloqueada->numero_venta}",
                            monto: $montoEfectivo,
                            metodoPago: 'EFECTIVO',
                            comprobante: "ANUL-{$ventaBloqueada->numero_venta}",
                            usuario: $usuarioAnulacion,
                            origen: $ventaBloqueada
                        );
                    }
                }
            }

            // 3. Si fue a Crédito, anular la cuenta por cobrar en Cartera
            if ($ventaBloqueada->tipo_pago === TipoPago::CREDITO) {
                $cxc = CuentaPorCobrar::withoutGlobalScopes()
                    ->where('empresa_id', $ventaBloqueada->empresa_id)
                    ->where('concepto', 'like', "%{$ventaBloqueada->numero_venta}%")
                    ->first();

                if ($cxc) {
                    if ((float) $cxc->monto_pagado > 0) {
                        throw new InvalidArgumentException(sprintf(
                            'No se puede anular la venta %s porque la cuenta por cobrar asociada ya registra abonos por $%s.',
                            $ventaBloqueada->numero_venta,
                            number_format($cxc->monto_pagado, 2)
                        ));
                    }
                    $cxc->update([
                        'estado' => EstadoCuentaCobrar::ANULADA,
                        'observaciones' => "Anulada por cancelación de venta: {$motivo}",
                    ]);
                }
            }

            // 4. Actualizar Estado de la Venta
            $ventaBloqueada->update([
                'estado' => EstadoVenta::ANULADA,
                'anulado_por_id' => $usuarioAnulacion->id,
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $ventaBloqueada->fresh();
        });
    }
}
