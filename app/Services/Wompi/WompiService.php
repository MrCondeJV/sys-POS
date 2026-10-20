<?php

namespace App\Services\Wompi;

use App\Models\Empresa;
use App\Models\PagoSuscripcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WompiService
{
    /**
     * Genera una referencia única de pago para el comercio.
     */
    public function generarReferencia(Empresa $empresa): string
    {
        return 'POS-'.$empresa->id.'-'.Str::ulid();
    }

    /**
     * Genera la firma de integridad SHA-256 exigida por Wompi para iniciar el checkout.
     * Fórmula: hash("sha256", reference + amountInCents + currency + [expirationTime] + integritySecret)
     */
    public function generarFirmaIntegridad(
        string $reference,
        int $amountInCents,
        string $currency = 'COP',
        ?string $expirationTime = null
    ): string {
        $secret = config('services.wompi.integrity_secret', '');
        $raw = $reference.$amountInCents.$currency.($expirationTime ?? '').$secret;

        return hash('sha256', $raw);
    }

    /**
     * Valida criptográficamente la firma del webhook con events_secret usando hash_equals.
     */
    public function verificarFirmaWebhook(array $payload): bool
    {
        $signature = $payload['signature'] ?? null;
        if (! is_array($signature) || empty($signature['properties']) || empty($signature['checksum'])) {
            return false;
        }

        $eventsSecret = config('services.wompi.events_secret', '');
        $timestamp = (string) ($payload['timestamp'] ?? '');

        $concatenatedProps = collect($signature['properties'])
            ->map(fn (string $prop) => (string) data_get($payload['data'] ?? [], $prop, ''))
            ->implode('');

        $rawString = $concatenatedProps.$timestamp.$eventsSecret;
        $expectedChecksum = hash('sha256', $rawString);

        return hash_equals(
            strtolower($expectedChecksum),
            strtolower((string) $signature['checksum'])
        );
    }

    /**
     * Crea una intención local de pago en estado PENDING con firma de integridad.
     */
    public function crearIntencionPago(
        Empresa $empresa,
        Plan $plan,
        string $ciclo = 'MENSUAL',
        ?User $user = null
    ): PagoSuscripcion {
        $precio = strtoupper($ciclo) === 'ANUAL'
            ? (float) $plan->precio_anual
            : (float) $plan->precio_mensual;

        $amountInCents = (int) round($precio * 100);
        $reference = $this->generarReferencia($empresa);
        $currency = 'COP';
        $signature = $this->generarFirmaIntegridad($reference, $amountInCents, $currency);

        return PagoSuscripcion::create([
            'empresa_id' => $empresa->id,
            'suscripcion_id' => $empresa->suscripcionActual?->id,
            'plan_id' => $plan->id,
            'user_id' => $user?->id,
            'ciclo_facturacion' => strtoupper($ciclo),
            'reference' => $reference,
            'amount_in_cents' => $amountInCents,
            'currency' => $currency,
            'integrity_signature' => $signature,
            'status' => 'PENDING',
            'expires_at' => now()->addHours(2),
        ]);
    }

    /**
     * Consulta una transacción directamente en el API de Wompi por su ID.
     */
    public function consultarTransaccion(string $wompiTransactionId): ?array
    {
        $baseUrl = rtrim(config('services.wompi.base_url', 'https://sandbox.wompi.co/v1'), '/');
        $privateKey = config('services.wompi.private_key', '');

        $response = Http::withToken($privateKey)
            ->timeout(10)
            ->get("{$baseUrl}/transactions/{$wompiTransactionId}");

        if ($response->successful()) {
            return $response->json('data');
        }

        return null;
    }

    /**
     * Consulta transacciones en el API de Wompi por referencia única.
     */
    public function consultarTransaccionPorReferencia(string $reference): ?array
    {
        $baseUrl = rtrim(config('services.wompi.base_url', 'https://sandbox.wompi.co/v1'), '/');
        $privateKey = config('services.wompi.private_key', '');

        $response = Http::withToken($privateKey)
            ->timeout(10)
            ->get("{$baseUrl}/transactions", [
                'reference' => $reference,
            ]);

        if ($response->successful()) {
            $data = $response->json('data');
            if (is_array($data) && count($data) > 0) {
                return $data[0];
            }
        }

        return null;
    }
}
