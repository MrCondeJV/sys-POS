<?php

namespace Tests\Feature\Fase8;

use App\Actions\Cartera\AnularAbonoCarteraAction;
use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoPagoCartera;
use App\Enums\MetodoPagoCartera;
use App\Enums\RolSistema;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarteraTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected Sucursal $sucursalA;

    protected Sucursal $sucursalB;

    protected User $adminA;

    protected User $adminB;

    protected User $cajeroA;

    protected Cliente $clienteA;

    protected Cliente $clienteB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Empresa Alfa S.A.S.']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Empresa Beta S.A.S.']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalA = Sucursal::factory()->create(['empresa_id' => $this->empresaA->id, 'es_principal' => true]);
        $this->sucursalB = Sucursal::factory()->create(['empresa_id' => $this->empresaB->id, 'es_principal' => true]);

        // Admin Empresa A
        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Cajero Empresa A
        $this->cajeroA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        // Admin Empresa B
        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Clientes
        $this->clienteA = Cliente::factory()->for($this->empresaA)->conCredito(5000000, 30)->create([
            'razon_social' => 'Cliente Alfa Crédito',
        ]);

        $this->clienteB = Cliente::factory()->for($this->empresaB)->conCredito(3000000, 15)->create([
            'razon_social' => 'Cliente Beta Crédito',
        ]);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('cartera.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_dashboard_de_cartera_con_kpis(): void
    {
        $action = app(RegistrarCuentaPorCobrarAction::class);
        $cuenta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 500000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura Alfa #001',
            userId: $this->adminA->id
        );

        $response = $this->actingAs($this->adminA)->get(route('cartera.index'));

        $response->assertOk();
        $response->assertSee('Crédito y Cartera');
        $response->assertSee($cuenta->numero_documento);
        $response->assertSee('Cliente Alfa Crédito');
        $response->assertSee('500,000.00');
    }

    public function test_aislamiento_multitenant_estricto_en_cartera(): void
    {
        $action = app(RegistrarCuentaPorCobrarAction::class);

        $cuentaA = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 700000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(15)->toDateString(),
            concepto: 'Obligación Exclusiva Alfa',
            userId: $this->adminA->id
        );

        $cuentaB = $action->execute(
            empresaId: $this->empresaB->id,
            sucursalId: $this->sucursalB->id,
            clienteId: $this->clienteB->id,
            montoTotal: 900000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(15)->toDateString(),
            concepto: 'Obligación Confidencial Beta',
            userId: $this->adminB->id
        );

        // Empresa A
        $responseA = $this->actingAs($this->adminA)->get(route('cartera.index'));
        $responseA->assertOk();
        $responseA->assertSee('Obligación Exclusiva Alfa');
        $responseA->assertDontSee('Obligación Confidencial Beta');

        // Empresa B
        $responseB = $this->actingAs($this->adminB)->get(route('cartera.index'));
        $responseB->assertOk();
        $responseB->assertSee('Obligación Confidencial Beta');
        $responseB->assertDontSee('Obligación Exclusiva Alfa');
    }

    public function test_admin_puede_aperturar_cuenta_por_cobrar_con_consecutivo(): void
    {
        $datos = [
            'cliente_id' => $this->clienteA->id,
            'concepto' => 'Apertura de Crédito Comercial Materiales',
            'monto_total' => 1200000.00,
            'fecha_emision' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'observaciones' => 'Autorizado por gerencia',
        ];

        $response = $this->actingAs($this->adminA)->post(route('cartera.store'), $datos);

        $response->assertRedirect();
        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'empresa_id' => $this->empresaA->id,
            'cliente_id' => $this->clienteA->id,
            'numero_documento' => 'CXC-00001',
            'monto_total' => 1200000.00,
            'monto_pagado' => 0.00,
            'saldo_pendiente' => 1200000.00,
            'estado' => EstadoCuentaCobrar::PENDIENTE->value,
        ]);
    }

    public function test_validacion_impide_crear_cuenta_con_monto_invalido(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('cartera.store'), [
            'cliente_id' => $this->clienteA->id,
            'concepto' => 'Prueba error',
            'monto_total' => -500,
            'fecha_emision' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertSessionHasErrors('monto_total');
    }

    public function test_admin_o_cajero_puede_aplicar_abono_parcial_y_actualiza_saldo_y_estado(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $cuenta = $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 1000000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura Alfa #10'
        );

        $response = $this->actingAs($this->cajeroA)->post(route('cartera.abono.store', $cuenta), [
            'monto' => 400000.00,
            'metodo_pago' => MetodoPagoCartera::EFECTIVO->value,
            'fecha_pago' => now()->toDateString(),
            'referencia_pago' => null,
            'notas' => 'Abono en efectivo en caja',
        ]);

        $response->assertRedirect(route('cartera.show', $cuenta));
        $response->assertSessionHas('success');
        $response->assertSessionHas('recibo_id');

        $cuenta->refresh();
        $this->assertEquals(400000.00, (float) $cuenta->monto_pagado);
        $this->assertEquals(600000.00, (float) $cuenta->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PARCIAL, $cuenta->estado);

        $this->assertDatabaseHas('pagos_clientes', [
            'empresa_id' => $this->empresaA->id,
            'cuenta_por_cobrar_id' => $cuenta->id,
            'cliente_id' => $this->clienteA->id,
            'numero_recibo' => 'RC-00001',
            'monto' => 400000.00,
            'saldo_anterior' => 1000000.00,
            'saldo_posterior' => 600000.00,
            'estado' => EstadoPagoCartera::APLICADO->value,
        ]);
    }

    public function test_abono_total_marca_cuenta_como_pagada(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $cuenta = $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 500000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura Alfa Total'
        );

        $response = $this->actingAs($this->adminA)->post(route('cartera.abono.store', $cuenta), [
            'monto' => 500000.00,
            'metodo_pago' => MetodoPagoCartera::TRANSFERENCIA->value,
            'fecha_pago' => now()->toDateString(),
            'referencia_pago' => 'TR-998877',
        ]);

        $response->assertRedirect();
        $cuenta->refresh();

        $this->assertEquals(500000.00, (float) $cuenta->monto_pagado);
        $this->assertEquals(0.00, (float) $cuenta->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PAGADA, $cuenta->estado);
    }

    public function test_no_se_puede_abonar_mas_del_saldo_pendiente(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $cuenta = $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 300000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura Alfa Límite'
        );

        $response = $this->actingAs($this->adminA)->post(route('cartera.abono.store', $cuenta), [
            'monto' => 350000.00, // Excede los 300.000
            'metodo_pago' => MetodoPagoCartera::EFECTIVO->value,
            'fecha_pago' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('monto');
        $cuenta->refresh();
        $this->assertEquals(300000.00, (float) $cuenta->saldo_pendiente);
    }

    public function test_anulacion_de_abono_revierte_saldo_correctamente(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $actionAbonar = app(RegistrarAbonoCarteraAction::class);
        $actionAnular = app(AnularAbonoCarteraAction::class);

        $cuenta = $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 600000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura para Anular Abono'
        );

        $pago = $actionAbonar->execute(
            cuenta: $cuenta,
            monto: 200000.00,
            metodoPago: MetodoPagoCartera::EFECTIVO,
            fechaPago: now()->toDateString()
        );

        $cuenta->refresh();
        $this->assertEquals(400000.00, (float) $cuenta->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PARCIAL, $cuenta->estado);

        // Anular a través del endpoint HTTP
        $response = $this->actingAs($this->adminA)->post(route('cartera.recibo.anular', $pago));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cuenta->refresh();
        $pago->refresh();

        $this->assertEquals(600000.00, (float) $cuenta->saldo_pendiente);
        $this->assertEquals(0.00, (float) $cuenta->monto_pagado);
        $this->assertEquals(EstadoCuentaCobrar::PENDIENTE, $cuenta->estado);
        $this->assertEquals(EstadoPagoCartera::ANULADO, $pago->estado);
    }

    public function test_filtro_de_cartera_vencida_y_vigente(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);

        // Cuenta Vencida
        $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 150000.00,
            fechaEmision: now()->subDays(40)->toDateString(),
            fechaVencimiento: now()->subDays(10)->toDateString(),
            concepto: 'Cuenta Vencida Alfa'
        );

        // Cuenta Vigente
        $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 250000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(20)->toDateString(),
            concepto: 'Cuenta Vigente Alfa'
        );

        $resVencida = $this->actingAs($this->adminA)->get(route('cartera.index', ['estado' => 'VENCIDA']));
        $resVencida->assertSee('Cuenta Vencida Alfa');
        $resVencida->assertDontSee('Cuenta Vigente Alfa');

        $resVigente = $this->actingAs($this->adminA)->get(route('cartera.index', ['estado' => 'VIGENTE']));
        $resVigente->assertSee('Cuenta Vigente Alfa');
        $resVigente->assertDontSee('Cuenta Vencida Alfa');
    }

    public function test_vista_estado_de_cuenta_del_cliente_consolida_saldos_y_cupo(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 800000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Materiales Especiales'
        );

        $response = $this->actingAs($this->adminA)->get(route('cartera.estado-cuenta', $this->clienteA));

        $response->assertOk();
        $response->assertSee('Estado de Cuenta: Cliente Alfa Crédito');
        $response->assertSee('5,000,000.00'); // Cupo de crédito
        $response->assertSee('800,000.00');   // Deuda
        $response->assertSee('4,200,000.00'); // Cupo disponible
    }

    public function test_vista_recibo_de_caja_se_renderiza_correctamente(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $actionAbonar = app(RegistrarAbonoCarteraAction::class);

        $cuenta = $actionCrear->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 300000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(15)->toDateString(),
            concepto: 'Factura para Recibo'
        );

        $pago = $actionAbonar->execute(
            cuenta: $cuenta,
            monto: 150000.00,
            metodoPago: MetodoPagoCartera::EFECTIVO,
            fechaPago: now()->toDateString()
        );

        $response = $this->actingAs($this->adminA)->get(route('cartera.recibo', $pago));

        $response->assertOk();
        $response->assertSee($pago->numero_recibo);
        $response->assertSee('150,000.00');
        $response->assertSee('Cliente Alfa Crédito');
    }

    public function test_usuario_empresa_a_no_puede_abonar_a_cuenta_de_empresa_b(): void
    {
        $actionCrear = app(RegistrarCuentaPorCobrarAction::class);
        $cuentaB = $actionCrear->execute(
            empresaId: $this->empresaB->id,
            sucursalId: $this->sucursalB->id,
            clienteId: $this->clienteB->id,
            montoTotal: 500000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(20)->toDateString(),
            concepto: 'Cuenta de Empresa B'
        );

        $response = $this->actingAs($this->adminA)->post(route('cartera.abono.store', $cuentaB), [
            'monto' => 100000.00,
            'metodo_pago' => MetodoPagoCartera::EFECTIVO->value,
            'fecha_pago' => now()->toDateString(),
        ]);

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }
}
