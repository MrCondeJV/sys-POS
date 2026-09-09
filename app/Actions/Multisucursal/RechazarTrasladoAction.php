<?php

namespace App\Actions\Multisucursal;

use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoTraslado;
use App\Enums\TipoMovimientoInventario;
use App\Models\ProductoLote;
use App\Models\TrasladoSucursal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RechazarTrasladoAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $movimientoAction
    ) {}

    public function execute(
        TrasladoSucursal $traslado,
        string $motivoRechazo,
        ?int $userId = null
    ): TrasladoSucursal {
        if ($traslado->estado !== EstadoTraslado::EN_TRANSITO) {
            throw new InvalidArgumentException("Solo se pueden rechazar traslados en tránsito. Estado actual: {$traslado->estado->label()}");
        }

        return DB::transaction(function () use ($traslado, $motivoRechazo, $userId) {
            $user = $userId ? \App\Models\User::find($userId) : auth()->user();

            // Reversar stock a la sucursal de origen
            foreach ($traslado->detalles as $detalle) {
                $cantidad = (float) $detalle->cantidad_enviada;

                // Reintegrar lote si aplica
                if ($detalle->lote_id) {
                    $lote = ProductoLote::find($detalle->lote_id);
                    if ($lote) {
                        $lote->increment('stock_actual', $cantidad);
                    }
                }

                // Reversar entrada a origen
                $this->movimientoAction->execute(new MovimientoInventarioDTO(
                    productoId: $detalle->producto_id,
                    sucursalId: $traslado->sucursal_origen_id,
                    tipo: TipoMovimientoInventario::TRASLADO_ENTRADA,
                    cantidad: $cantidad,
                    referencia: $traslado->consecutivo . '-RECHAZO',
                    costoUnitario: (float) $detalle->producto->precio_compra,
                    userId: $user?->id,
                    sucursalDestinoId: $traslado->sucursal_destino_id,
                    notas: "Reversión por rechazo de traslado hacia {$traslado->sucursalDestino->nombre}: {$motivoRechazo}"
                ));
            }

            $notas = ($traslado->observaciones ? $traslado->observaciones . " | " : "") . "Rechazado: " . $motivoRechazo;

            $traslado->update([
                'estado' => EstadoTraslado::RECHAZADO,
                'user_receptor_id' => $user?->id ?? 1,
                'fecha_recepcion' => now(),
                'observaciones' => $notas,
            ]);

            return $traslado->fresh(['detalles.producto', 'sucursalOrigen', 'sucursalDestino', 'usuarioReceptor']);
        });
    }
}
