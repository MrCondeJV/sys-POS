<?php

namespace App\Listeners;

use App\Enums\NivelNotificacion;
use App\Enums\TipoNotificacion;
use App\Events\VentaImportanteEvent;
use App\Models\NotificacionSistema;

class NotificarVentaImportanteListener
{
    public function handle(VentaImportanteEvent $event): void
    {
        $venta = $event->venta;

        $mensaje = sprintf(
            'Se registró la venta %s por un valor total de $%s (%s). Motivo: %s.',
            $venta->numero_venta,
            number_format($venta->total, 2),
            $venta->tipo_pago->value,
            $event->motivo
        );

        NotificacionSistema::create([
            'empresa_id' => $venta->empresa_id,
            'user_id' => null,
            'tipo' => TipoNotificacion::VENTA_IMPORTANTE,
            'nivel' => NivelNotificacion::SUCCESS,
            'titulo' => "Venta Relevante: {$venta->numero_venta}",
            'mensaje' => $mensaje,
            'url_accion' => route('ventas.show', $venta),
            'leida' => false,
            'datos' => [
                'venta_id' => $venta->id,
                'total' => (float) $venta->total,
            ],
        ]);
    }
}
