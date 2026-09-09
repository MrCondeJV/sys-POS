<?php

namespace App\Actions\Cartera;

use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoPagoCartera;
use App\Enums\MetodoPagoCartera;
use App\Models\CuentaPorCobrar;
use App\Models\PagoCliente;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarAbonoCarteraAction
{
    /**
     * Aplica un abono o pago a una cuenta por cobrar con transacción ACID y bloqueo pesimista.
     */
    public function execute(
        CuentaPorCobrar $cuenta,
        float $monto,
        MetodoPagoCartera $metodoPago,
        string $fechaPago,
        ?string $referenciaPago = null,
        ?string $notas = null,
        ?int $userId = null
    ): PagoCliente {
        if ($monto <= 0) {
            throw new InvalidArgumentException('El monto del abono debe ser estrictamente mayor a cero.');
        }

        return DB::transaction(function () use (
            $cuenta,
            $monto,
            $metodoPago,
            $fechaPago,
            $referenciaPago,
            $notas,
            $userId
        ) {
            // Bloqueo pesimista de fila para evitar race conditions
            $cuentaBloqueada = CuentaPorCobrar::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($cuenta->id);

            if ($cuentaBloqueada->estado === EstadoCuentaCobrar::PAGADA) {
                throw new InvalidArgumentException('Esta cuenta por cobrar ya se encuentra totalmente pagada.');
            }

            if ($cuentaBloqueada->estado === EstadoCuentaCobrar::ANULADA) {
                throw new InvalidArgumentException('No se pueden registrar abonos sobre una cuenta anulada.');
            }

            $saldoPendienteActual = (float) $cuentaBloqueada->saldo_pendiente;

            // Validación de sobrepago (con tolerancia a centavos 0.01)
            if (round($monto, 2) > round($saldoPendienteActual + 0.01, 2)) {
                throw new InvalidArgumentException(sprintf(
                    'El abono ($%s) supera el saldo pendiente de la obligación ($%s).',
                    number_format($monto, 2),
                    number_format($saldoPendienteActual, 2)
                ));
            }

            // Generación de consecutivo atómico de recibo de caja RC-00001
            $ultimoRecibo = PagoCliente::withoutGlobalScopes()
                ->where('empresa_id', $cuentaBloqueada->empresa_id)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $siguienteNumero = 1;
            if ($ultimoRecibo && preg_match('/RC-(\d+)/', $ultimoRecibo->numero_recibo, $matches)) {
                $siguienteNumero = (int) $matches[1] + 1;
            }

            $numeroRecibo = sprintf('RC-%05d', $siguienteNumero);

            $saldoAnterior = $saldoPendienteActual;
            $saldoPosterior = max(0.0, round($saldoAnterior - $monto, 2));
            $nuevoMontoPagado = round((float) $cuentaBloqueada->monto_pagado + $monto, 2);

            // Determinar nuevo estado de la obligación
            $nuevoEstado = ($saldoPosterior <= 0.001)
                ? EstadoCuentaCobrar::PAGADA
                : EstadoCuentaCobrar::PARCIAL;

            // Actualizar cuenta por cobrar
            $cuentaBloqueada->update([
                'monto_pagado' => $nuevoMontoPagado,
                'saldo_pendiente' => $saldoPosterior,
                'estado' => $nuevoEstado,
            ]);

            // Registrar el abono
            return PagoCliente::create([
                'empresa_id' => $cuentaBloqueada->empresa_id,
                'sucursal_id' => $cuentaBloqueada->sucursal_id,
                'cuenta_por_cobrar_id' => $cuentaBloqueada->id,
                'cliente_id' => $cuentaBloqueada->cliente_id,
                'numero_recibo' => $numeroRecibo,
                'monto' => $monto,
                'metodo_pago' => $metodoPago,
                'referencia_pago' => $referenciaPago,
                'fecha_pago' => $fechaPago,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'notas' => $notas,
                'user_id' => $userId,
                'estado' => EstadoPagoCartera::APLICADO,
            ]);
        });
    }
}
