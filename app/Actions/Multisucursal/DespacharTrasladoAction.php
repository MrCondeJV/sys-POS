<?php

namespace App\Actions\Multisucursal;

use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoTraslado;
use App\Enums\TipoMovimientoInventario;
use App\Exceptions\StockInsuficienteException;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\Sucursal;
use App\Models\TrasladoDetalle;
use App\Models\TrasladoSucursal;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DespacharTrasladoAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $movimientoAction
    ) {}

    /**
     * @param array<int, array{producto_id: int, cantidad: float, lote_id?: ?int, observaciones?: ?string}> $items
     */
    public function execute(
        int $sucursalOrigenId,
        int $sucursalDestinoId,
        string $motivo,
        array $items,
        ?string $observaciones = null,
        ?int $userId = null
    ): TrasladoSucursal {
        if ($sucursalOrigenId === $sucursalDestinoId) {
            throw new InvalidArgumentException('La sucursal de origen y destino no pueden ser la misma.');
        }

        $sucursalOrigen = Sucursal::findOrFail($sucursalOrigenId);
        $sucursalDestino = Sucursal::findOrFail($sucursalDestinoId);

        if ($sucursalOrigen->empresa_id !== $sucursalDestino->empresa_id) {
            throw new InvalidArgumentException('No se pueden realizar traslados entre sucursales de diferentes empresas.');
        }

        if (empty($items)) {
            throw new InvalidArgumentException('Debe incluir al menos un producto a trasladar.');
        }

        return DB::transaction(function () use (
            $sucursalOrigenId,
            $sucursalDestinoId,
            $motivo,
            $items,
            $observaciones,
            $userId
        ) {
            $empresaId = CompanyContext::getId() ?? Sucursal::findOrFail($sucursalOrigenId)->empresa_id;
            $user = $userId ? \App\Models\User::find($userId) : auth()->user();

            // 1. Generar consecutivo correlativo por empresa
            $ultimoNumero = TrasladoSucursal::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->count() + 1;
            $consecutivo = 'TRAS-' . str_pad((string) $ultimoNumero, 6, '0', STR_PAD_LEFT);

            // 2. Crear encabezado del traslado en estado EN_TRANSITO
            $traslado = TrasladoSucursal::create([
                'empresa_id' => $empresaId,
                'sucursal_origen_id' => $sucursalOrigenId,
                'sucursal_destino_id' => $sucursalDestinoId,
                'consecutivo' => $consecutivo,
                'user_id' => $user?->id ?? 1,
                'estado' => EstadoTraslado::EN_TRANSITO,
                'motivo' => $motivo,
                'observaciones' => $observaciones,
                'fecha_envio' => now(),
            ]);

            // 3. Procesar cada ítem: descontar stock de origen y registrar detalle
            foreach ($items as $item) {
                $productoId = (int) $item['producto_id'];
                $cantidad = (float) $item['cantidad'];
                $loteId = !empty($item['lote_id']) ? (int) $item['lote_id'] : null;

                $producto = Producto::findOrFail($productoId);

                // Si maneja lote y se especificó lote_id, descontar del lote también
                if ($loteId) {
                    $lote = ProductoLote::findOrFail($loteId);
                    $lote->descontarStock($cantidad);
                }

                // Registrar salida de inventario en origen
                $this->movimientoAction->execute(new MovimientoInventarioDTO(
                    productoId: $productoId,
                    sucursalId: $sucursalOrigenId,
                    tipo: TipoMovimientoInventario::TRASLADO_SALIDA,
                    cantidad: $cantidad,
                    referencia: $consecutivo,
                    costoUnitario: (float) $producto->precio_compra,
                    userId: $user?->id,
                    sucursalDestinoId: $sucursalDestinoId,
                    notas: "Traslado hacia {$traslado->sucursalDestino->nombre}: {$motivo}"
                ));

                TrasladoDetalle::create([
                    'traslado_sucursal_id' => $traslado->id,
                    'producto_id' => $productoId,
                    'lote_id' => $loteId,
                    'cantidad_enviada' => $cantidad,
                    'cantidad_recibida' => 0,
                    'observaciones' => $item['observaciones'] ?? null,
                ]);
            }

            return $traslado->load(['detalles.producto', 'sucursalOrigen', 'sucursalDestino', 'usuarioDespacho']);
        });
    }
}
