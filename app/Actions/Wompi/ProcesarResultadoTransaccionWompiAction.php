<?php

namespace App\Actions\Wompi;

use App\Enums\EstadoSuscripcion;
use App\Models\PagoSuscripcion;
use App\Models\Suscripcion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcesarResultadoTransaccionWompiAction
{
    /**
     * Procesa atómica e idempotentemente el resultado de una transacción de Wompi.
     */
    public function execute(array $transactionData): array
    {
        $reference = (string) ($transactionData['reference'] ?? '');
        $status = strtoupper((string) ($transactionData['status'] ?? ''));
        $transactionId = (string) ($transactionData['id'] ?? '');

        return DB::transaction(function () use ($reference, $status, $transactionId, $transactionData) {
            $pago = PagoSuscripcion::where('reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $pago) {
                return [
                    'result' => 'NOT_FOUND',
                    'message' => "No se encontró el registro local para la referencia {$reference}",
                ];
            }

            // 1. Idempotencia: Si ya está APPROVED, no se degrada por eventos desordenados
            if ($pago->isApproved()) {
                return [
                    'result' => 'DUPLICATE_IGNORED',
                    'message' => 'La transacción ya se encontraba aprobada previamente.',
                ];
            }

            // 2. Control de Fraude: Monto y Moneda
            $incomingAmount = (int) ($transactionData['amount_in_cents'] ?? 0);
            $incomingCurrency = strtoupper((string) ($transactionData['currency'] ?? ''));

            if ($incomingAmount !== (int) $pago->amount_in_cents || $incomingCurrency !== strtoupper($pago->currency)) {
                $pago->update([
                    'status' => 'AMOUNT_MISMATCH',
                    'wompi_transaction_id' => $transactionId ?: $pago->wompi_transaction_id,
                    'status_message' => "Monto recibido ({$incomingAmount} {$incomingCurrency}) difiere del esperado ({$pago->amount_in_cents} {$pago->currency})",
                    'raw_response' => $transactionData,
                ]);

                return [
                    'result' => 'AMOUNT_MISMATCH',
                    'message' => 'Discrepancia detectada en monto o moneda.',
                ];
            }

            // 3. Procesamiento según estado
            if ($status === 'APPROVED') {
                $pago->update([
                    'status' => 'APPROVED',
                    'wompi_transaction_id' => $transactionId,
                    'payment_method_type' => $transactionData['payment_method_type'] ?? null,
                    'status_message' => 'Pago aprobado exitosamente.',
                    'paid_at' => now(),
                    'raw_response' => $transactionData,
                ]);

                // Actualizar o crear suscripción del tenant
                $empresa = $pago->empresa;
                $suscripcion = $empresa->suscripcionActual;

                $baseDate = ($suscripcion && $suscripcion->isVigente() && $suscripcion->fecha_fin)
                    ? Carbon::parse($suscripcion->fecha_fin)
                    : now();

                $nuevaFechaFin = $pago->ciclo_facturacion === 'ANUAL'
                    ? $baseDate->copy()->addYear()
                    : $baseDate->copy()->addMonth();

                if ($suscripcion) {
                    $suscripcion->update([
                        'plan_id' => $pago->plan_id,
                        'estado' => EstadoSuscripcion::ACTIVA,
                        'fecha_fin' => $nuevaFechaFin,
                        'fecha_gracia_fin' => null,
                        'ciclo_facturacion' => $pago->ciclo_facturacion,
                        'precio_pago' => $pago->amount_in_cents / 100,
                        'metodo_pago' => 'WOMPI_'.($transactionData['payment_method_type'] ?? 'ONLINE'),
                        'ultimo_pago_id' => $pago->id,
                    ]);
                } else {
                    Suscripcion::create([
                        'empresa_id' => $empresa->id,
                        'plan_id' => $pago->plan_id,
                        'estado' => EstadoSuscripcion::ACTIVA,
                        'fecha_inicio' => now(),
                        'fecha_fin' => $nuevaFechaFin,
                        'ciclo_facturacion' => $pago->ciclo_facturacion,
                        'precio_pago' => $pago->amount_in_cents / 100,
                        'metodo_pago' => 'WOMPI_'.($transactionData['payment_method_type'] ?? 'ONLINE'),
                        'ultimo_pago_id' => $pago->id,
                    ]);
                }

                return [
                    'result' => 'PROCESSED',
                    'status' => 'APPROVED',
                    'pago_id' => $pago->id,
                ];
            }

            // Estados declinados o fallidos
            $pago->update([
                'status' => $status,
                'wompi_transaction_id' => $transactionId ?: $pago->wompi_transaction_id,
                'status_message' => $transactionData['status_message'] ?? 'Transacción no completada por la entidad financiera.',
                'raw_response' => $transactionData,
            ]);

            return [
                'result' => 'PROCESSED',
                'status' => $status,
                'pago_id' => $pago->id,
            ];
        });
    }
}
