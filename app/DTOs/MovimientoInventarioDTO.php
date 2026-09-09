<?php

namespace App\DTOs;

use App\Enums\TipoMovimientoInventario;

readonly class MovimientoInventarioDTO
{
    public function __construct(
        public int $productoId,
        public int $sucursalId,
        public TipoMovimientoInventario $tipo,
        public float $cantidad,
        public string $referencia,
        public ?float $costoUnitario = null,
        public ?int $userId = null,
        public ?int $sucursalDestinoId = null,
        public ?string $notas = null,
    ) {}
}
