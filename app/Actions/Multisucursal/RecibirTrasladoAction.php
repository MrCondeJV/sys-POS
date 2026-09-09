<?php

namespace App\Actions\Multisucursal;

use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoTraslado;
use App\Enums\TipoMovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\TrasladoSucursal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecibirTrasladoAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $movimientoAction
    ) {}

    public function execute(
        TrasladoSucursal $traslado,
        ?string $observacionesRecepcion = null,
        ?int $userId = null
    ): TrasladoSucursal {
        if ($traslado->estado !== EstadoTraslado::EN_TRANSITO) {
            throw new InvalidArgumentException("Solo se pueden recibir traslados en tránsito. Estado actual: {$traslado->estado->label()}");
        }

        return DB::transaction(function () use ($traslado, $observacionesRecepcion, $userId) {
            $user = $userId ? \App\Models\User::find($userId) : auth()->user();

            foreach ($traslado->detalles as $detalle) {
                $producto = $detalle->producto;
                $cantidad = (float) $detalle->cantidad_enviada;

                // Replicar o aumentar lote en sucursal destino si aplica
                if ($detalle->lote_id) {
                    $loteOrigen = ProductoLote::find($detalle->lote_id);
                    if ($loteOrigen) {
                        $loteDestino = ProductoLote::firstOrCreate(
                            [
                                'empresa_id' => $traslado->empresa_id,
                                'sucursal_id' => $traslado->sucursal_destino_id,
                                'producto_id' => $detalle->producto_id,
                                'numero_lote' => $loteOrigen->numero_lote,
                            ],
                            [
                                'fecha_vencimiento' => $loteOrigen->fecha_vencimiento,
                                'fecha_fabricacion' => $loteOrigen->fecha_fabricacion,
                                'stock_inicial' => 0,
                                'stock_actual' => 0,
                                'estado' => $loteOrigen->estado,
                            ]
                        );
                        $loteDestino->increment('stock_actual', $cantidad);
                    }
                }

                // Registrar entrada de inventario en destino
                $this->movimientoAction->execute(new MovimientoInventarioDTO(
                    productoId: $detalle->producto_id,
                    sucursalId: $traslado->sucursal_destino_id,
                    tipo: TipoMovimientoInventario::TRASLADO_ENTRADA,
                    cantidad: $cantidad,
                    referencia: $traslado->consecutivo,
                    costoUnitario: (float) $producto->precio_compra,
                    userId: $user?->id,
                    sucursalDestinoId: $traslado->sucursal_origen_id,
                    notas: "Recepción de traslado desde {$traslado->sucursalOrigen->nombre}"
                ));

                $detalle->update([
                    'cantidad_recibida' => $cantidad,
                ]);
            }

            $notas = $traslado->observaciones;
            if ($observacionesRecepcion) {
                $notas = ($notas ? $notas . " | " : "") . "Recepción: " . $observacionesRecepcion;
            }

            $traslado->update([
                'estado' => EstadoTraslado::RECIBIDO,
                'user_receptor_id' => $user?->id ?? 1,
                'fecha_recepcion' => now(),
                'observaciones' => $notas,
            ]);

            return $traslado->fresh(['detalles.producto', 'sucursalOrigen', 'sucursalDestino', 'usuarioReceptor']);
        });
    }
}
