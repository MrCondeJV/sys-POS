<?php

namespace App\Actions\Caja;

use App\Enums\EstadoSesionCaja;
use App\Enums\TipoMovimientoCaja;
use App\Models\CajaSesion;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarMovimientoCajaAction
{
    /**
     * Registra un ingreso o egreso de dinero en la sesión activa de caja.
     */
    public function execute(
        CajaSesion $sesion,
        TipoMovimientoCaja $tipo,
        string $concepto,
        float $monto,
        string $metodoPago = 'EFECTIVO',
        ?string $comprobante = null,
        ?User $usuario = null,
        ?Model $origen = null
    ): MovimientoCaja {
        if ($monto <= 0) {
            throw new InvalidArgumentException('El monto del movimiento de caja debe ser estrictamente mayor a cero.');
        }

        if (trim($concepto) === '') {
            throw new InvalidArgumentException('El concepto o motivo del movimiento es obligatorio.');
        }

        return DB::transaction(function () use (
            $sesion,
            $tipo,
            $concepto,
            $monto,
            $metodoPago,
            $comprobante,
            $usuario,
            $origen
        ) {
            $sesionBloqueada = CajaSesion::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($sesion->id);

            if ($sesionBloqueada->estado !== EstadoSesionCaja::ABIERTA) {
                throw new InvalidArgumentException('No se pueden registrar movimientos en un turno de caja que no esté abierto.');
            }

            // Si es un egreso en efectivo, validar que no supere el saldo disponible en caja
            if ($tipo === TipoMovimientoCaja::EGRESO && strtoupper($metodoPago) === 'EFECTIVO') {
                $saldoEfectivo = $sesionBloqueada->calcularSaldoEsperadoEfectivo();
                if (round($monto, 2) > round($saldoEfectivo + 0.01, 2)) {
                    throw new InvalidArgumentException(sprintf(
                        'Fondos insuficientes en caja para el egreso. Efectivo disponible: $%s, monto solicitado: $%s.',
                        number_format($saldoEfectivo, 2),
                        number_format($monto, 2)
                    ));
                }
            }

            $userId = $usuario ? $usuario->id : ($sesionBloqueada->user_id);

            $movimiento = MovimientoCaja::create([
                'empresa_id' => $sesionBloqueada->empresa_id,
                'sucursal_id' => $sesionBloqueada->sucursal_id,
                'caja_sesion_id' => $sesionBloqueada->id,
                'user_id' => $userId,
                'tipo' => $tipo,
                'concepto' => trim($concepto),
                'monto' => $monto,
                'metodo_pago' => strtoupper($metodoPago),
                'comprobante' => $comprobante,
                'origen_type' => $origen ? get_class($origen) : null,
                'origen_id' => $origen ? $origen->getKey() : null,
            ]);

            // Actualizar acumulados en la sesión
            $sesionBloqueada->recalcularTotales();

            return $movimiento;
        });
    }
}
