<?php

namespace App\Console\Commands;

use App\Actions\Wompi\ProcesarResultadoTransaccionWompiAction;
use App\Models\PagoSuscripcion;
use App\Services\Wompi\WompiService;
use Illuminate\Console\Command;

class ReconciliarPagosWompi extends Command
{
    protected $signature = 'wompi:reconciliar-pagos';

    protected $description = 'Reconcilia transacciones de Wompi pendientes consultando el API oficial';

    public function handle(
        WompiService $wompiService,
        ProcesarResultadoTransaccionWompiAction $procesarAction
    ): int {
        $this->info('Consultando pagos PENDING creados hace más de 15 minutos...');

        $pagos = PagoSuscripcion::where('status', 'PENDING')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->where('created_at', '>=', now()->subDays(2))
            ->get();

        if ($pagos->isEmpty()) {
            $this->info('No hay transacciones pendientes para reconciliar.');

            return self::SUCCESS;
        }

        $reconciliados = 0;

        foreach ($pagos as $pago) {
            $tx = null;

            if ($pago->wompi_transaction_id) {
                $tx = $wompiService->consultarTransaccion($pago->wompi_transaction_id);
            } else {
                $tx = $wompiService->consultarTransaccionPorReferencia($pago->reference);
            }

            if ($tx && isset($tx['status']) && strtoupper($tx['status']) !== 'PENDING') {
                $res = $procesarAction->execute($tx);
                $this->info("Pago {$pago->reference} actualizado a {$tx['status']} (Resultado: {$res['result']})");
                $reconciliados++;
            } elseif ($pago->isExpired()) {
                $pago->update([
                    'status' => 'EXPIRED',
                    'status_message' => 'Intención de pago expirada sin transacción registrada.',
                ]);
                $this->warn("Pago {$pago->reference} marcado como EXPIRED.");
                $reconciliados++;
            }
        }

        $this->info("Reconciliación completada: {$reconciliados} pagos procesados.");

        return self::SUCCESS;
    }
}
