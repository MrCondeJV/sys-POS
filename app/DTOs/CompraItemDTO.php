<?php

namespace App\DTOs;

readonly class CompraItemDTO
{
    public function __construct(
        public int $productoId,
        public float $cantidad,
        public float $costoUnitario,
        public float $porcentajeIva = 0.0,
    ) {}

    public function getSubtotal(): float
    {
        return round($this->cantidad * $this->costoUnitario, 2);
    }

    public function getValorIva(): float
    {
        return round($this->getSubtotal() * ($this->porcentajeIva / 100), 2);
    }

    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getValorIva(), 2);
    }
}
