<?php

namespace Tests\Feature\Fase15;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCompra;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoCaja;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Support\Tenancy\CompanyContext;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $vendedorA;
    protected User $adminB;
    protected Producto $productoA;
    protected Producto $productoB;
    protected Cliente $clienteA;
    protected Cliente $clienteB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Test A',
            'nit' => '900111222-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Norte A',
            'es_principal' => true,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'email' => 'admin@empresa-a.com',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->vendedorA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'email' => 'vendedor@empresa-a.com',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->vendedorA->assignRole(RolSistema::VENDEDOR->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Test B',
            'nit' => '900333444-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Sur B',
            'es_principal' => true,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'email' => 'admin@empresa-b.com',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Catálogos e Inventario Empresa A
        CompanyContext::setCompany($this->empresaA);
        $categoriaA = Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Bebidas A',
        ]);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'categoria_id' => $categoriaA->id,
            'nombre' => 'Jugo Natural A',
            'codigo' => 'PROD-A01',
            'precio_compra' => 5000.00,
            'precio_costo' => 5000.00,
            'precio_venta' => 10000.00,
            'stock' => 50,
            'stock_minimo' => 5,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 50, 'costo_promedio_ponderado' => 5000.00]
        );

        $this->clienteA = Cliente::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Cliente Mayorista A',
            'numero_documento' => '1001',
        ]);

        // Catálogos e Inventario Empresa B
        CompanyContext::setCompany($this->empresaB);
        $this->productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Producto Secreto B',
            'codigo' => 'PROD-B01',
            'precio_compra' => 20000.00,
            'precio_costo' => 20000.00,
            'precio_venta' => 40000.00,
            'stock' => 100,
            'stock_minimo' => 10,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaB->id, 'sucursal_id' => $this->sucursalB->id, 'producto_id' => $this->productoB->id],
            ['stock' => 100, 'costo_promedio_ponderado' => 20000.00]
        );

        $this->clienteB = Cliente::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Cliente Exclusivo B',
            'numero_documento' => '2002',
        ]);

        CompanyContext::clear();
    }

    public function test_invitado_no_puede_acceder_a_reportes(): void
    {
        $response = $this->get(route('reportes.index'));
        $response->assertRedirect(route('login'));

        $responseVentas = $this->get(route('reportes.ventas'));
        $responseVentas->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        // Vendedor no tiene permiso reportes.ver
        $response = $this->actingAs($this->vendedorA)->get(route('reportes.index'));
        $response->assertStatus(403);

        $responseVentas = $this->actingAs($this->vendedorA)->get(route('reportes.ventas'));
        $responseVentas->assertStatus(403);
    }

    public function test_admin_puede_ver_centro_de_reportes(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('reportes.index'));
        $response->assertStatus(200);
        $response->assertSee('Centro de Reportes');
        $response->assertSee('Ventas Detalladas');
        $response->assertSee('Utilidad y Rentabilidad');
        $response->assertSee('Valoración de Stock');
    }

    public function test_dashboard_muestra_kpis_financieros(): void
    {
        // Registrar una venta para hoy
        CompanyContext::setCompany($this->empresaA);
        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-001',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 100000.00,
            'descuento' => 0.00,
            'impuesto' => 19000.00,
            'total' => 119000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        VentaDetalle::create([
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'producto_id' => $this->productoA->id,
            'cantidad' => 10,
            'precio_unitario' => 10000.00,
            'costo_unitario' => 5000.00,
            'descuento' => 0.00,
            'impuesto_porcentaje' => 19.00,
            'impuesto_monto' => 19000.00,
            'subtotal' => 100000.00,
            'total' => 119000.00,
        ]);

        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('119,000.00'); // Total ventas hoy / mes
        $response->assertSee('50,000.00');  // Utilidad estimada: 100k - (10*5k=50k) = 50k
    }

    public function test_reporte_ventas_calcula_totales_y_aplica_filtros(): void
    {
        CompanyContext::setCompany($this->empresaA);
        Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-101',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::today(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 20000.00,
            'descuento' => 0.00,
            'impuesto' => 3800.00,
            'total' => 23800.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.ventas', [
            'fecha_desde' => Carbon::today()->toDateString(),
            'fecha_hasta' => Carbon::today()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('V-101');
        $response->assertSee('23,800.00');
    }

    public function test_reporte_utilidad_calcula_margen_bruto(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-201',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 50000.00,
            'descuento' => 0.00,
            'impuesto' => 0.00,
            'total' => 50000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        // Venta 5 unidades a $10,000 c/u (costo $5,000 c/u) -> Ingreso 50,000, Costo 25,000, Utilidad 25,000 (50%)
        VentaDetalle::create([
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'producto_id' => $this->productoA->id,
            'cantidad' => 5,
            'precio_unitario' => 10000.00,
            'costo_unitario' => 5000.00,
            'descuento' => 0.00,
            'impuesto_porcentaje' => 0.00,
            'impuesto_monto' => 0.00,
            'subtotal' => 50000.00,
            'total' => 50000.00,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.utilidad'));
        $response->assertStatus(200);
        $response->assertSee('25,000.00'); // Utilidad bruta
        $response->assertSee('50%');      // Margen
    }

    public function test_reporte_inventario_calcula_costo_y_valor_comercial(): void
    {
        // 50 unidades de Jugo Natural A: costo $5,000 -> $250,000. Venta $10,000 -> $500,000.
        $response = $this->actingAs($this->adminA)->get(route('reportes.inventario'));
        $response->assertStatus(200);
        $response->assertSee('Jugo Natural A');
        $response->assertSee('250,000.00'); // Costo total valorizado
        $response->assertSee('500,000.00'); // Valor venta proyectado
    }

    public function test_reporte_cajas_muestra_sesiones_y_diferencias(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $caja = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Principal Test',
            'codigo' => 'CAJA-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        CajaSesion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'caja_id' => $caja->id,
            'user_id' => $this->adminA->id,
            'user_cierre_id' => $this->adminA->id,
            'fecha_apertura' => Carbon::now()->subHours(4),
            'fecha_cierre' => Carbon::now(),
            'monto_apertura' => 100000.00,
            'monto_cierre_esperado' => 250000.00,
            'monto_cierre_contado' => 250000.00,
            'diferencia' => 0.00,
            'total_ingresos' => 150000.00,
            'total_egresos' => 0.00,
            'total_ventas_efectivo' => 150000.00,
            'total_ventas_electronico' => 0.00,
            'estado' => EstadoSesionCaja::CERRADA,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.cajas'));
        $response->assertStatus(200);
        $response->assertSee('Caja Principal Test');
        $response->assertSee('250,000.00');
    }

    public function test_reporte_cartera_clasifica_por_antiguedad(): void
    {
        CompanyContext::setCompany($this->empresaA);
        // Cuenta corriente (vence en 15 días)
        CuentaPorCobrar::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'numero_documento' => 'CC-001',
            'concepto' => 'Crédito Corriente',
            'monto_total' => 80000.00,
            'monto_pagado' => 0.00,
            'saldo_pendiente' => 80000.00,
            'fecha_emision' => Carbon::today(),
            'fecha_vencimiento' => Carbon::today()->addDays(15),
            'estado' => EstadoCuentaCobrar::PENDIENTE,
            'created_by' => $this->adminA->id,
        ]);

        // Cuenta vencida (venció hace 10 días -> mora 1-30)
        CuentaPorCobrar::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'numero_documento' => 'CC-002',
            'concepto' => 'Crédito Vencido',
            'monto_total' => 50000.00,
            'monto_pagado' => 0.00,
            'saldo_pendiente' => 50000.00,
            'fecha_emision' => Carbon::today()->subDays(40),
            'fecha_vencimiento' => Carbon::today()->subDays(10),
            'estado' => EstadoCuentaCobrar::PENDIENTE,
            'created_by' => $this->adminA->id,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.cartera'));
        $response->assertStatus(200);
        $response->assertSee('130,000.00'); // Saldo total pendiente (80k + 50k)
        $response->assertSee('80,000.00');  // Corriente
        $response->assertSee('50,000.00');  // Mora 1-30
    }

    public function test_reporte_metodos_pago_desglosa_transacciones(): void
    {
        CompanyContext::setCompany($this->empresaA);
        Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-PAGO-01',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'TARJETA',
            'subtotal' => 60000.00,
            'descuento' => 0.00,
            'impuesto' => 0.00,
            'total' => 60000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.metodos-pago'));
        $response->assertStatus(200);
        $response->assertSee('TARJETA');
        $response->assertSee('60,000.00');
    }

    public function test_reporte_impuestos_calcula_balance_fiscal(): void
    {
        CompanyContext::setCompany($this->empresaA);
        // Venta con IVA 19,000
        Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-IVA-01',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 100000.00,
            'descuento' => 0.00,
            'impuesto' => 19000.00,
            'total' => 119000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        // Proveedor y Compra con IVA descontable 9,500
        $proveedor = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Proveedor Insumos SAS',
            'tipo_documento' => 'NIT',
            'numero_documento' => '800555666-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Compra::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => $this->adminA->id,
            'numero_factura' => 'FAC-999',
            'fecha_emision' => Carbon::now(),
            'subtotal' => 50000.00,
            'impuestos' => 9500.00,
            'descuento' => 0.00,
            'total' => 59500.00,
            'tipo_pago' => TipoPago::CONTADO,
            'estado' => EstadoCompra::REGISTRADA,
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('reportes.impuestos'));
        $response->assertStatus(200);
        $response->assertSee('19,000.00'); // IVA Generado
        $response->assertSee('9,500.00');  // IVA Descontable
        $response->assertSee('9,500.00');  // Saldo a pagar neto (19,000 - 9,500 = 9,500)
    }

    public function test_aislamiento_multitenant_estricto_en_reportes(): void
    {
        // Empresa B crea venta confidencial
        CompanyContext::setCompany($this->empresaB);
        Venta::create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'cliente_id' => $this->clienteB->id,
            'user_id' => $this->adminB->id,
            'numero_venta' => 'VB-TOPSECRET',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 999999.00,
            'descuento' => 0.00,
            'impuesto' => 0.00,
            'total' => 999999.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);
        CompanyContext::clear();

        // Admin de Empresa A consulta reportes de ventas
        $responseVentas = $this->actingAs($this->adminA)->get(route('reportes.ventas'));
        $responseVentas->assertStatus(200);
        $responseVentas->assertDontSee('VB-TOPSECRET');
        $responseVentas->assertDontSee('999,999.00');
        $responseVentas->assertDontSee('Cliente Exclusivo B');

        // Admin de Empresa A consulta inventario
        $responseInventario = $this->actingAs($this->adminA)->get(route('reportes.inventario'));
        $responseInventario->assertStatus(200);
        $responseInventario->assertDontSee('Producto Secreto B');
    }

    public function test_vistas_imprimibles_renderizan_exitosamente(): void
    {
        $this->actingAs($this->adminA)->get(route('reportes.ventas.imprimir'))->assertStatus(200);
        $this->actingAs($this->adminA)->get(route('reportes.compras.imprimir'))->assertStatus(200);
        $this->actingAs($this->adminA)->get(route('reportes.utilidad.imprimir'))->assertStatus(200);
        $this->actingAs($this->adminA)->get(route('reportes.inventario.imprimir'))->assertStatus(200);
        $this->actingAs($this->adminA)->get(route('reportes.cajas.imprimir'))->assertStatus(200);
        $this->actingAs($this->adminA)->get(route('reportes.cartera.imprimir'))->assertStatus(200);
    }
}
