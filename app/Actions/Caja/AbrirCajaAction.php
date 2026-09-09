<?php

namespace App\Actions\Caja;

use App\Enums\EstadoCaja;
use App\Enums\EstadoSesionCaja;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AbrirCajaAction
{
    /**
     * Inicia un nuevo turno o sesión de caja con un fondo inicial.
     */
    public function execute(
        Caja $caja,
        User $cajero,
        float $montoApertura,
        ?string $observaciones = null
    ): CajaSesion {
        if ($montoApertura < 0) {
            throw new InvalidArgumentException('El monto de apertura (fondo inicial) no puede ser negativo.');
        }

        return DB::transaction(function () use ($caja, $cajero, $montoApertura, $observaciones) {
            // Bloqueo pesimista de la caja para evitar doble apertura simultánea
            $cajaBloqueada = Caja::withoutGlobalScopes()
                ->lockForUpdate()
                ->findOrFail($caja->id);

            if ($cajaBloqueada->estado === EstadoCaja::INACTIVA) {
                throw new InvalidArgumentException('No se puede abrir una caja que está inactiva.');
            }

            $sesionAbierta = CajaSesion::withoutGlobalScopes()
                ->where('caja_id', $cajaBloqueada->id)
                ->where('estado', EstadoSesionCaja::ABIERTA->value)
                ->lockForUpdate()
                ->first();

            if ($sesionAbierta) {
                throw new InvalidArgumentException(sprintf(
                    'La caja "%s" ya cuenta con un turno abierto iniciado por %s el %s.',
                    $cajaBloqueada->nombre,
                    $sesionAbierta->cajero?->name ?? 'otro usuario',
                    $sesionAbierta->fecha_apertura->format('d/m/Y H:i')
                ));
            }

            $sesion = CajaSesion::create([
                'empresa_id' => $cajaBloqueada->empresa_id,
                'sucursal_id' => $cajaBloqueada->sucursal_id,
                'caja_id' => $cajaBloqueada->id,
                'user_id' => $cajero->id,
                'fecha_apertura' => now(),
                'monto_apertura' => $montoApertura,
                'estado' => EstadoSesionCaja::ABIERTA,
                'observaciones_apertura' => $observaciones,
            ]);

            \App\Actions\Auditoria\RegistrarAuditoriaAction::execute(
                accion: 'APERTURA_CAJA',
                modulo: 'CAJA',
                model: $sesion,
                datosAnteriores: null,
                datosNuevos: [
                    'caja_id' => $cajaBloqueada->id,
                    'caja_nombre' => $cajaBloqueada->nombre,
                    'monto_apertura' => $montoApertura,
                    'cajero_id' => $cajero->id,
                ],
                descripcion: "Apertura de turno en {$cajaBloqueada->nombre} con fondo inicial de \${$montoApertura} por {$cajero->name}",
                usuario: $cajero,
                empresaId: $cajaBloqueada->empresa_id
            );

            return $sesion;
        });
    }
}
