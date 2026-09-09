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

        $movimiento = $this->registrarAction->execute($dto);

        \App\Actions\Auditoria\RegistrarAuditoriaAction::execute(
            accion: 'AJUSTE_INVENTARIO',
            modulo: 'INVENTARIO',
            model: $movimiento,
            datosAnteriores: [
                'stock_anterior' => $movimiento->stock_anterior,
            ],
            datosNuevos: [
                'stock_posterior' => $movimiento->stock_posterior,
                'cantidad' => $movimiento->cantidad,
                'tipo' => $tipo->value,
                'motivo' => $motivo,
            ],
            descripcion: "Ajuste de inventario ({$tipo->label()}) de {$cantidad} unidades. Motivo: {$motivo}. Nuevo stock: {$movimiento->stock_posterior}",
            usuario: $userId ? \App\Models\User::find($userId) : null,
            empresaId: $movimiento->empresa_id
        );

        return $movimiento;
    }
}
