<?php

namespace Tests\Feature\Fase11;

use App\Actions\Caja\AbrirCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoSesionCaja;
use App\Enums\RolSistema;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $cajeroA;
    protected User $adminB;
    protected Cliente $clienteA;
    protected Producto $productoA;
    protected Caja $cajaA;

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

        // Usuarios
        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
        ]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Cliente con crédito en Empresa A
        $this->clienteA = Cliente::factory()->for($this->empresaA)->conCredito(500000.00, 30)->create([
            'razon_social' => 'Ferretería El Tornillo',
            'es_predeterminado' => true,
        ]);

        // Producto con stock en Empresa A
        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Martillo de Uña 16oz',
            'codigo' => 'MAR-016',
            'codigo_barras' => '7701234567890',
            'sku' => 'SKU-MAR-016',
            'precio_compra' => 15000.00,
            'precio_venta' => 25000.00,
            'stock' => 20,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => \App\Enums\EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 20,
            'stock_minimo' => 2,
        ]);

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Terminal 1',
            'codigo' => 'POS-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        CompanyContext::clear();
    }

    public function test_invitado_no_puede_acceder_al_pos(): void
    {
        $response = $this->get(route('pos.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pos_muestra_pantalla_sin_caja_si_no_hay_turno_abierto(): void
    {
        $response = $this->actingAs($this->cajeroA)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pos.sin_caja');
        $response->assertSee('Se requiere un turno de caja abierto');
        $response->assertSee($this->cajaA->nombre);
    }

    public function test_pos_carga_terminal_cuando_hay_caja_abierta(): void
    {
        // Abrir caja
        app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pos.index');
        $response->assertSee('Terminal de Venta');
        $response->assertViewHas('productos');
        $response->assertViewHas('clientes');
        $response->assertSee('7701234567890');
    }

    public function test_pos_procesar_falla_si_faltan_items(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)
            ->postJson(route('pos.procesar'), [
                'caja_sesion_id' => $sesion->id,
                'cliente_id' => $this->clienteA->id,
                'tipo_pago' => 'CONTADO',
                'metodo_pago' => 'EFECTIVO',
                'items' => [],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_pos_procesar_venta_efectivo_con_exito(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)
            ->postJson(route('pos.procesar'), [
                'caja_sesion_id' => $sesion->id,
                'cliente_id' => $this->clienteA->id,
                'tipo_pago' => 'CONTADO',
                'metodo_pago' => 'EFECTIVO',
                'tipo_comprobante' => 'TICKET',
                'pago_con' => 60000.00,
                'items' => [
                    [
                        'producto_id' => $this->productoA->id,
                        'cantidad' => 2,
                        'precio_unitario' => 25000.00,
                        'descuento' => 0,
                        'impuesto_porcentaje' => 19,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'venta_id',
            'numero_venta',
            'total',
            'cambio',
            'ticket_url',
            'show_url',
            'message',
        ]);

        $this->assertDatabaseHas('ventas', [
            'empresa_id' => $this->empresaA->id,
            'cliente_id' => $this->clienteA->id,
            'caja_sesion_id' => $sesion->id,
            'total' => 59500.00, // 50000 + 19% IVA (9500)
            'tipo_pago' => TipoPago::CONTADO->value,
            'metodo_pago' => 'EFECTIVO',
        ]);

        // Stock descontado en inventario (20 - 2 = 18)
        $this->assertDatabaseHas('inventarios', [
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 18,
        ]);
    }

    public function test_pos_procesar_venta_pago_mixto_exitoso(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)
            ->postJson(route('pos.procesar'), [
                'caja_sesion_id' => $sesion->id,
                'cliente_id' => $this->clienteA->id,
                'tipo_pago' => 'CONTADO',
                'metodo_pago' => 'MIXTO',
                'tipo_comprobante' => 'TICKET',
                'pagos' => [
                    [
                        'metodo_pago' => 'EFECTIVO',
                        'monto' => 20000.00,
                    ],
                    [
                        'metodo_pago' => 'TARJETA',
                        'monto' => 39500.00,
                        'referencia' => 'VOUCHER-9876',
                    ],
                ],
                'items' => [
                    [
                        'producto_id' => $this->productoA->id,
                        'cantidad' => 2,
                        'precio_unitario' => 25000.00,
                        'impuesto_porcentaje' => 19,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $ventaId = $response->json('venta_id');
        $this->assertDatabaseCount('venta_pagos', 2);
        $this->assertDatabaseHas('venta_pagos', [
            'venta_id' => $ventaId,
            'metodo_pago' => 'EFECTIVO',
            'monto' => 20000.00,
        ]);
        $this->assertDatabaseHas('venta_pagos', [
            'venta_id' => $ventaId,
            'metodo_pago' => 'TARJETA',
            'monto' => 39500.00,
            'referencia' => 'VOUCHER-9876',
        ]);
    }

    public function test_pos_procesar_venta_a_credito_crea_cuenta_en_cartera(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)
            ->postJson(route('pos.procesar'), [
                'caja_sesion_id' => $sesion->id,
                'cliente_id' => $this->clienteA->id,
                'tipo_pago' => 'CREDITO',
                'metodo_pago' => 'CREDITO',
                'items' => [
                    [
                        'producto_id' => $this->productoA->id,
                        'cantidad' => 1,
                        'precio_unitario' => 25000.00,
                        'impuesto_porcentaje' => 0,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'empresa_id' => $this->empresaA->id,
            'cliente_id' => $this->clienteA->id,
            'monto_total' => 25000.00,
            'saldo_pendiente' => 25000.00,
        ]);
    }

    public function test_aislamiento_multiempresa_en_pos(): void
    {
        $sesionA = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        // Usuario de Empresa B intenta enviar una venta usando la sesión de caja de Empresa A
        $response = $this->actingAs($this->adminB)
            ->postJson(route('pos.procesar'), [
                'caja_sesion_id' => $sesionA->id,
                'tipo_pago' => 'CONTADO',
                'metodo_pago' => 'EFECTIVO',
                'items' => [
                    [
                        'producto_id' => $this->productoA->id,
                        'cantidad' => 1,
                        'precio_unitario' => 25000.00,
                    ],
                ],
            ]);

        // Debe fallar con error de validación de caja o excepción de aislamiento
        $this->assertTrue(in_array($response->getStatusCode(), [422, 403, 404]));
    }
}
