<?php

namespace App\Actions\Compras;

use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\CompraItemDTO;
use App\DTOs\MovimientoInventarioDTO;
use App\DTOs\RegistrarCompraDTO;
use App\Enums\EstadoCompra;
use App\Enums\TipoMovimientoInventario;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarCompraAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $movimientoAction
    ) {}

    /**
     * Registra una compra de mercancías transaccionalmente e ingresa los artículos al inventario.
     */
    public function execute(RegistrarCompraDTO $dto): Compra
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException('La compra debe contener al menos un producto.');
        }

        $proveedor = Proveedor::findOrFail($dto->proveedorId);
        $sucursal = Sucursal::findOrFail($dto->sucursalId);

        return DB::transaction(function () use ($dto, $proveedor, $sucursal) {
            // 1. Crear cabecera de la compra
            $compra = Compra::create([
                'empresa_id' => $proveedor->empresa_id,
                'sucursal_id' => $sucursal->id,
                'proveedor_id' => $proveedor->id,
                'user_id' => $dto->userId ?? auth()->id(),
                'numero_factura' => $dto->numeroFactura,
                'fecha_emision' => $dto->fechaEmision,
                'subtotal' => $dto->calcularSubtotal(),
                'impuestos' => $dto->calcularImpuestos(),
                'descuento' => $dto->descuento,
                'total' => $dto->calcularTotal(),
                'tipo_pago' => $dto->tipoPago,
                'estado' => EstadoCompra::REGISTRADA,
                'observaciones' => $dto->observaciones,
            ]);

            // 2. Procesar cada línea de compra y asentar en el Kardex
            foreach ($dto->items as $item) {
                /** @var CompraItemDTO $item */
                $detalle = CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $item->productoId,
                    'cantidad' => $item->cantidad,
                    'costo_unitario' => $item->costoUnitario,
                    'porcentaje_iva' => $item->porcentajeIva,
                    'valor_iva' => $item->getValorIva(),
                    'subtotal' => $item->getSubtotal(),
                    'total' => $item->getTotal(),
                ]);

                // Asentar movimiento en inventario / Kardex (ENTRADA_COMPRA)
                $movDTO = new MovimientoInventarioDTO(
                    productoId: $item->productoId,
                    sucursalId: $sucursal->id,
                    tipo: TipoMovimientoInventario::ENTRADA_COMPRA,
                    cantidad: $item->cantidad,
                    referencia: "Factura de Compra #{$compra->numero_factura} ({$proveedor->razon_social})",
                    costoUnitario: $item->costoUnitario,
                    userId: $dto->userId ?? auth()->id(),
                    notas: "Recepción de compra #{$compra->id}"
                );
                $this->movimientoAction->execute($movDTO);

                // Actualizar costo de reposición del producto
                Producto::withoutGlobalScopes()
                    ->where('id', $item->productoId)
                    ->update(['precio_compra' => $item->costoUnitario]);
            }

            return $compra->load(['detalles.producto', 'proveedor', 'sucursal']);
        });
    }
}
