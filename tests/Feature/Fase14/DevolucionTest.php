<?php

namespace Tests\Feature\Fase14;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoDevolucion;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoDevolucion;
use App\Enums\TipoMovimientoCaja;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Devolucion;
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

class DevolucionTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected Cliente $clienteA;
    protected Producto $productoA;
    protected Producto $productoB;
    protected Caja $cajaA;
    protected CajaSesion $sesionA;

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

        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id, 'sucursal_id' => $this->sucursalB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->clienteA = Cliente::factory()->for($this->empresaA)->conCredito(1000000.00, 30)->create([
            'razon_social' => 'Cliente Construcciones Alfa',
        ]);

        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Pintura Blanca Tipo 1',
            'codigo' => 'PIN-001',
            'precio_compra' => 40000.00,
            'precio_venta' => 60000.00,
            'stock' => 20,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 20,
            'stock_minimo' => 2,
        ]);

        $this->productoB = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Brocha 4 Pulgadas',
            'codigo' => 'BRO-004',
            'precio_compra' => 8000.00,
            'precio_venta' => 12000.00,
            'stock' => 50,
            'stock_minimo' => 5,
            'impuesto_porcentaje' => 0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoB->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Central',
            'codigo' => 'CAJA-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        CompanyContext::clear();

        $this->sesionA = app(AbrirCajaAction::class)->execute($this->cajaA, $this->adminA, 100000.00);
    }

    public function test_invitado_no_puede_acceder_a_devoluciones(): void
    {
        $response = $this->get(route('devoluciones.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_listado_de_devoluciones(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('devoluciones.index'));

        $response->assertStatus(200);
        $response->assertViewIs('devoluciones.index');
        $response->assertSee('Devoluciones de Ventas');
    }

    public function test_devolucion_total_actualiza_inventario_caja_y_venta(): void
    {
        // 1. Realizar venta en efectivo de 2 pinturas
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 2,
                    'precio_unitario' => 60000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            pagoCon: 142800.00 // 120000 + 19% IVA (22800) = 142800
        );

        $this->assertEquals(142800.00, (float) $venta->total);
        // Stock actual después de venta: 20 - 2 = 18
        $this->assertDatabaseHas('inventarios', [
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 18,
        ]);

        $detalle = $venta->detalles->first();

        // 2. Registrar devolución TOTAL
        $response = $this->actingAs($this->adminA)->post(route('devoluciones.store', $venta), [
            'tipo_reintegro' => 'EFECTIVO',
            'caja_sesion_id' => $this->sesionA->id,
            'motivo' => 'Cliente compró color equivocado por error',
            'items' => [
                [
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => 2,
                    'reingresa_inventario' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('devoluciones', [
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'tipo_devolucion' => TipoDevolucion::TOTAL->value,
            'tipo_reintegro' => TipoReintegroDevolucion::EFECTIVO->value,
            'total' => 142800.00,
        ]);

        // Stock reingresado a inventario: 18 + 2 = 20
        $this->assertDatabaseHas('inventarios', [
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 20,
        ]);

        // Movimiento de entrada en kardex
        $this->assertDatabaseHas('movimientos_inventario', [
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $this->productoA->id,
            'tipo' => TipoMovimientoInventario::DEVOLUCION_CLIENTE->value,
            'cantidad' => 2,
        ]);

        // Movimiento de egreso en caja
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $this->sesionA->id,
            'tipo' => TipoMovimientoCaja::EGRESO->value,
            'monto' => 142800.00,
        ]);

        // Venta actualizada
        $venta->refresh();
        $this->assertTrue((bool) $venta->tiene_devolucion);
        $this->assertEquals(142800.00, (float) $venta->total_devuelto);
        $this->assertEquals(2.0, (float) $detalle->fresh()->cantidad_devuelta);
    }

    public function test_devolucion_parcial_permite_remanente(): void
    {
        // Venta de 5 brochas a $12.000 = $60.000
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoB->id,
                    'cantidad' => 5,
                    'precio_unitario' => 12000.00,
                    'impuesto_porcentaje' => 0,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            pagoCon: 60000.00
        );

        $detalle = $venta->detalles->first();

        // Devolución parcial de 2 unidades
        $devolucion1 = app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->adminA->id,
            items: [
                [
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => 2,
                    'reingresa_inventario' => true,
                ],
            ],
            tipoReintegro: TipoReintegroDevolucion::SALDO_FAVOR,
            motivo: 'Devuelve 2 brochas sobrantes'
        );

        $this->assertEquals(TipoDevolucion::PARCIAL, $devolucion1->tipo_devolucion);
        $this->assertEquals(24000.00, (float) $devolucion1->total);
        $this->assertEquals(2.0, (float) $detalle->fresh()->cantidad_devuelta);
        $this->assertEquals(3.0, $detalle->fresh()->cantidadPendienteDevolucion());

        // Segunda devolución parcial de 3 unidades restantes (completa el total)
        $devolucion2 = app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->adminA->id,
            items: [
                [
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => 3,
                    'reingresa_inventario' => true,
                ],
            ],
            tipoReintegro: TipoReintegroDevolucion::SALDO_FAVOR,
            motivo: 'Devuelve las 3 restantes'
        );

        $this->assertEquals(TipoDevolucion::TOTAL, $devolucion2->tipo_devolucion);
        $this->assertEquals(36000.00, (float) $devolucion2->total);
        $this->assertEquals(5.0, (float) $detalle->fresh()->cantidad_devuelta);
        $this->assertEquals(0.0, $detalle->fresh()->cantidadPendienteDevolucion());
    }

    public function test_validacion_impide_devolver_mas_de_lo_vendido(): void
    {
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoB->id,
                    'cantidad' => 2,
                    'precio_unitario' => 12000.00,
                    'impuesto_porcentaje' => 0,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            pagoCon: 24000.00
        );

        $detalle = $venta->detalles->first();

        $this->expectException(\InvalidArgumentException::class);

        // Intentar devolver 5 cuando solo se compraron 2
        app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->adminA->id,
            items: [
                [
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => 5,
                ],
            ]
        );
    }

    public function test_devolucion_con_ajuste_cartera_reduce_saldo_en_cuentas_por_cobrar(): void
    {
        // Venta a crédito de 1 pintura ($60.000 + 19% IVA = $71.400)
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 60000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CREDITO,
            metodoPago: 'CREDITO'
        );

        $cuenta = CuentaPorCobrar::where('empresa_id', $this->empresaA->id)
            ->where('concepto', 'like', "%{$venta->numero_venta}%")
            ->first();
        $this->assertNotNull($cuenta);
        $this->assertEquals(71400.00, (float) $cuenta->saldo_pendiente);

        // Devolución total con ajuste de cartera
        $detalle = $venta->detalles->first();
        app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->adminA->id,
            items: [
                [
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => 1,
                    'reingresa_inventario' => true,
                ],
            ],
            tipoReintegro: TipoReintegroDevolucion::AJUSTE_CARTERA,
            motivo: 'Cancelación y devolución de mercancía a crédito'
        );

        $cuenta->refresh();
        $this->assertEquals(0.00, (float) $cuenta->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PAGADA, $cuenta->estado);
    }

    public function test_aislamiento_multiempresa_en_devoluciones(): void
    {
        $ventaA = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoB->id,
                    'cantidad' => 1,
                    'precio_unitario' => 12000.00,
                    'impuesto_porcentaje' => 0,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            pagoCon: 12000.00
        );

        // Admin B intenta acceder al formulario de devolución de la venta de Empresa A
        $response = $this->actingAs($this->adminB)->get(route('devoluciones.create', $ventaA));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }
}
