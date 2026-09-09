<?php

namespace App\Actions\Ventas;

use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\CajaSesion;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizarVentasOfflineAction
{
    public function __construct(
        protected RegistrarVentaAction $registrarVentaAction
    ) {}

    /**
     * Procesa un lote de transacciones efectuadas fuera de línea (offline).
     *
     * @param array<int, array{
     *     client_transaction_id: string,
     *     items: array,
     *     cliente_id?: ?int,
     *     metodo_pago?: string,
     *     tipo_pago?: string,
     *     pago_con?: ?float,
     *     observaciones?: ?string,
     *     fecha_offline?: ?string
     * }> $ventasOffline
     * @return array{
     *     total: int,
     *     procesadas: int,
     *     duplicadas: int,
     *     fallidas: int,
     *     resultados: array
     * }
     */
    public function execute(
        int $empresaId,
        int $sucursalId,
        int $userId,
        array $ventasOffline
    ): array {
        $procesadas = 0;
        $duplicadas = 0;
        $fallidas = 0;
        $resultados = [];

        // Resolver sesión de caja activa en la sucursal o null
        $cajaSesion = CajaSesion::where('sucursal_id', $sucursalId)
            ->where('estado', 'ABIERTA')
            ->latest('id')
            ->first();
        $cajaSesionId = $cajaSesion?->id;

        foreach ($ventasOffline as $index => $itemVenta) {
            $clientTxId = $itemVenta['client_transaction_id'] ?? null;

            if (empty($clientTxId)) {
                $fallidas++;
                $resultados[] = [
                    'indice' => $index,
                    'status' => 'error',
                    'mensaje' => 'client_transaction_id es obligatorio para sincronización offline.',
                ];
                continue;
            }

            // 1. Idempotencia: Verificar si ya existe una venta con ese client_transaction_id en la empresa
            $existente = Venta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('client_transaction_id', $clientTxId)
                ->first();

            if ($existente) {
                $duplicadas++;
                $resultados[] = [
                    'client_transaction_id' => $clientTxId,
                    'status' => 'duplicate_ignored',
                    'venta_id' => $existente->id,
                    'numero_venta' => $existente->numero_venta,
                    'mensaje' => 'Transacción ya procesada previamente (idempotencia garantizada).',
                ];
                continue;
            }

            // 2. Registrar venta
            try {
                $tipoPago = isset($itemVenta['tipo_pago']) && strtoupper($itemVenta['tipo_pago']) === 'CREDITO'
                    ? TipoPago::CREDITO
                    : TipoPago::CONTADO;

                $metodoPago = $itemVenta['metodo_pago'] ?? 'EFECTIVO';
                $observaciones = ($itemVenta['observaciones'] ?? '') . ' [Sincronizada Offline]';

                $venta = $this->registrarVentaAction->execute(
                    empresaId: $empresaId,
                    sucursalId: $sucursalId,
                    userId: $userId,
                    items: $itemVenta['items'],
                    clienteId: $itemVenta['cliente_id'] ?? null,
                    tipoPago: $tipoPago,
                    metodoPago: $metodoPago,
                    tipoComprobante: TipoComprobanteVenta::TICKET,
                    cajaSesionId: $cajaSesionId,
                    pagoCon: isset($itemVenta['pago_con']) ? (float) $itemVenta['pago_con'] : null,
                    observaciones: trim($observaciones)
                );

                // Marcar bandera offline e ID de cliente
                $venta->update([
                    'client_transaction_id' => $clientTxId,
                    'sincronizada_offline' => true,
                ]);

                $procesadas++;
                $resultados[] = [
                    'client_transaction_id' => $clientTxId,
                    'status' => 'synced',
                    'venta_id' => $venta->id,
                    'numero_venta' => $venta->numero_venta,
                    'total' => (float) $venta->total,
                ];
            } catch (\Throwable $e) {
                $fallidas++;
                Log::warning("Error sincronizando venta offline {$clientTxId}: {$e->getMessage()}");
                $resultados[] = [
                    'client_transaction_id' => $clientTxId,
                    'status' => 'error',
                    'mensaje' => $e->getMessage(),
                ];
            }
        }

        return [
            'total' => count($ventasOffline),
            'procesadas' => $procesadas,
            'duplicadas' => $duplicadas,
            'fallidas' => $fallidas,
            'resultados' => $resultados,
        ];
    }
}
