<?php

namespace App\Actions\Inventario;

use App\DTOs\MovimientoInventarioDTO;
use App\Exceptions\StockInsuficienteException;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class RegistrarMovimientoInventarioAction
{
    /**
     * Ejecuta el registro transaccional e inmutable de un movimiento de inventario.
     *
     * @throws StockInsuficienteException
     */
    public function execute(MovimientoInventarioDTO $dto): MovimientoInventario
    {
        return DB::transaction(function () use ($dto) {
            // 1. Obtener o inicializar el registro de inventario para la sucursal con bloqueo pesimista
            $inventario = Inventario::where('sucursal_id', $dto->sucursalId)
                ->where('producto_id', $dto->productoId)
                ->lockForUpdate()
                ->first();

            if (! $inventario) {
                $producto = Producto::findOrFail($dto->productoId);
                $inventario = Inventario::create([
                    'empresa_id' => $producto->empresa_id,
                    'sucursal_id' => $dto->sucursalId,
                    'producto_id' => $dto->productoId,
                    'stock' => 0,
                    'stock_minimo' => $producto->stock_minimo ?? 0,
                ]);
            }

            $stockAnterior = (float) $inventario->stock;
            $cantidad = (float) $dto->cantidad;

            // 2. Calcular nuevo stock y validar que no quede negativo en operaciones de salida
            if ($dto->tipo->esEntrada()) {
                $stockPosterior = round($stockAnterior + $cantidad, 2);
            } else {
                if ($stockAnterior < $cantidad) {
                    $productoNombre = $inventario->producto?->nombre ?? 'Producto #'.$dto->productoId;
                    throw new StockInsuficienteException(
                        productoNombre: $productoNombre,
                        stockActual: $stockAnterior,
                        cantidadRequerida: $cantidad
                    );
                }
                $stockPosterior = round($stockAnterior - $cantidad, 2);
            }

            // 3. Persistir existencias actualizadas en la sucursal
            $inventario->stock = $stockPosterior;
            $inventario->save();

            // 4. Sincronizar stock total consolidado en la tabla de productos
            $stockConsolidado = (float) Inventario::where('producto_id', $dto->productoId)->sum('stock');
            Producto::withoutGlobalScopes()
                ->where('id', $dto->productoId)
                ->update(['stock' => $stockConsolidado]);

            // Fase 20: Verificar alerta de Stock Bajo en salida
            if ($dto->tipo->esSalida()) {
                $inventario->loadMissing(['producto', 'sucursal']);
                if ($inventario->producto && $stockPosterior <= (float) $inventario->producto->stock_minimo) {
                    \App\Events\StockBajoEvent::dispatch(
                        $inventario->producto,
                        $inventario->sucursal,
                        $stockPosterior,
                        (float) $inventario->producto->stock_minimo
                    );
                }
            }

            // 5. Determinar costo unitario histórico si no fue provisto
            $costoUnitario = $dto->costoUnitario !== null
                ? $dto->costoUnitario
                : (float) ($inventario->producto?->precio_compra ?? 0);

            // 6. Asentar el registro inmutable en el Kardex
            return MovimientoInventario::create([
                'empresa_id' => $inventario->empresa_id,
                'sucursal_id' => $dto->sucursalId,
                'sucursal_destino_id' => $dto->sucursalDestinoId,
                'producto_id' => $dto->productoId,
                'user_id' => $dto->userId ?? auth()->id(),
                'tipo' => $dto->tipo,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockPosterior,
                'referencia' => $dto->referencia,
                'notas' => $dto->notas,
            ]);
        });
    }
}
