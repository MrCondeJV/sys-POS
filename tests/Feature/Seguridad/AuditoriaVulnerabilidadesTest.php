<?php

namespace Tests\Feature\Seguridad;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Actions\Inventario\RealizarTrasladoInventarioAction;
use App\Actions\Multisucursal\DespacharTrasladoAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoVenta;
use App\Enums\MetodoPagoCartera;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AuditoriaVulnerabilidadesTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $cajeroA;
    protected User $cajeroB;
    protected Caja $cajaA;
    protected CajaSesion $sesionA;
    protected Cliente $clienteA;
    protected Producto $productoA;
    protected Producto $productoInactivoA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A (Víctima potencial / Tenant Principal)
        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Ferretería Central S.A.S.']);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Principal A',
            'es_principal' => true,
        ]);

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

        // Empresa B (Atacante o Tenant Aislado)
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Materiales Norte Ltda.']);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Norte B',
            'es_principal' => true,
        ]);

        $this->cajeroB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->cajeroB->assignRole(RolSistema::CAJERO->value);

        // Clientes y Productos Empresa A
        CompanyContext::setCompany($this->empresaA);
        $this->clienteA = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Cliente Mostrador',
            'es_predeterminado' => true,
            'cupo_credito' => 0,
        ]);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Tornillo Acero 1 pulg',
            'codigo' => 'TOR-SEC-01',
            'precio_compra' => 2000.00,
            'precio_venta' => 5000.00,
            'stock' => 50,
            'iva' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);

        $this->productoInactivoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Producto Descontinuado Peligroso',
            'codigo' => 'PROD-DESC-99',
            'precio_compra' => 10000.00,
            'precio_venta' => 25000.00,
            'stock' => 10,
            'iva' => 19,
            'estado' => EstadoGeneral::INACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoInactivoA->id,
            'stock' => 10,
            'stock_minimo' => 1,
        ]);

        // Caja y Sesión abierta Empresa A
        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja #1 A',
            'codigo' => 'CJ-A1',
            'estado' => EstadoCaja::ACTIVA,
        ]);
        $this->sesionA = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        CompanyContext::clear();
        BranchContext::clear();
    }

    /**
     * VULNERABILIDAD 1: Intento de aplicar un descuento mayor al valor del producto (Generación de saldo negativo o robo).
     */
    public function test_vulnerabilidad_descuento_excesivo_debe_ser_rechazado(): void
    {
        // Producto vale $5.000. Intentan enviar un descuento de $10.000.
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 5000.00,
                    'descuento' => 10000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(422);
        $this->assertStringContainsString('descuento', strtolower($response->json('error')));
        // Stock debe permanecer intacto
        $this->assertEquals(50, Inventario::where('producto_id', $this->productoA->id)->first()->stock);
    }

    /**
     * VULNERABILIDAD 2: Intento de descuento negativo para inflar precio.
     */
    public function test_vulnerabilidad_descuento_negativo_debe_ser_rechazado(): void
    {
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 5000.00,
                    'descuento' => -2000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);
        $response->assertStatus(422);
    }

    /**
     * VULNERABILIDAD 3: Intento de vender un producto descontinuado o marcado como INACTIVO.
     */
    public function test_vulnerabilidad_producto_inactivo_no_puede_ser_vendido(): void
    {
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoInactivoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 25000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(422);
        $this->assertStringContainsString('inactivo', strtolower($response->json('error')));
        $this->assertEquals(10, Inventario::where('producto_id', $this->productoInactivoA->id)->first()->stock);
    }

    /**
     * VULNERABILIDAD 4: Intento de procesar venta sobre un turno de caja ya CERRADO.
     */
    public function test_vulnerabilidad_venta_en_caja_cerrada_debe_ser_rechazada(): void
    {
        // 1. Cerramos formalmente la sesión de caja
        app(CerrarCajaAction::class)->execute(
            sesion: $this->sesionA,
            auditor: $this->cajeroA,
            montoContado: 100000.00
        );

        // 2. Intentamos procesar una venta en esa sesión ya cerrada
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 10000.00,
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 5000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        // Debe ser rechazada porque el turno de caja no está abierto
        $response->assertStatus(422);
        $this->assertStringContainsString('cerrado', strtolower($response->json('error')));
        $this->assertEquals(0, Venta::count());
    }

    /**
     * VULNERABILIDAD 5: Secuestro de caja multitenant (Empresa B intenta usar caja_sesion_id de Empresa A).
     */
    public function test_vulnerabilidad_secuestro_de_caja_entre_empresas_debe_ser_bloqueado(): void
    {
        // Cajero B intenta vender usando la sesión de caja de Empresa A
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 5000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroB)->postJson(route('pos.procesar'), $payload);

        $this->assertContains($response->status(), [403, 404, 422]);
        // Verificar que no se alteró la caja de la Empresa A
        $this->assertEquals(100000.00, $this->sesionA->fresh()->calcularSaldoEsperadoEfectivo());
    }

    /**
     * VULNERABILIDAD 6: Fuga de inventario entre empresas distintas vía Traslado.
     */
    public function test_vulnerabilidad_traslado_entre_sucursales_de_distintas_empresas_debe_fallar(): void
    {
        $action = app(RealizarTrasladoInventarioAction::class);

        // Intentar trasladar del inventario de Empresa A a la sucursal de Empresa B
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/diferentes empresas/i');

        $action->execute(
            productoId: $this->productoA->id,
            sucursalOrigenId: $this->sucursalA->id,
            sucursalDestinoId: $this->sucursalB->id,
            cantidad: 5,
            motivo: 'Intento de transferencia entre empresas'
        );
    }

    /**
     * VULNERABILIDAD 7: Despachar traslado multisucursal entre empresas distintas vía DespacharTrasladoAction.
     */
    public function test_vulnerabilidad_despacho_traslado_entre_distintas_empresas_debe_fallar(): void
    {
        $action = app(DespacharTrasladoAction::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/diferentes empresas/i');

        $action->execute(
            sucursalOrigenId: $this->sucursalA->id,
            sucursalDestinoId: $this->sucursalB->id,
            motivo: 'Intento de despacho cruzado',
            items: [
                ['producto_id' => $this->productoA->id, 'cantidad' => 2],
            ]
        );
    }

    /**
     * VULNERABILIDAD 8: Egreso fraudulento de caja que supera el saldo en efectivo disponible.
     */
    public function test_vulnerabilidad_egreso_de_caja_superior_al_saldo_disponible_debe_fallar(): void
    {
        // Saldo inicial es $100.000. Intentamos sacar $200.000.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/fondos insuficientes/i');

        app(RegistrarMovimientoCajaAction::class)->execute(
            sesion: $this->sesionA,
            tipo: TipoMovimientoCaja::EGRESO,
            concepto: 'Retiro no respaldado',
            monto: 200000.00,
            usuario: $this->cajeroA
        );
    }

    /**
     * VULNERABILIDAD 9: Egreso con monto negativo para sumar dinero sin rastro.
     */
    public function test_vulnerabilidad_movimiento_de_caja_con_monto_negativo_debe_fallar(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(RegistrarMovimientoCajaAction::class)->execute(
            sesion: $this->sesionA,
            tipo: TipoMovimientoCaja::EGRESO,
            concepto: 'Egreso negativo',
            monto: -50000.00,
            usuario: $this->cajeroA
        );
    }

    /**
     * VULNERABILIDAD 10: Sobrepago malicioso en abono a cuenta por cobrar (Cartera).
     */
    public function test_vulnerabilidad_sobrepago_de_abono_a_cartera_debe_fallar(): void
    {
        $cxc = app(RegistrarCuentaPorCobrarAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 50000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura para prueba de sobrepago'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/supera el saldo pendiente/i');

        // Intentar abonar $80.000 a una deuda de $50.000
        app(RegistrarAbonoCarteraAction::class)->execute(
            cuenta: $cxc,
            monto: 80000.00,
            metodoPago: MetodoPagoCartera::EFECTIVO,
            fechaPago: now()->toDateString()
        );
    }

    /**
     * VULNERABILIDAD 11: IDOR - Cajero de Empresa B no debe ver detalle ni ticket de Empresa A.
     */
    public function test_vulnerabilidad_idor_cajero_b_no_puede_ver_ticket_de_empresa_a(): void
    {
        // 1. Crear venta en Empresa A
        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->cajeroA->id,
            'caja_sesion_id' => $this->sesionA->id,
            'numero_venta' => 'VTA-99999',
            'fecha' => now(),
            'tipo_pago' => \App\Enums\TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 10000.00,
            'total' => 11900.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        // 2. Cajero B intenta acceder al show y al ticket
        CompanyContext::setCompany($this->empresaB);
        $resShow = $this->actingAs($this->cajeroB)->get(route('ventas.show', $venta));
        $this->assertContains($resShow->status(), [403, 404]);

        $resTicket = $this->actingAs($this->cajeroB)->get(route('ventas.ticket', $venta));
        $this->assertContains($resTicket->status(), [403, 404]);
    }
}
