<?php

namespace Tests\Feature\SimulacionVentas;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulacionVentaCompletaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA1;
    protected Sucursal $sucursalA2;
    protected Sucursal $sucursalB;
    protected User $cajeroA;
    protected User $adminA;
    protected User $cajeroB;
    protected Caja $cajaA;
    protected CajaSesion $sesionA;
    protected Cliente $consumidorFinal;
    protected Cliente $clienteCredito;
    protected Producto $productoTornillo;
    protected Producto $productoTaladro;
    protected Producto $productoPintura;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // 1. Configurar Empresa A (Ferretería Principal)
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería El Progreso S.A.S.',
            'moneda' => 'COP',
            'simbolo_moneda' => '$',
        ]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->sucursalA1 = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Centro',
            'es_principal' => true,
        ]);
        $this->sucursalA2 = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Norte',
            'es_principal' => false,
        ]);

        // 2. Configurar Empresa B (Competencia para aislamiento)
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Materiales del Oriente',
        ]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);
        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Oriente',
            'es_principal' => true,
        ]);

        // 3. Usuarios de prueba
        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
        ]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        $this->cajeroB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->cajeroB->assignRole(RolSistema::CAJERO->value);

        // 4. Clientes en Empresa A
        CompanyContext::setCompany($this->empresaA);
        $this->consumidorFinal = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Consumidor Final',
            'numero_documento' => '222222222222',
            'es_predeterminado' => true,
            'cupo_credito' => 0,
            'plazo_dias' => 0,
        ]);

        $this->clienteCredito = Cliente::factory()->for($this->empresaA)->conCredito(1500000.00, 30)->create([
            'razon_social' => 'Constructora Bolívar S.A.S.',
            'numero_documento' => '900987654',
            'es_predeterminado' => false,
        ]);

        // 5. Productos con inventarios reales en Sede Centro
        $this->productoTornillo = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Tornillo Drywall 1 pulgada x100',
            'codigo' => 'TOR-001',
            'codigo_barras' => '7701111111111',
            'precio_compra' => 3000.00,
            'precio_venta' => 6000.00,
            'stock' => 50,
            'stock_minimo' => 5,
            'iva' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoTornillo->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);

        $this->productoTaladro = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor 700W Profesional',
            'codigo' => 'HER-002',
            'codigo_barras' => '7702222222222',
            'precio_compra' => 200000.00,
            'precio_venta' => 350000.00,
            'stock' => 5,
            'stock_minimo' => 1,
            'iva' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoTaladro->id,
            'stock' => 5,
            'stock_minimo' => 1,
        ]);

        $this->productoPintura = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Pintura Vinilo Tipo 1 Blanco Galón',
            'codigo' => 'PIN-003',
            'codigo_barras' => '7703333333333',
            'precio_compra' => 45000.00,
            'precio_venta' => 80000.00,
            'stock' => 20,
            'stock_minimo' => 3,
            'iva' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoPintura->id,
            'stock' => 20,
            'stock_minimo' => 3,
        ]);

        // 6. Configuración de Caja y Turno Abierto
        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'nombre' => 'Caja Principal #1',
            'codigo' => 'CJ-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $this->sesionA = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        CompanyContext::clear();
        BranchContext::clear();
    }

    /**
     * SIMULACIÓN 1: Venta de mostrador POS estándar al contado con pago en efectivo y cálculo de vuelto.
     */
    public function test_simulacion_venta_pos_efectivo_con_cambio_y_descuento_kardex(): void
    {
        // El cajero vende 2 cajas de tornillos ($6.000 c/u + 19% IVA = $7.140 c/u, Total: $14.280)
        // El cliente paga con un billete de $20.000. Vuelto esperado: $5.720.
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'tipo_comprobante' => 'TICKET',
            'pago_con' => 20000.00,
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 2,
                    'precio_unitario' => 6000.00,
                    'descuento' => 0,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 14280.00,
            'cambio' => 5720.00,
        ]);

        // Verificar que el stock físico en la sucursal se redujo de 50 a 48
        $this->assertDatabaseHas('inventarios', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoTornillo->id,
            'stock' => 48,
        ]);

        // Verificar que la venta fue registrada como COMPLETADA con consecutivo correlativo
        $this->assertDatabaseHas('ventas', [
            'empresa_id' => $this->empresaA->id,
            'caja_sesion_id' => $this->sesionA->id,
            'subtotal' => 12000.00,
            'impuesto' => 2280.00,
            'total' => 14280.00,
            'cambio' => 5720.00,
            'estado' => EstadoVenta::COMPLETADA->value,
        ]);

        // Verificar que el dinero en efectivo ingresó a la sesión de caja
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $this->sesionA->id,
            'monto' => 14280.00,
            'tipo' => 'INGRESO',
        ]);
    }

    /**
     * SIMULACIÓN 2: Venta de alto valor con múltiples métodos de pago combinados (Pago Mixto).
     */
    public function test_simulacion_venta_pago_mixto_efectivo_y_tarjeta(): void
    {
        // Venta de 1 Taladro ($350.000 + 19% IVA = $416.500)
        // Pago: $100.000 en Efectivo y $316.500 con Tarjeta / Datáfono
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'MIXTO',
            'tipo_comprobante' => 'FACTURA',
            'pagos' => [
                [
                    'metodo_pago' => 'EFECTIVO',
                    'monto' => 100000.00,
                ],
                [
                    'metodo_pago' => 'TARJETA_DEBITO',
                    'monto' => 316500.00,
                    'referencia' => 'AUTH-778899',
                ],
            ],
            'items' => [
                [
                    'producto_id' => $this->productoTaladro->id,
                    'cantidad' => 1,
                    'precio_unitario' => 350000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 416500.00,
            'cambio' => 0.00,
        ]);

        // Verificar que el detalle de pagos desglosó ambos montos
        $ventaId = $response->json('venta_id');
        $this->assertDatabaseHas('venta_pagos', [
            'venta_id' => $ventaId,
            'metodo_pago' => 'EFECTIVO',
            'monto' => 100000.00,
        ]);
        $this->assertDatabaseHas('venta_pagos', [
            'venta_id' => $ventaId,
            'metodo_pago' => 'TARJETA_DEBITO',
            'monto' => 316500.00,
            'referencia' => 'AUTH-778899',
        ]);

        // En la caja física de efectivo solo debe sumarse la porción en efectivo ($100.000)
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $this->sesionA->id,
            'monto' => 100000.00,
            'tipo' => 'INGRESO',
        ]);

        // Stock disminuyó de 5 a 4
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $this->productoTaladro->id,
            'stock' => 4,
        ]);
    }

    /**
     * SIMULACIÓN 3: Venta a crédito que apertura cuenta por cobrar y reduce cupo del cliente.
     */
    public function test_simulacion_venta_a_credito_apertura_cartera_exitosamente(): void
    {
        // Cliente tiene $1.500.000 de cupo.
        // Compra 1 Galón de pintura ($80.000 + 19% IVA = $95.200) a Crédito
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteCredito->id,
            'tipo_pago' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'tipo_comprobante' => 'FACTURA',
            'items' => [
                [
                    'producto_id' => $this->productoPintura->id,
                    'cantidad' => 1,
                    'precio_unitario' => 80000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 95200.00,
        ]);

        // Verificar que se creó la cuenta por cobrar en el módulo de Cartera
        $this->assertDatabaseHas('cuentas_por_cobrar', [
            'empresa_id' => $this->empresaA->id,
            'cliente_id' => $this->clienteCredito->id,
            'monto_total' => 95200.00,
            'saldo_pendiente' => 95200.00,
            'estado' => 'PENDIENTE',
        ]);

        // NO debe registrar movimiento de ingreso de efectivo en caja porque es crédito
        $this->assertDatabaseMissing('movimientos_caja', [
            'caja_sesion_id' => $this->sesionA->id,
            'monto' => 95200.00,
        ]);

        // El cupo disponible del cliente ahora debe ser $1.500.000 - $95.200 = $1.404.800
        $this->assertEquals(1404800.00, $this->clienteCredito->fresh()->cupoDisponible());
    }

    /**
     * SIMULACIÓN 4: Detección de fallo cuando una venta a crédito supera el cupo permitido.
     */
    public function test_simulacion_rechaza_credito_si_supera_cupo_del_cliente(): void
    {
        // Cliente tiene $1.500.000 de cupo. Intentamos venderle 5 taladros ($2.082.500 con IVA)
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->clienteCredito->id,
            'tipo_pago' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'items' => [
                [
                    'producto_id' => $this->productoTaladro->id,
                    'cantidad' => 5,
                    'precio_unitario' => 350000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        // Debe fallar con código 422 y mensaje explicativo de cupo excedido
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('supera el cupo de crédito disponible', $response->json('error'));

        // El inventario NO debe ser alterado
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $this->productoTaladro->id,
            'stock' => 5,
        ]);
    }

    /**
     * SIMULACIÓN 5: Detección de fallo cuando se intenta vender a crédito a Consumidor Final.
     */
    public function test_simulacion_rechaza_credito_a_consumidor_final(): void
    {
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CREDITO',
            'metodo_pago' => 'CREDITO',
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 1,
                    'precio_unitario' => 6000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(422);
        $this->assertStringContainsString('no tiene crédito comercial habilitado', $response->json('error'));
    }

    /**
     * SIMULACIÓN 6: Detección de fallo cuando el stock es insuficiente en la sucursal.
     */
    public function test_simulacion_rechaza_venta_con_stock_insuficiente(): void
    {
        // Hay 5 taladros en inventario. Intentamos vender 6.
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoTaladro->id,
                    'cantidad' => 6,
                    'precio_unitario' => 350000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(422);
        $this->assertStringContainsString('Stock insuficiente', $response->json('error'));

        // El stock sigue intacto en 5
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $this->productoTaladro->id,
            'stock' => 5,
        ]);
        // No se creó ninguna venta
        $this->assertEquals(0, Venta::count());
    }

    /**
     * SIMULACIÓN 7: Venta con descuento por ítem y precisión decimal.
     */
    public function test_simulacion_venta_con_descuento_calcula_base_e_impuesto_correctos(): void
    {
        // 10 cajas de tornillos a $6.000 = $60.000 subtotal.
        // Descuento especial de $10.000 -> Base gravable: $50.000.
        // IVA 19% sobre $50.000 = $9.500.
        // Total esperado: $59.500.
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 60000.00,
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 10,
                    'precio_unitario' => 6000.00,
                    'descuento' => 10000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 59500.00,
            'cambio' => 500.00,
        ]);

        $this->assertDatabaseHas('ventas', [
            'subtotal' => 60000.00,
            'descuento' => 10000.00,
            'impuesto' => 9500.00,
            'total' => 59500.00,
            'cambio' => 500.00,
        ]);
    }

    /**
     * SIMULACIÓN 8: Flujo de Anulación de venta y reversión completa (Kardex + Caja).
     */
    public function test_simulacion_anulacion_de_venta_devuelve_stock_y_egresa_caja(): void
    {
        // 1. Registrar venta inicial de 3 pinturas
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoPintura->id,
                    'cantidad' => 3,
                    'precio_unitario' => 80000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $resVenta = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);
        $ventaId = $resVenta->json('venta_id');

        // Stock original 20 - 3 = 17
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $this->productoPintura->id,
            'stock' => 17,
        ]);

        // 2. Administrador anula la venta
        $venta = Venta::findOrFail($ventaId);
        $resAnular = $this->actingAs($this->adminA)
            ->post(route('ventas.anular', $venta), [
                'motivo' => 'Cliente canceló la compra antes de salir de tienda',
            ]);

        $resAnular->assertSessionHas('success');

        // 3. Verificar estado de la venta
        $this->assertEquals(EstadoVenta::ANULADA, $venta->fresh()->estado);

        // 4. Verificar que el stock regresó a 20
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $this->productoPintura->id,
            'stock' => 20,
        ]);

        // 5. Verificar que se registró el EGRESO de devolución en la sesión de caja
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $this->sesionA->id,
            'monto' => $venta->total,
            'tipo' => 'EGRESO',
        ]);
    }

    /**
     * SIMULACIÓN 9: Aislamiento Multitenant (Intento de cruce de datos entre empresas).
     */
    public function test_simulacion_aislamiento_empresa_b_no_puede_vender_en_caja_empresa_a(): void
    {
        // Cajero de Empresa B intenta procesar una venta enviando la sesión de caja de Empresa A
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 1,
                    'precio_unitario' => 6000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroB)->postJson(route('pos.procesar'), $payload);

        // Debe ser denegado por políticas de autorización o validación de tenant
        $this->assertContains($response->status(), [403, 422, 404]);
    }

    /**
     * SIMULACIÓN 10: Ciclo diario completo con cierre de caja y arqueo de turno.
     */
    public function test_simulacion_ciclo_completo_con_cierre_de_caja_y_arqueo(): void
    {
        // 1. Fondo inicial: $100.000
        // 2. Venta 1: $14.280 en Efectivo
        $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 2,
                    'precio_unitario' => 6000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ]);

        // 3. Venta 2: $95.200 en Efectivo
        $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoPintura->id,
                    'cantidad' => 1,
                    'precio_unitario' => 80000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ]);

        // Total esperado en caja: $100.000 + $14.280 + $95.200 = $209.480
        $saldoEsperado = $this->sesionA->fresh()->calcularSaldoEsperadoEfectivo();
        $this->assertEquals(209480.00, $saldoEsperado);

        // 4. Cajero cierra la caja con $209.480 (arqueo exacto, diferencia 0)
        $sesionCerrada = app(CerrarCajaAction::class)->execute(
            sesion: $this->sesionA->fresh(),
            auditor: $this->cajeroA,
            montoContado: 209480.00,
            observaciones: 'Turno de la tarde cerrado sin novedades.'
        );

        $this->assertEquals(EstadoSesionCaja::CERRADA, $sesionCerrada->estado);
        $this->assertEquals(0.00, (float) $sesionCerrada->diferencia);
        $this->assertNotNull($sesionCerrada->fecha_cierre);
    }

    /**
     * SIMULACIÓN 11 (CASO BORDE / DEFECTO): Rechazo de venta en efectivo con pago insuficiente.
     */
    public function test_simulacion_rechaza_pago_efectivo_menor_al_total(): void
    {
        // Total de la venta: 2 tornillos a $6.000 + 19% IVA = $14.280.
        // El cliente solo entrega $10.000 en efectivo (insuficiente).
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 10000.00,
            'items' => [
                [
                    'producto_id' => $this->productoTornillo->id,
                    'cantidad' => 2,
                    'precio_unitario' => 6000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        // Debe fallar con 422 indicando que el monto recibido es inferior al total
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('insuficiente', strtolower($response->json('error')));
        $this->assertEquals(50, Inventario::where('producto_id', $this->productoTornillo->id)->first()->stock);
    }

    /**
     * SIMULACIÓN 12 (CASO BORDE / DEFECTO): Rechazo de pago mixto incompleto.
     */
    public function test_simulacion_rechaza_pago_mixto_menor_al_total(): void
    {
        // Venta de Taladro ($350.000 + 19% IVA = $416.500)
        // Se envían pagos parciales que solo suman $250.000 ($100.000 efec + $150.000 debito)
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'MIXTO',
            'pagos' => [
                ['metodo_pago' => 'EFECTIVO', 'monto' => 100000.00],
                ['metodo_pago' => 'TARJETA_DEBITO', 'monto' => 150000.00],
            ],
            'items' => [
                [
                    'producto_id' => $this->productoTaladro->id,
                    'cantidad' => 1,
                    'precio_unitario' => 350000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('no cubre el total', strtolower($response->json('error')));
    }

    /**
     * SIMULACIÓN 13: Límite de stock exacto y agotamiento controlado.
     */
    public function test_simulacion_limite_stock_exacto_y_agotamiento(): void
    {
        // Hay exactamente 5 taladros.
        // 1. Vendemos exactamente los 5 taladros
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 3000000.00,
            'items' => [
                [
                    'producto_id' => $this->productoTaladro->id,
                    'cantidad' => 5,
                    'precio_unitario' => 350000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);
        $response->assertStatus(200);

        // El stock físico ahora es 0
        $inv = Inventario::where('producto_id', $this->productoTaladro->id)
            ->where('sucursal_id', $this->sucursalA1->id)
            ->first();
        $this->assertEquals(0, $inv->stock);

        // 2. Intentamos vender 1 más cuando el stock es 0
        $payload['items'][0]['cantidad'] = 1;
        $resAgotado = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);
        $resAgotado->assertStatus(422);
        $this->assertStringContainsString('Stock insuficiente', $resAgotado->json('error'));
    }

    /**
     * SIMULACIÓN 14: Anulación de venta cuando el turno de caja ya fue cerrado (comportamiento seguro).
     */
    public function test_simulacion_anulacion_de_venta_con_caja_ya_cerrada(): void
    {
        // 1. Venta de 1 pintura en turno actual
        $payload = [
            'caja_sesion_id' => $this->sesionA->id,
            'cliente_id' => $this->consumidorFinal->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [
                [
                    'producto_id' => $this->productoPintura->id,
                    'cantidad' => 1,
                    'precio_unitario' => 80000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];
        $resVenta = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload);
        $ventaId = $resVenta->json('venta_id');
        $this->assertEquals(19, Inventario::where('producto_id', $this->productoPintura->id)->first()->stock);

        // 2. Se cierra el turno de caja formalmente
        app(CerrarCajaAction::class)->execute(
            sesion: $this->sesionA->fresh(),
            auditor: $this->cajeroA,
            montoContado: $this->sesionA->fresh()->calcularSaldoEsperadoEfectivo()
        );

        // 3. Al día siguiente o más tarde, un administrador anula la venta
        $venta = Venta::findOrFail($ventaId);
        $resAnular = $this->actingAs($this->adminA)->post(route('ventas.anular', $venta), [
            'motivo' => 'Garantía por defecto de fábrica en lata sellada',
        ]);

        $resAnular->assertSessionHas('success');
        $this->assertEquals(EstadoVenta::ANULADA, $venta->fresh()->estado);

        // El stock debe haber regresado a 20 aunque la caja esté cerrada
        $this->assertEquals(20, Inventario::where('producto_id', $this->productoPintura->id)->first()->stock);
    }
}

