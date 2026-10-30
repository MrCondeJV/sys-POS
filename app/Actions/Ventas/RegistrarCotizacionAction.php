<?php

namespace App\Actions\Ventas;

use App\Enums\EstadoCotizacion;
use App\Enums\EstadoGeneral;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\ListaPrecio;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarCotizacionAction
{
    /**
     * Registra una cotización. No mueve inventario, caja ni cartera: solo documenta precios ofrecidos.
     *
     * @param array<int, array{producto_id: int, cantidad: float, descuento?: float}> $items
     */
    public function execute(
        int $empresaId,
        ?int $sucursalId,
        int $userId,
        array $items,
        ?int $clienteId = null,
        ?string $fechaValidez = null,
        ?string $observaciones = null
    ): Cotizacion {
        if (empty($items)) {
            throw new InvalidArgumentException('La cotización debe contener al menos un producto.');
        }

        return DB::transaction(function () use ($empresaId, $sucursalId, $userId, $items, $clienteId, $fechaValidez, $observaciones) {
            $cliente = $clienteId
                ? Cliente::withoutGlobalScopes()->where('empresa_id', $empresaId)->findOrFail($clienteId)
                : null;

            // Misma regla de precios que la venta: lista asignada al cliente o precio de catálogo
            $listaPrecio = null;
            if ($cliente && $cliente->lista_precio_id) {
                $listaPrecio = ListaPrecio::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->where('estado', EstadoGeneral::ACTIVO->value)
                    ->with('detalles')
                    ->find($cliente->lista_precio_id);
            }

            // Consecutivo correlativo por empresa (COT-00001)
            $ultimoNumero = Cotizacion::withoutGlobalScopes()
                ->withTrashed()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->max('numero_cotizacion');

            $consecutivo = ($ultimoNumero && preg_match('/COT-(\d+)/', $ultimoNumero, $matches))
                ? (int) $matches[1] + 1
                : 1;

            $subtotal = 0.0;
            $descuento = 0.0;
            $impuesto = 0.0;
            $detalles = [];

            foreach ($items as $item) {
                $cantidad = (float) $item['cantidad'];
                if ($cantidad <= 0) {
                    throw new InvalidArgumentException('La cantidad de cada ítem debe ser estrictamente mayor a cero.');
                }

                $producto = Producto::withoutGlobalScopes()
                    ->where('empresa_id', $empresaId)
                    ->with('impuesto')
                    ->findOrFail($item['producto_id']);

                if ($producto->estado !== EstadoGeneral::ACTIVO) {
                    throw new InvalidArgumentException("El producto '{$producto->nombre}' se encuentra inactivo y no puede cotizarse.");
                }

                // Precio e IVA siempre del catálogo, nunca del formulario
                $precioUnitario = $listaPrecio ? $listaPrecio->calcularPrecio($producto) : (float) $producto->precio_venta;
                $impuestoPorcentaje = (float) $producto->porcentaje_iva;

                $descuentoItem = (float) ($item['descuento'] ?? 0);
                $bruto = round($cantidad * $precioUnitario, 2);
                if ($descuentoItem < 0 || $descuentoItem > $bruto) {
                    throw new InvalidArgumentException("El descuento del producto '{$producto->nombre}' no es válido.");
                }

                $subtotalLinea = round($bruto - $descuentoItem, 2);
                $impuestoLinea = round($subtotalLinea * ($impuestoPorcentaje / 100), 2);

                $subtotal += $bruto;
                $descuento += $descuentoItem;
                $impuesto += $impuestoLinea;

                $detalles[] = [
                    'empresa_id' => $empresaId,
                    'producto_id' => $producto->id,
                    'descripcion' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuentoItem,
                    'impuesto_porcentaje' => $impuestoPorcentaje,
                    'impuesto_monto' => $impuestoLinea,
                    'subtotal' => $subtotalLinea,
                    'total' => round($subtotalLinea + $impuestoLinea, 2),
                ];
            }

            $cotizacion = Cotizacion::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'cliente_id' => $cliente?->id,
                'lista_precio_id' => $listaPrecio?->id,
                'user_id' => $userId,
                'numero_cotizacion' => sprintf('COT-%05d', $consecutivo),
                'fecha' => now()->toDateString(),
                'fecha_validez' => $fechaValidez,
                'subtotal' => round($subtotal, 2),
                'descuento' => round($descuento, 2),
                'impuesto' => round($impuesto, 2),
                'total' => round($subtotal - $descuento + $impuesto, 2),
                'estado' => EstadoCotizacion::PENDIENTE,
                'observaciones' => $observaciones,
            ]);

            $cotizacion->detalles()->createMany($detalles);

            return $cotizacion->load('detalles');
        });
    }
}
