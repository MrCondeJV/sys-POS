<?php

namespace App\Actions\Compras;

use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoCompra;
use App\Enums\TipoMovimientoInventario;
use App\Exceptions\StockInsuficienteException;
use App\Models\Compra;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnularCompraAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $movimientoAction
    ) {}

    /**
     * Anula una compra registrada y revierte las existencias ingresadas al inventario.
     *
     * @throws InvalidArgumentException|StockInsuficienteException
     */
    public function execute(Compra $compra, ?string $motivo = null, ?int $userId = null): Compra
    {
        if (! $compra->isRegistrada()) {
            throw new InvalidArgumentException('Solo se pueden anular compras en estado REGISTRADA.');
        }

        return DB::transaction(function () use ($compra, $motivo, $userId) {
            // 1. Validar que la sucursal conserve suficiente stock para cada producto
            foreach ($compra->detalles as $detalle) {
                $inv = Inventario::where('sucursal_id', $compra->sucursal_id)
                    ->where('producto_id', $detalle->producto_id)
                    ->lockForUpdate()
                    ->first();

                $stockActual = $inv ? (float) $inv->stock : 0.0;
                $requerido = (float) $detalle->cantidad;

                if ($stockActual < $requerido) {
                    throw new StockInsuficienteException(
                        productoNombre: $detalle->producto?->nombre ?? 'Producto #'.$detalle->producto_id,
                        stockActual: $stockActual,
                        cantidadRequerida: $requerido,
                        message: "No se puede anular la compra: el stock actual de '{$detalle->producto?->nombre}' en la sede ({$stockActual}) es menor a la cantidad adquirida ({$requerido}). Es posible que parte de la mercancía ya haya sido vendida o trasladada."
                    );
                }
            }

            // 2. Revertir inventario mediante movimiento de devolución a proveedor
            foreach ($compra->detalles as $detalle) {
                $movDTO = new MovimientoInventarioDTO(
                    productoId: $detalle->producto_id,
                    sucursalId: $compra->sucursal_id,
                    tipo: TipoMovimientoInventario::DEVOLUCION_PROVEEDOR,
                    cantidad: (float) $detalle->cantidad,
                    referencia: "Anulación Factura de Compra #{$compra->numero_factura}",
                    costoUnitario: (float) $detalle->costo_unitario,
                    userId: $userId ?? auth()->id(),
                    notas: $motivo ? "Motivo anulación: {$motivo}" : 'Anulación de compra de proveedor'
                );
                $this->movimientoAction->execute($movDTO);
            }

            // 3. Marcar la compra como ANULADA
            $compra->estado = EstadoCompra::ANULADA;
            $anotacion = '[ANULADA el '.now()->format('d/m/Y H:i').']: '.($motivo ?? 'Anulación de compra');
            $compra->observaciones = trim($compra->observaciones."\n".$anotacion);
            $compra->save();

            return $compra;
        });
    }
}
