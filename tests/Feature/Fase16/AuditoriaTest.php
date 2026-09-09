<?php

namespace Tests\Feature\Fase16;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Actions\Inventario\RealizarAjusteInventarioAction;
use App\Actions\Ventas\AnularVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoDevolucion;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Auditoria;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Support\Tenancy\CompanyContext;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaTest extends TestCase
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
    protected Cliente $clienteA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Auditoría',
            'nit' => '901111222-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Alfa',
            'es_principal' => true,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'email' => 'admin@alfa.com',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->vendedorA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'email' => 'vendedor@alfa.com',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->vendedorA->assignRole(RolSistema::VENDEDOR->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta Auditoría',
            'nit' => '901333444-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Beta',
            'es_principal' => true,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'email' => 'admin@beta.com',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Artículos y Clientes para Empresa A
        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor Industrial',
            'codigo' => 'HERR-001',
            'precio_compra' => 150000.00,
            'precio_venta' => 250000.00,
            'stock' => 20,
            'stock_minimo' => 3,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 20, 'costo_promedio_ponderado' => 150000.00]
        );

        $this->clienteA = Cliente::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Constructora Nacional SAS',
        ]);

        CompanyContext::clear();
    }

    public function test_invitado_no_puede_acceder_a_auditoria(): void
    {
        $response = $this->get(route('auditoria.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        // Vendedor no tiene permiso auditoria.ver
        $response = $this->actingAs($this->vendedorA)->get(route('auditoria.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_bitacora_auditoria(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('auditoria.index'));
        $response->assertStatus(200);
        $response->assertSee('Bitácora de Auditoría');
    }

    public function test_anulacion_venta_registra_auditoria_automaticamente(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $caja = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja 1',
            'codigo' => 'C1',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-AUDIT-01',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 250000.00,
            'descuento' => 0.00,
            'impuesto' => 0.00,
            'total' => 250000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        VentaDetalle::create([
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'producto_id' => $this->productoA->id,
            'cantidad' => 1,
            'precio_unitario' => 250000.00,
            'costo_unitario' => 150000.00,
            'descuento' => 0.00,
            'impuesto_porcentaje' => 0.00,
            'impuesto_monto' => 0.00,
            'subtotal' => 250000.00,
            'total' => 250000.00,
        ]);

        // Ejecutar acción de anulación
        $action = app(AnularVentaAction::class);
        $action->execute($venta, $this->adminA, 'Cliente canceló el pedido en obra');

        CompanyContext::clear();

        // Verificar que exista registro en auditorias
        $this->assertDatabaseHas('auditorias', [
            'empresa_id' => $this->empresaA->id,
            'user_id' => $this->adminA->id,
            'modulo' => 'VENTAS',
            'accion' => 'ANULAR_VENTA',
            'auditable_type' => Venta::class,
            'auditable_id' => $venta->id,
        ]);

        $auditoria = Auditoria::where('auditable_id', $venta->id)->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals(EstadoVenta::COMPLETADA->value, $auditoria->datos_anteriores['estado']);
        $this->assertEquals(EstadoVenta::ANULADA->value, $auditoria->datos_nuevos['estado']);
        $this->assertStringContainsString('Cliente canceló el pedido en obra', $auditoria->descripcion);
    }

    public function test_devolucion_registra_auditoria_automaticamente(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $this->clienteA->id,
            'user_id' => $this->adminA->id,
            'numero_venta' => 'V-DEV-01',
            'tipo_comprobante' => 'TICKET',
            'fecha' => Carbon::now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 250000.00,
            'descuento' => 0.00,
            'impuesto' => 0.00,
            'total' => 250000.00,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        $detalle = VentaDetalle::create([
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'producto_id' => $this->productoA->id,
            'cantidad' => 1,
            'precio_unitario' => 250000.00,
            'costo_unitario' => 150000.00,
            'descuento' => 0.00,
            'impuesto_porcentaje' => 0.00,
            'impuesto_monto' => 0.00,
            'subtotal' => 250000.00,
            'total' => 250000.00,
        ]);

        $action = app(RegistrarDevolucionAction::class);
        $devolucion = $action->execute(
            venta: $venta,
            userId: $this->adminA->id,
            items: [
                ['venta_detalle_id' => $detalle->id, 'cantidad' => 1],
            ],
            tipoReintegro: TipoReintegroDevolucion::SALDO_FAVOR,
            motivo: 'Producto con empaque defectuoso'
        );

        CompanyContext::clear();

        $this->assertDatabaseHas('auditorias', [
            'empresa_id' => $this->empresaA->id,
            'user_id' => $this->adminA->id,
            'modulo' => 'DEVOLUCIONES',
            'accion' => 'DEVOLUCION',
            'auditable_type' => get_class($devolucion),
            'auditable_id' => $devolucion->id,
        ]);
    }

    public function test_apertura_y_cierre_caja_registran_auditoria(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $caja = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja de Prueba Turnos',
            'codigo' => 'CPT-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        // 1. Apertura
        $abrirAction = app(AbrirCajaAction::class);
        $sesion = $abrirAction->execute($caja, $this->adminA, 100000.00, 'Turno Mañana');

        $this->assertDatabaseHas('auditorias', [
            'empresa_id' => $this->empresaA->id,
            'modulo' => 'CAJA',
            'accion' => 'APERTURA_CAJA',
            'auditable_id' => $sesion->id,
        ]);

        // 2. Cierre
        $cerrarAction = app(CerrarCajaAction::class);
        $cerrarAction->execute($sesion, $this->adminA, 100000.00, 'Cierre sin novedades');

        CompanyContext::clear();

        $this->assertDatabaseHas('auditorias', [
            'empresa_id' => $this->empresaA->id,
            'modulo' => 'CAJA',
            'accion' => 'CIERRE_CAJA',
            'auditable_id' => $sesion->id,
        ]);
    }

    public function test_ajuste_inventario_registra_auditoria(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $action = app(RealizarAjusteInventarioAction::class);
        $action->execute(
            productoId: $this->productoA->id,
            sucursalId: $this->sucursalA->id,
            tipo: TipoMovimientoInventario::AJUSTE_POSITIVO,
            cantidad: 5,
            motivo: 'Conteo físico anual - sobrante encontrado',
            userId: $this->adminA->id
        );

        CompanyContext::clear();

        $this->assertDatabaseHas('auditorias', [
            'empresa_id' => $this->empresaA->id,
            'user_id' => $this->adminA->id,
            'modulo' => 'INVENTARIO',
            'accion' => 'AJUSTE_INVENTARIO',
        ]);
    }

    public function test_aislamiento_multitenant_estricto_en_auditoria(): void
    {
        // Empresa B crea auditoría secreta
        CompanyContext::setCompany($this->empresaB);
        $auditoriaB = Auditoria::create([
            'empresa_id' => $this->empresaB->id,
            'user_id' => $this->adminB->id,
            'modulo' => 'SECRET',
            'accion' => 'ACCION_CONFIDENCIAL_B',
            'descripcion' => 'Operación secreta en Empresa B',
            'ip' => '10.0.0.99',
        ]);
        CompanyContext::clear();

        // Admin de Empresa A consulta bitácora
        $response = $this->actingAs($this->adminA)->get(route('auditoria.index'));
        $response->assertStatus(200);
        $response->assertDontSee('ACCION_CONFIDENCIAL_B');
        $response->assertDontSee('Operación secreta en Empresa B');

        // Admin de Empresa A intenta acceder al detalle de auditoría de Empresa B
        $responseDetalle = $this->actingAs($this->adminA)->get(route('auditoria.show', $auditoriaB));
        $this->assertContains($responseDetalle->status(), [403, 404]);
    }

    public function test_vista_detalle_muestra_diffs(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $auditoria = Auditoria::create([
            'empresa_id' => $this->empresaA->id,
            'user_id' => $this->adminA->id,
            'modulo' => 'VENTAS',
            'accion' => 'ANULAR_VENTA',
            'descripcion' => 'Anulación de prueba para diff',
            'datos_anteriores' => ['estado' => 'COMPLETADA', 'total' => 200],
            'datos_nuevos' => ['estado' => 'ANULADA', 'total' => 200],
            'ip' => '192.168.1.50',
        ]);
        CompanyContext::clear();

        $response = $this->actingAs($this->adminA)->get(route('auditoria.show', $auditoria));
        $response->assertStatus(200);
        $response->assertSee('COMPLETADA');
        $response->assertSee('ANULADA');
        $response->assertSee('192.168.1.50');
    }
}
