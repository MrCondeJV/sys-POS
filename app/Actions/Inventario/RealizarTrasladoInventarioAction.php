<?php

namespace App\Actions\Inventario;

use App\DTOs\MovimientoInventarioDTO;
use App\Enums\TipoMovimientoInventario;
use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RealizarTrasladoInventarioAction
{
    public function __construct(
        protected RegistrarMovimientoInventarioAction $registrarAction
    ) {}

    /**
     * Transfiere existencias entre dos sucursales de la misma empresa de manera atómica.
     *
     * @return array{salida: MovimientoInventario, entrada: MovimientoInventario}
     *
     * @throws InvalidArgumentException|StockInsuficienteException
     */
    public function execute(
        int $productoId,
        int $sucursalOrigenId,
        int $sucursalDestinoId,
        float $cantidad,
        string $motivo,
        ?string $notas = null,
        ?int $userId = null
    ): array {
        if ($sucursalOrigenId === $sucursalDestinoId) {
            throw new InvalidArgumentException('La sucursal de origen y destino no pueden ser la misma.');
        }

        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad a trasladar debe ser mayor a cero.');
        }

        $sucursalOrigen = Sucursal::findOrFail($sucursalOrigenId);
        $sucursalDestino = Sucursal::findOrFail($sucursalDestinoId);

        if ($sucursalOrigen->empresa_id !== $sucursalDestino->empresa_id) {
            throw new InvalidArgumentException('No se pueden realizar traslados entre sucursales de diferentes empresas.');
        }

        return DB::transaction(function () use (
            $productoId,
            $sucursalOrigen,
            $sucursalDestino,
            $cantidad,
            $motivo,
            $notas,
            $userId
        ) {
            $notaCompleta = $motivo.($notas ? " | {$notas}" : '');

            // 1. Asentar salida por traslado en sucursal de origen
            $dtoSalida = new MovimientoInventarioDTO(
                productoId: $productoId,
                sucursalId: $sucursalOrigen->id,
                tipo: TipoMovimientoInventario::TRASLADO_SALIDA,
                cantidad: $cantidad,
                referencia: "Traslado hacia {$sucursalDestino->nombre}",
                userId: $userId,
                sucursalDestinoId: $sucursalDestino->id,
                notas: $notaCompleta
            );
            $movSalida = $this->registrarAction->execute($dtoSalida);

            // 2. Asentar entrada por traslado en sucursal de destino
            $dtoEntrada = new MovimientoInventarioDTO(
                productoId: $productoId,
                sucursalId: $sucursalDestino->id,
                tipo: TipoMovimientoInventario::TRASLADO_ENTRADA,
                cantidad: $cantidad,
                referencia: "Traslado recibido desde {$sucursalOrigen->nombre}",
                costoUnitario: $movSalida->costo_unitario,
                userId: $userId,
                sucursalDestinoId: $sucursalOrigen->id,
                notas: $notaCompleta
            );
            $movEntrada = $this->registrarAction->execute($dtoEntrada);

            return [
                'salida' => $movSalida,
                'entrada' => $movEntrada,
            ];
        });
    }
}
