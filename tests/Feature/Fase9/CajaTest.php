<?php

namespace Tests\Feature\Fase9;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoSesionCaja;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected User $cajeroA;
    protected Caja $cajaA;
    protected Caja $cajaB;

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

        // Crear Caja en Empresa A
        CompanyContext::setCompany($this->empresaA);
        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Principal 01',
            'codigo' => 'CAJ-001',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        // Crear Caja en Empresa B
        CompanyContext::setCompany($this->empresaB);
        $this->cajaB = Caja::create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'nombre' => 'Caja Beta 01',
            'codigo' => 'CAJ-001',
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
        $response = $this->get(route('cajas.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_dashboard_de_cajas_con_metricas(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('cajas.index'));

        $response->assertOk();
        $response->assertViewIs('cajas.index');
        $response->assertSee('Caja Principal 01');
        $response->assertSee('CAJ-001');
        $response->assertDontSee('Caja Beta 01'); // Aislamiento multi-tenant
    }

    public function test_admin_puede_crear_caja_para_su_sucursal(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('cajas.store'), [
            'nombre' => 'Caja Mostrador 02',
            'codigo' => 'CAJ-002',
            'sucursal_id' => $this->sucursalA->id,
        ]);

        $response->assertRedirect(route('cajas.index'));
        $this->assertDatabaseHas('cajas', [
            'empresa_id' => $this->empresaA->id,
            'codigo' => 'CAJ-002',
            'nombre' => 'Caja Mostrador 02',
        ]);
    }

    public function test_cajero_puede_abrir_caja_con_fondo_inicial(): void
    {
        $response = $this->actingAs($this->cajeroA)->post(route('cajas.abrir', $this->cajaA), [
            'monto_apertura' => 150000.00,
            'observaciones' => 'Fondo para vuelto 50k y 100k',
        ]);

        $response->assertRedirect(route('cajas.index'));
        $this->assertDatabaseHas('cajas_sesiones', [
            'empresa_id' => $this->empresaA->id,
            'caja_id' => $this->cajaA->id,
            'user_id' => $this->cajeroA->id,
            'monto_apertura' => 150000.00,
            'estado' => EstadoSesionCaja::ABIERTA->value,
        ]);

        $this->assertTrue($this->cajaA->fresh()->estaAbierta());
    }

    public function test_no_se_puede_abrir_caja_si_ya_cuenta_con_turno_abierto(): void
    {
        // Abrir primera vez
        app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        // Intentar abrir por segunda vez
        $response = $this->actingAs($this->adminA)->post(route('cajas.abrir', $this->cajaA), [
            'monto_apertura' => 50000.00,
        ]);

        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(1, CajaSesion::where('caja_id', $this->cajaA->id)->count());
    }

    public function test_no_se_puede_abrir_caja_con_monto_negativo(): void
    {
        $response = $this->actingAs($this->cajeroA)->post(route('cajas.abrir', $this->cajaA), [
            'monto_apertura' => -500.00,
        ]);

        $response->assertSessionHasErrors(['monto_apertura']);
        $this->assertFalse($this->cajaA->fresh()->estaAbierta());
    }

    public function test_se_puede_registrar_ingreso_de_dinero_en_caja_abierta(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        $response = $this->actingAs($this->cajeroA)->post(route('cajas.movimiento', $this->cajaA), [
            'tipo' => TipoMovimientoCaja::INGRESO->value,
            'concepto' => 'Aporte adicional para caja chica',
            'monto' => 50000.00,
            'metodo_pago' => 'EFECTIVO',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $sesion->id,
            'tipo' => TipoMovimientoCaja::INGRESO->value,
            'monto' => 50000.00,
        ]);

        // Saldo esperado: 100.000 base + 50.000 ingreso = 150.000
        $this->assertEquals(150000.00, $sesion->fresh()->calcularSaldoEsperadoEfectivo());
    }

    public function test_se_puede_registrar_egreso_de_dinero_en_caja_abierta(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        $response = $this->actingAs($this->cajeroA)->post(route('cajas.movimiento', $this->cajaA), [
            'tipo' => TipoMovimientoCaja::EGRESO->value,
            'concepto' => 'Pago de taxi para mensajería',
            'monto' => 20000.00,
            'metodo_pago' => 'EFECTIVO',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('movimientos_caja', [
            'caja_sesion_id' => $sesion->id,
            'tipo' => TipoMovimientoCaja::EGRESO->value,
            'monto' => 20000.00,
        ]);

        // Saldo esperado: 100.000 base - 20.000 egreso = 80.000
        $this->assertEquals(80000.00, $sesion->fresh()->calcularSaldoEsperadoEfectivo());
    }

    public function test_egreso_no_puede_superar_el_efectivo_disponible_en_caja(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        // Intentar retirar 70.000 cuando solo hay 50.000
        $response = $this->actingAs($this->cajeroA)->post(route('cajas.movimiento', $this->cajaA), [
            'tipo' => TipoMovimientoCaja::EGRESO->value,
            'concepto' => 'Retiro excesivo',
            'monto' => 70000.00,
            'metodo_pago' => 'EFECTIVO',
        ]);

        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(50000.00, $sesion->fresh()->calcularSaldoEsperadoEfectivo());
    }

    public function test_no_se_pueden_registrar_movimientos_en_caja_cerrada(): void
    {
        $response = $this->actingAs($this->cajeroA)->post(route('cajas.movimiento', $this->cajaA), [
            'tipo' => TipoMovimientoCaja::INGRESO->value,
            'concepto' => 'Ingreso inválido',
            'monto' => 10000.00,
            'metodo_pago' => 'EFECTIVO',
        ]);

        $response->assertSessionHasErrors(['error']);
        $this->assertEquals(0, \App\Models\MovimientoCaja::count());
    }

    public function test_cierre_de_caja_calcula_saldo_esperado_y_diferencia_de_arqueo(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);

        // Movimientos: +30.000, -10.000 -> Esperado = 120.000
        app(RegistrarMovimientoCajaAction::class)->execute($sesion, TipoMovimientoCaja::INGRESO, 'Ingreso extra', 30000.00);
        app(RegistrarMovimientoCajaAction::class)->execute($sesion, TipoMovimientoCaja::EGRESO, 'Pago fotocopias', 10000.00);

        // Cierre con Arqueo físico de 118.000 (Faltante de -2.000)
        $response = $this->actingAs($this->adminA)->post(route('cajas.cierre.store', $this->cajaA), [
            'monto_cierre_contado' => 118000.00,
            'observaciones' => 'Faltante menor en monedas',
        ]);

        $sesionActualizada = $sesion->fresh();

        $response->assertRedirect(route('cajas.comprobante', $sesionActualizada));
        $this->assertEquals(EstadoSesionCaja::CERRADA, $sesionActualizada->estado);
        $this->assertEquals(120000.00, $sesionActualizada->monto_cierre_esperado);
        $this->assertEquals(118000.00, $sesionActualizada->monto_cierre_contado);
        $this->assertEquals(-2000.00, $sesionActualizada->diferencia);
        $this->assertFalse($this->cajaA->fresh()->estaAbierta());
    }

    public function test_comprobante_de_cierre_se_renderiza_correctamente(): void
    {
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 100000.00);
        app(CerrarCajaAction::class)->execute($sesion, $this->adminA, 100000.00, 'Cierre sin novedades');

        $response = $this->actingAs($this->adminA)->get(route('cajas.comprobante', $sesion));

        $response->assertOk();
        $response->assertViewIs('cajas.comprobante_cierre');
        $response->assertSee('COMPROBANTE DE CIERRE (REPORTE Z)');
        $response->assertSee('Cuadre Exacto');
        $response->assertSee($this->empresaA->nombre);
    }

    public function test_aislamiento_multitenant_estricto_entre_empresas_en_cajas(): void
    {
        // Empresa B no puede ver ni operar caja de Empresa A
        $response = $this->actingAs($this->adminB)->get(route('cajas.show', $this->cajaA));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }

    public function test_usuario_empresa_a_no_puede_abrir_ni_cerrar_caja_de_empresa_b(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('cajas.abrir', $this->cajaB), [
            'monto_apertura' => 50000.00,
        ]);
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
    }
}
