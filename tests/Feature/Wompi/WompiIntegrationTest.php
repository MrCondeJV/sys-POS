<?php

namespace Tests\Feature\Wompi;

use App\Actions\Wompi\ProcesarResultadoTransaccionWompiAction;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSuscripcion;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\PagoSuscripcion;
use App\Models\Plan;
use App\Models\Sucursal;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\Wompi\WompiService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WompiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    private Plan $plan;

    private User $admin;

    private WompiService $wompiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        Config::set('services.wompi.integrity_secret', 'test_integrity_secret_123');
        Config::set('services.wompi.events_secret', 'test_events_secret_456');
        Config::set('services.wompi.public_key', 'pub_test_dummy_key');
        Config::set('services.wompi.private_key', 'prv_test_dummy_key');
        Config::set('services.wompi.base_url', 'https://sandbox.wompi.co/v1');

        $this->empresa = Empresa::create([
            'nombre_comercial' => 'Comercio Wompi S.A.S.',
            'razon_social' => 'Comercio Wompi S.A.S.',
            'nit' => '901777888-2',
            'email' => 'admin@comerciowompi.co',
            'moneda' => 'COP',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Principal',
            'codigo' => 'SUC-01',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Wompi',
            'email' => 'admin@comerciowompi.co',
            'password' => 'password123',
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($this->empresa->id);
        $this->admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->plan = Plan::create([
            'nombre' => 'Plan Pyme Wompi',
            'slug' => 'pyme-wompi',
            'descripcion' => 'Plan completo para pymes',
            'precio_mensual' => 49900,
            'precio_anual' => 499000,
            'moneda' => 'COP',
            'limite_sucursales' => 2,
            'limite_usuarios' => 5,
            'permite_facturacion_electronica' => true,
            'permite_api' => true,
            'activo' => true,
        ]);

        $this->wompiService = app(WompiService::class);
    }

    /**
     * W-01: Genera referencia única y firma SHA-256 válida para Wompi checkout.
     */
    public function test_genera_referencia_unica_y_firma_integridad_sha256(): void
    {
        $pago = $this->wompiService->crearIntencionPago(
            $this->empresa,
            $this->plan,
            'MENSUAL',
            $this->admin
        );

        $this->assertDatabaseHas('pagos_suscripcion', [
            'id' => $pago->id,
            'empresa_id' => $this->empresa->id,
            'plan_id' => $this->plan->id,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'PENDING',
        ]);

        $expectedSignature = hash('sha256', "{$pago->reference}4990000COPtest_integrity_secret_123");
        $this->assertEquals($expectedSignature, $pago->integrity_signature);
    }

    /**
     * Helper para armar un payload simulado de webhook de Wompi con firma criptográfica correcta.
     */
    private function buildWebhookPayload(array $transactionData, ?string $overrideSecret = null): array
    {
        $timestamp = time();
        $secret = $overrideSecret ?? config('services.wompi.events_secret');

        $properties = ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'];
        $raw = $transactionData['id'].$transactionData['status'].$transactionData['amount_in_cents'].$timestamp.$secret;
        $checksum = hash('sha256', $raw);

        return [
            'event' => 'transaction.updated',
            'data' => [
                'transaction' => $transactionData,
            ],
            'timestamp' => $timestamp,
            'signature' => [
                'properties' => $properties,
                'checksum' => $checksum,
            ],
        ];
    }

    /**
     * W-02: Webhook APPROVED activa suscripción y extiende vigencia.
     */
    public function test_webhook_aprobado_activa_suscripcion_y_extiende_vigencia(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        $txData = [
            'id' => 'wompi-tx-001',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'APPROVED',
            'payment_method_type' => 'CARD',
        ];

        $payload = $this->buildWebhookPayload($txData);

        $response = $this->postJson('/webhooks/wompi', $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'ok', 'result' => 'PROCESSED']);

        $pago->refresh();
        $this->assertEquals('APPROVED', $pago->status);
        $this->assertEquals('wompi-tx-001', $pago->wompi_transaction_id);

        $suscripcion = $this->empresa->fresh()->suscripcionActual;
        $this->assertNotNull($suscripcion);
        $this->assertEquals(EstadoSuscripcion::ACTIVA, $suscripcion->estado);
        $this->assertTrue($suscripcion->isVigente());
    }

    /**
     * W-03: Webhook DECLINED no activa suscripción.
     */
    public function test_webhook_declinado_no_activa_suscripcion(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        $txData = [
            'id' => 'wompi-tx-002',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'DECLINED',
            'status_message' => 'Fondos insuficientes',
        ];

        $payload = $this->buildWebhookPayload($txData);

        $response = $this->postJson('/webhooks/wompi', $payload);

        $response->assertOk();
        $this->assertEquals('DECLINED', $pago->fresh()->status);
        $this->assertNull($this->empresa->fresh()->suscripcionActual);
    }

    /**
     * W-04: Webhook con firma inválida es rechazado con HTTP 401.
     */
    public function test_webhook_con_firma_invalida_es_rechazado_401(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        $txData = [
            'id' => 'wompi-tx-003',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'APPROVED',
        ];

        // Firma generada con llave incorrecta
        $payload = $this->buildWebhookPayload($txData, 'llave_falsa_hacker');

        $response = $this->postJson('/webhooks/wompi', $payload);

        $response->assertStatus(401);
        $this->assertEquals('PENDING', $pago->fresh()->status);
    }

    /**
     * W-05: Webhook duplicado es idempotente (DUPLICATE_IGNORED).
     */
    public function test_webhook_duplicado_es_idempotente(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        $txData = [
            'id' => 'wompi-tx-004',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'APPROVED',
        ];

        $payload = $this->buildWebhookPayload($txData);

        // Primer envío
        $this->postJson('/webhooks/wompi', $payload)->assertOk();
        $fechaFinInicial = $this->empresa->fresh()->suscripcionActual->fecha_fin->toIso8601String();

        // Segundo y tercer envío (duplicados de red)
        $resp2 = $this->postJson('/webhooks/wompi', $payload);
        $resp2->assertOk();
        $resp2->assertJson(['status' => 'duplicate_ignored']);

        // La fecha de vigencia no se extiende múltiples veces
        $this->assertEquals(
            $fechaFinInicial,
            $this->empresa->fresh()->suscripcionActual->fecha_fin->toIso8601String()
        );
    }

    /**
     * W-06: Webhook con monto alterado (AMOUNT_MISMATCH) rechaza y no activa suscripción.
     */
    public function test_webhook_con_monto_alterado_es_rechazado_y_auditado(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        // Envío de monto inferior (ej. pagó $10.000 en vez de $49.900)
        $txData = [
            'id' => 'wompi-tx-005',
            'reference' => $pago->reference,
            'amount_in_cents' => 1000000,
            'currency' => 'COP',
            'status' => 'APPROVED',
        ];

        $payload = $this->buildWebhookPayload($txData);

        $response = $this->postJson('/webhooks/wompi', $payload);

        $response->assertOk();
        $response->assertJson(['result' => 'AMOUNT_MISMATCH']);

        $pago->refresh();
        $this->assertEquals('AMOUNT_MISMATCH', $pago->status);
        $this->assertNull($this->empresa->fresh()->suscripcionActual);
    }

    /**
     * W-07: Webhook fuera de orden no degrada pago aprobado.
     */
    public function test_webhook_fuera_de_orden_no_degrada_pago_aprobado(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        // 1. Llega APPROVED
        $txApproved = [
            'id' => 'wompi-tx-006',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'APPROVED',
        ];
        $this->postJson('/webhooks/wompi', $this->buildWebhookPayload($txApproved))->assertOk();
        $this->assertEquals('APPROVED', $pago->fresh()->status);

        // 2. Llega evento tardío PENDING
        $txPending = [
            'id' => 'wompi-tx-006',
            'reference' => $pago->reference,
            'amount_in_cents' => 4990000,
            'currency' => 'COP',
            'status' => 'PENDING',
        ];
        $this->postJson('/webhooks/wompi', $this->buildWebhookPayload($txPending))->assertOk();

        // El pago permanece APPROVED
        $this->assertEquals('APPROVED', $pago->fresh()->status);
        $this->assertEquals(EstadoSuscripcion::ACTIVA, $this->empresa->fresh()->suscripcionActual->estado);
    }

    /**
     * W-08: Comando de reconciliación consulta Wompi con Http::fake() y activa pago pendiente.
     */
    public function test_comando_reconciliacion_consulta_wompi_con_http_fake(): void
    {
        $pago = $this->wompiService->crearIntencionPago($this->empresa, $this->plan, 'MENSUAL', $this->admin);

        // Simular que el pago fue creado hace 20 minutos (apto para reconciliación)
        $pago->forceFill(['created_at' => now()->subMinutes(20)])->saveQuietly();

        Http::fake([
            'https://sandbox.wompi.co/v1/transactions*' => Http::response([
                'data' => [
                    [
                        'id' => 'wompi-tx-reconciled-01',
                        'reference' => $pago->reference,
                        'amount_in_cents' => 4990000,
                        'currency' => 'COP',
                        'status' => 'APPROVED',
                        'payment_method_type' => 'PSE',
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('wompi:reconciliar-pagos')->assertSuccessful();

        $this->assertEquals('APPROVED', $pago->fresh()->status);
        $this->assertEquals('wompi-tx-reconciled-01', $pago->fresh()->wompi_transaction_id);
        $this->assertEquals(EstadoSuscripcion::ACTIVA, $this->empresa->fresh()->suscripcionActual->estado);
    }

    /**
     * W-09: Middleware EnsureSubscriptionIsActive bloquea mutaciones operativas pero permite lecturas GET.
     */
    public function test_middleware_ensure_subscription_is_active_bloquea_escritura_pero_permite_lectura(): void
    {
        // Suscripción vencida
        Suscripcion::create([
            'empresa_id' => $this->empresa->id,
            'plan_id' => $this->plan->id,
            'estado' => EstadoSuscripcion::VENCIDA,
            'fecha_inicio' => now()->subDays(60),
            'fecha_fin' => now()->subDays(10),
            'ciclo_facturacion' => 'MENSUAL',
            'precio_pago' => 49900,
        ]);

        // 1. Petición GET al dashboard y a suscripción: PERMITIDA (200)
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('suscripcion.index'))->assertOk();

        // 2. Petición mutadora POST (intentar registrar sucursal): BLOQUEADA
        $postResponse = $this->actingAs($this->admin)->post(route('sucursales.store'), [
            'nombre' => 'Sucursal No Permitida',
            'codigo' => 'SUC-02',
        ]);
        $postResponse->assertRedirect(route('suscripcion.index'));

        // 3. Petición JSON mutadora: HTTP 402 Payment Required
        $jsonResponse = $this->actingAs($this->admin)->postJson(route('pos.procesar'), [
            'items' => [],
        ]);
        $jsonResponse->assertStatus(402);
        $jsonResponse->assertJson(['code' => 'SUBSCRIPTION_EXPIRED']);
    }

    /**
     * W-10: Vistas de checkout y retorno renderizan exitosamente.
     */
    public function test_vistas_de_suscripcion_y_checkout_renderizan_exitosamente(): void
    {
        $this->actingAs($this->admin)
            ->get(route('suscripcion.index'))
            ->assertOk()
            ->assertSee('Suscripción y Facturación')
            ->assertSee('Plan Pyme Wompi');

        $this->actingAs($this->admin)
            ->get(route('suscripcion.checkout', ['plan' => $this->plan, 'ciclo' => 'MENSUAL']))
            ->assertOk()
            ->assertSee('Confirmar y Pagar Suscripción')
            ->assertSee('widget.js');

        $this->actingAs($this->admin)
            ->get(route('suscripcion.retorno', ['id' => 'tx-dummy-123']))
            ->assertOk()
            ->assertSee('Transacción en Verificación');
    }
}
