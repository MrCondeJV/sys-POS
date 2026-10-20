<?php

namespace App\Http\Controllers;

use App\Actions\Wompi\ProcesarResultadoTransaccionWompiAction;
use App\Models\WompiWebhookEvent;
use App\Services\Wompi\WompiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WompiWebhookController extends Controller
{
    public function __construct(
        protected WompiService $wompiService,
        protected ProcesarResultadoTransaccionWompiAction $procesarAction
    ) {}

    /**
     * Endpoint receptor del Webhook de Wompi (/webhooks/wompi).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $transaction = data_get($payload, 'data.transaction', []);
        $wompiTxId = (string) ($transaction['id'] ?? 'unknown');
        $reference = (string) ($transaction['reference'] ?? null);
        $txStatus = (string) ($transaction['status'] ?? 'UNKNOWN');
        $eventType = (string) ($payload['event'] ?? 'transaction.updated');

        $eventId = hash('sha256', $eventType.':'.$wompiTxId.':'.($payload['timestamp'] ?? time()));

        // 1. Verificación criptográfica obligatoria antes de procesar
        $firmaValida = $this->wompiService->verificarFirmaWebhook($payload);

        if (! $firmaValida) {
            WompiWebhookEvent::create([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'wompi_transaction_id' => $wompiTxId,
                'reference' => $reference,
                'transaction_status' => $txStatus,
                'signature_valid' => false,
                'processing_result' => 'INVALID_SIGNATURE',
                'ip_address' => $request->ip(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => 'Firma criptográfica inválida.'], 401);
        }

        // 2. Control de idempotencia por evento
        $eventoPrevio = WompiWebhookEvent::where('event_id', $eventId)
            ->orWhere(function ($query) use ($wompiTxId, $txStatus) {
                $query->where('wompi_transaction_id', $wompiTxId)
                    ->where('transaction_status', $txStatus)
                    ->where('processing_result', 'PROCESSED');
            })
            ->first();

        if ($eventoPrevio) {
            return response()->json([
                'status' => 'duplicate_ignored',
                'message' => 'Evento ya procesado previamente.',
            ], 200);
        }

        // 3. Procesamiento transaccional de negocio
        $resultado = $this->procesarAction->execute($transaction);

        WompiWebhookEvent::create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'wompi_transaction_id' => $wompiTxId,
            'reference' => $reference,
            'transaction_status' => $txStatus,
            'signature_valid' => true,
            'processed_at' => now(),
            'processing_result' => $resultado['result'],
            'ip_address' => $request->ip(),
            'payload' => $payload,
        ]);

        return response()->json([
            'status' => 'ok',
            'result' => $resultado['result'],
        ], 200);
    }
}
