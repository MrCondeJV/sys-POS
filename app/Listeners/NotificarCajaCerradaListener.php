<?php

namespace App\Listeners;

use App\Enums\NivelNotificacion;
use App\Enums\TipoNotificacion;
use App\Events\CajaCerradaEvent;
use App\Models\NotificacionSistema;

class NotificarCajaCerradaListener
{
    public function handle(CajaCerradaEvent $event): void
    {
        $sesion = $event->sesion;
        $caja = $sesion->caja;
        $sucursal = $caja->sucursal;

        $diferencia = (float) $sesion->diferencia;
        $nivel = abs($diferencia) > 1000 ? NivelNotificacion::WARNING : NivelNotificacion::INFO;

        $cajeroNombre = $sesion->cajero->name ?? 'Cajero';
        $mensaje = sprintf(
            'La caja %s fue cerrada por %s. Ventas efectivo: $%s. Diferencia de arqueo: $%s.',
            $caja->nombre,
            $cajeroNombre,
            number_format($sesion->total_ventas_efectivo ?? 0, 2),
            number_format($diferencia, 2)
        );

        NotificacionSistema::create([
            'empresa_id' => $sesion->empresa_id,
            'user_id' => null,
            'tipo' => TipoNotificacion::CAJA_CERRADA,
            'nivel' => $nivel,
            'titulo' => "Cierre de Turno: {$caja->nombre}",
            'mensaje' => $mensaje,
            'url_accion' => route('imprimir.caja', $sesion),
            'leida' => false,
            'datos' => [
                'sesion_id' => $sesion->id,
                'caja_id' => $caja->id,
                'diferencia' => $diferencia,
            ],
        ]);
    }
}
