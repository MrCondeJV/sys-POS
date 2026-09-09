<?php

namespace App\DTOs;

use App\Enums\TipoPago;

readonly class RegistrarCompraDTO
{
    /**
     * @param  array<CompraItemDTO>  $items
     */
    public function __construct(
        public int $proveedorId,
        public int $sucursalId,
        public string $numeroFactura,
        public string $fechaEmision,
        public TipoPago $tipoPago,
        public array $items,
        public float $descuento = 0.0,
        public ?string $observaciones = null,
        public ?int $userId = null,
    ) {}

    public function calcularSubtotal(): float
    {
        return array_reduce($this->items, fn ($acc, CompraItemDTO $item) => $acc + $item->getSubtotal(), 0.0);
    }

    public function calcularImpuestos(): float
    {
        return array_reduce($this->items, fn ($acc, CompraItemDTO $item) => $acc + $item->getValorIva(), 0.0);
    }

    public function calcularTotal(): float
    {
        return max(0.0, round($this->calcularSubtotal() + $this->calcularImpuestos() - $this->descuento, 2));
    }
}
