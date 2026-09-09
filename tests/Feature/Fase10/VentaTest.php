<?php

namespace Tests\Feature\Fase10;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Ventas\AnularVentaAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Exceptions\StockInsuficienteException;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaTest extends TestCase
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
    protected Producto $productoB;
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
        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Cliente con crédito en Empresa A
        $this->clienteA = Cliente::factory()->for($this->empresaA)->conCredito(1000000.00, 30)->create([
            'razon_social' => 'Cliente Mayorista Alfa',
        ]);

        // Producto e Inventario en Empresa A (Stock: 50 unidades)
        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Cemento Gris 50kg',
            'codigo' => 'CEM-050',
            'precio_compra' => 22000.00,
            'precio_venta' => 30000.00,
            'stock' => 50,
            'stock_minimo' => 5,
            'estado' => \App\Enums\EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);

        // Caja para Empresa A
        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja POS 1',
            'codigo' => 'POS-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        CompanyContext::clear();
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('ventas.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_o_cajero_puede_ver_listado_de_ventas_con_kpis(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('ventas.index'));

        $response->assertOk();
        $response->assertViewIs('ventas.index');
        $response->assertSee('Ventas de Hoy');
    }

    public function test_registrar_venta_al_contado_valida_stock_y_descuenta_inventario(): void
    {
        $action = app(RegistrarVentaAction::class);

        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 5, // 5 bolsas x 30.000 = 150.000
                    'precio_unitario' => 30000.00,
                    'descuento' => 0,
                    'impuesto_porcentaje' => 0,
                ],
            ],
            clienteId: null, // Consumidor final
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO'
        );

        $this->assertEquals(EstadoVenta::COMPLETADA, $venta->estado);
        $this->assertEquals(150000.00, $venta->total);
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'numero_venta' => $venta->numero_venta,
            'total' => 150000.00,
        ]);

        // Stock original: 50 - 5 = 45
        $inv = Inventario::where('sucursal_id', $this->sucursalA->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(45, $inv->stock);

        // Movimiento de inventario generado
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->productoA->id,
            'tipo' => TipoMovimientoInventario::SALIDA_VENTA->value,
            'cantidad' => 5,
        ]);
    }

    public function test_registrar_venta_al_contado_impacta_caja_abierta_automaticamente(): void
    {
        // Abrir caja con 50.000
        $sesionCaja = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $action = app(RegistrarVentaAction::class);

        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 2, // 2 x 30.000 = 60.000
                    'precio_unitario' => 30000.00,
                ],
            ],
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $sesionCaja->id
        );

        // Caja: 50.000 inicial + 60.000 venta = 110.000
        $this->assertEquals(110000.00, $sesionCaja->fresh()->calcularSaldoEsperadoEfectivo());

        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $sesionCaja->id,
            'monto' => 60000.00,
            'concepto' => "Venta {$venta->numero_venta}",
        ]);
    }

    public function test_no_se_puede_vender_mas_del_stock_disponible(): void
    {
        $this->expectException(StockInsuficienteException::class);

        $action = app(RegistrarVentaAction::class);

        // Intentar vender 60 unidades cuando el stock es 50
        $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 60,
                    'precio_unitario' => 30000.00,
                ],
            ]
        );
    }

    public function test_registrar_venta_a_credito_valida_cupo_y_genera_cuenta_por_cobrar(): void
    {
        $action = app(RegistrarVentaAction::class);

        // Venta de 20 bolsas x 30.000 = 600.000 (Cupo cliente: 1.000.000)
        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 20,
                    'precio_unitario' => 30000.00,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CREDITO,
            metodoPago: 'CREDITO'
        );

        $this->assertEquals(TipoPago::CREDITO, $venta->tipo_pago);
        $this->assertEquals(600000.00, $venta->total);

        // Cuenta por cobrar generada en cartera
        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'empresa_id' => $this->empresaA->id,
            'cliente_id' => $this->clienteA->id,
            'monto_total' => 600000.00,
            'saldo_pendiente' => 600000.00,
            'estado' => EstadoCuentaCobrar::PENDIENTE->value,
        ]);

        // Cupo restante del cliente: 1.000.000 - 600.000 = 400.000
        $this->assertEquals(400000.00, $this->clienteA->fresh()->cupoDisponible());
    }

    public function test_venta_a_credito_falla_si_supera_cupo_disponible(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $action = app(RegistrarVentaAction::class);

        // 40 bolsas x 30.000 = 1.200.000 (Supera el cupo de 1.000.000)
        $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 40,
                    'precio_unitario' => 30000.00,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CREDITO,
            metodoPago: 'CREDITO'
        );
    }

    public function test_anulacion_de_venta_reingresa_existencias_al_inventario(): void
    {
        $actionRegistrar = app(RegistrarVentaAction::class);
        $actionAnular = app(AnularVentaAction::class);

        // Venta de 10 unidades -> Stock baja de 50 a 40
        $venta = $actionRegistrar->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 10,
                    'precio_unitario' => 30000.00,
                ],
            ]
        );

        $this->assertEquals(40, Inventario::where('producto_id', $this->productoA->id)->first()->stock);

        // Anulación
        $ventaAnulada = $actionAnular->execute($venta, $this->adminA, 'Cliente canceló el pedido en mostrador');

        $this->assertEquals(EstadoVenta::ANULADA, $ventaAnulada->estado);
        $this->assertEquals('Cliente canceló el pedido en mostrador', $ventaAnulada->motivo_anulacion);

        // Stock recuperado: 40 + 10 = 50
        $this->assertEquals(50, Inventario::where('producto_id', $this->productoA->id)->first()->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $this->productoA->id,
            'tipo' => TipoMovimientoInventario::DEVOLUCION_CLIENTE->value,
            'cantidad' => 10,
        ]);
    }

    public function test_ticket_de_venta_se_renderiza_correctamente(): void
    {
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 30000.00,
                ],
            ]
        );

        $response = $this->actingAs($this->cajeroA)->get(route('ventas.ticket', $venta));

        $response->assertOk();
        $response->assertViewIs('ventas.ticket');
        $response->assertSee($venta->numero_venta);
        $response->assertSee('Cemento Gris 50kg');
        $response->assertSee('¡GRACIAS POR SU COMPRA!');
    }

    public function test_aislamiento_multitenant_estricto_en_ventas(): void
    {
        $ventaA = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 30000.00,
                ],
            ]
        );

        // Admin B no puede ver la venta de Empresa A
        $response = $this->actingAs($this->adminB)->get(route('ventas.show', $ventaA));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }
}
