<?php

namespace App\Services;

use App\Enums\TipoNotificacion;
use App\Models\NotificacionSistema;
use App\Models\ProductoLote;
use Illuminate\Support\Collection;

class FarmaciaAlertasService
{
    /**
     * Retorna los lotes vencidos con stock.
     */
    public function obtenerLotesVencidos(int $empresaId, ?int $sucursalId = null): Collection
    {
        $query = ProductoLote::where('empresa_id', $empresaId)
            ->with(['producto', 'sucursal'])
            ->vencidos()
            ->orderBy('fecha_vencimiento', 'asc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return $query->get();
    }

    /**
     * Retorna los lotes próximos a vencer dentro del rango de días indicado (default: 30 días).
     */
    public function obtenerLotesProximosAVencer(int $empresaId, int $dias = 30, ?int $sucursalId = null): Collection
    {
        $query = ProductoLote::where('empresa_id', $empresaId)
            ->with(['producto', 'sucursal'])
            ->proximosAVencer($dias)
            ->orderBy('fecha_vencimiento', 'asc');

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return $query->get();
    }

    /**
     * Genera notificaciones en el sistema si se detectan medicamentos vencidos o por vencer.
     */
    public function sincronizarNotificacionesFarmacia(int $empresaId, ?int $sucursalId = null): int
    {
        $vencidos = $this->obtenerLotesVencidos($empresaId, $sucursalId);
        $notificacionesCreadas = 0;

        foreach ($vencidos as $lote) {
            $mensaje = "ALERTA FARMACIA: El lote {$lote->numero_lote} del producto {$lote->producto->nombre} venció el {$lote->fecha_vencimiento->format('d/m/Y')}. Stock disponible: {$lote->stock_actual}.";

            $existe = NotificacionSistema::where('empresa_id', $empresaId)
                ->where('tipo', TipoNotificacion::VENCIDO)
                ->where('titulo', 'Medicamento Vencido')
                ->whereDate('created_at', now()->toDateString())
                ->where('mensaje', 'like', "%{$lote->numero_lote}%")
                ->exists();

            if (!$existe) {
                NotificacionSistema::create([
                    'empresa_id' => $empresaId,
                    'tipo' => TipoNotificacion::VENCIDO,
                    'titulo' => 'Medicamento Vencido',
                    'mensaje' => $mensaje,
                    'leida' => false,
                ]);
                $notificacionesCreadas++;
            }
        }

        return $notificacionesCreadas;
    }
}
