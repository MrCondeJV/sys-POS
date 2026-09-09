<?php

namespace App\Actions\Inventario;

use App\DTOs\MovimientoInventarioDTO;
use App\Enums\TipoMovimientoInventario;
use App\Models\MovimientoInventario;

class RealizarAjusteInventarioAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $registrarAction
    ) {}

    /**
     * Procesa un ajuste manual de inventario (positivo o negativo).
     */
    public function execute(
        int $productoId,
        int $sucursalId,
        TipoMovimientoInventario $tipo,
        float $cantidad,
        string $motivo,
        ?string $notas = null,
        ?float $costoUnitario = null,
        ?int $userId = null
    ): MovimientoInventario {
        $dto = new MovimientoInventarioDTO(
            productoId: $productoId,
            sucursalId: $sucursalId,
            tipo: $tipo,
            cantidad: abs($cantidad),
            referencia: $motivo,
            costoUnitario: $costoUnitario,
            userId: $userId,
            notas: $notas
        );

        return $this->registrarAction->execute($dto);
    }
}
