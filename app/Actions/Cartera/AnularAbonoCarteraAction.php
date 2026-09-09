<?php

namespace App\Actions\Cartera;

use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoPagoCartera;
use App\Models\CuentaPorCobrar;
use App\Models\PagoCliente;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnularAbonoCarteraAction
{
    /**
     * Anula un abono y revierte el saldo pendiente y estado de la cuenta por cobrar.
     */
    public function execute(PagoCliente $pago): void
    {
        DB::transaction(function () use ($pago) {
            $pagoBloqueado = PagoCliente::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($pago->id);

            if ($pagoBloqueado->estado === EstadoPagoCartera::ANULADO) {
                throw new InvalidArgumentException('Este abono ya se encuentra anulado.');
            }

            $cuenta = CuentaPorCobrar::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($pagoBloqueado->cuenta_por_cobrar_id);

            $montoRevertir = (float) $pagoBloqueado->monto;
            $nuevoMontoPagado = max(0.0, round((float) $cuenta->monto_pagado - $montoRevertir, 2));
            $nuevoSaldoPendiente = min((float) $cuenta->monto_total, round((float) $cuenta->saldo_pendiente + $montoRevertir, 2));

            $nuevoEstado = ($nuevoSaldoPendiente >= (float) $cuenta->monto_total)
                ? EstadoCuentaCobrar::PENDIENTE
                : EstadoCuentaCobrar::PARCIAL;

            $cuenta->update([
                'monto_pagado' => $nuevoMontoPagado,
                'saldo_pendiente' => $nuevoSaldoPendiente,
                'estado' => $nuevoEstado,
            ]);

            $pagoBloqueado->update([
                'estado' => EstadoPagoCartera::ANULADO,
            ]);
        });
    }
}
