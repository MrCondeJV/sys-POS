<?php

namespace App\Actions\Caja;

use App\Enums\EstadoSesionCaja;
use App\Models\CajaSesion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CerrarCajaAction
{
    /**
     * Realiza el arqueo y cierre formal de un turno de caja.
     */
    public function execute(
        CajaSesion $sesion,
        User $auditor,
        float $montoContado,
        ?string $observaciones = null
    ): CajaSesion {
        if ($montoContado < 0) {
            throw new InvalidArgumentException('El monto físico contado no puede ser negativo.');
        }

        return DB::transaction(function () use ($sesion, $auditor, $montoContado, $observaciones) {
            $sesionBloqueada = CajaSesion::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($sesion->id);

            if ($sesionBloqueada->estado !== EstadoSesionCaja::ABIERTA) {
                throw new InvalidArgumentException('Este turno de caja ya se encuentra cerrado.');
            }

            // Recalcular totales antes del corte
            $sesionBloqueada->recalcularTotales();

            $saldoEsperado = $sesionBloqueada->calcularSaldoEsperadoEfectivo();
            $diferencia = round($montoContado - $saldoEsperado, 2);

            $sesionBloqueada->update([
                'user_cierre_id' => $auditor->id,
                'fecha_cierre' => now(),
                'monto_cierre_esperado' => $saldoEsperado,
                'monto_cierre_contado' => $montoContado,
                'diferencia' => $diferencia,
                'estado' => EstadoSesionCaja::CERRADA,
                'observaciones_cierre' => $observaciones,
            ]);

            return $sesionBloqueada->fresh();
        });
    }
}
