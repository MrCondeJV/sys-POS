<?php

namespace App\Listeners;

use App\Enums\NivelNotificacion;
use App\Enums\TipoNotificacion;
use App\Events\StockBajoEvent;
use App\Models\NotificacionSistema;

class NotificarStockBajoListener
{
    public function handle(StockBajoEvent $event): void
    {
        $producto = $event->producto;
        $sucursal = $event->sucursal;

        // Evitar duplicar alertas no leídas para el mismo producto en la misma sucursal
        $existe = NotificacionSistema::where('empresa_id', $producto->empresa_id)
            ->where('tipo', TipoNotificacion::STOCK_BAJO)
            ->where('leida', false)
            ->whereJsonContains('datos->producto_id', $producto->id)
            ->whereJsonContains('datos->sucursal_id', $sucursal->id)
            ->exists();

        if ($existe) {
            return;
        }

        $nivel = $event->stockActual <= 0 ? NivelNotificacion::DANGER : NivelNotificacion::WARNING;
        $titulo = $event->stockActual <= 0
            ? "¡Agotado! {$producto->nombre}"
            : "Stock Bajo: {$producto->nombre}";

        $mensaje = sprintf(
            'El producto %s (código: %s) tiene una existencia de %s unidades en la sede %s (Mínimo requerido: %s).',
            $producto->nombre,
            $producto->codigo,
            number_format($event->stockActual, 2),
            $sucursal->nombre,
            number_format($event->stockMinimo, 2)
        );

        NotificacionSistema::create([
            'empresa_id' => $producto->empresa_id,
            'user_id' => null, // General para administradores de la empresa
            'tipo' => TipoNotificacion::STOCK_BAJO,
            'nivel' => $nivel,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url_accion' => route('inventario.index', ['producto_id' => $producto->id]),
            'leida' => false,
            'datos' => [
                'producto_id' => $producto->id,
                'sucursal_id' => $sucursal->id,
                'stock_actual' => $event->stockActual,
                'stock_minimo' => $event->stockMinimo,
            ],
        ]);
    }
}
