<?php

namespace Tests\Feature\Fase23;

use App\Enums\EstadoGeneral;
use App\Enums\EstadoLote;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Laboratorio;
use App\Models\NotificacionSistema;
use App\Models\PrincipioActivo;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\FarmaciaAlertasService;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrogueriaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected Producto $medicamentoA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Farmacia Cruz Verde Alfa',
            'nit' => '900555666-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Droguería Principal Alfa',
            'codigo' => 'DROG-ALFA',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Regente Alfa',
            'email' => 'regente@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Droguería La Rebaja Beta',
            'nit' => '900777888-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Droguería Beta Centro',
            'codigo' => 'DROG-BETA',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'Regente Beta',
            'email' => 'regente@beta.test',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($this->empresaA);
        BranchContext::setId($this->sucursalA->id);

        $this->medicamentoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Amoxicilina 500mg Cápsulas',
            'codigo' => 'MED-AMOX-500',
            'precio_compra' => 12000.00,
            'precio_venta' => 22000.00,
            'stock' => 100,
            'stock_minimo' => 10,
            'registro_sanitario' => 'INVIMA 2021M-001234',
            'requiere_receta' => true,
            'maneja_lotes' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
    }

    public function test_admin_puede_crear_laboratorio_y_principio_activo(): void
    {
        $responseLab = $this->actingAs($this->adminA)
            ->post(route('laboratorios.store'), [
                'nombre' => 'Laboratorios Genfar',
                'codigo' => 'GEN-01',
                'telefono' => '3109998877',
                'estado' => EstadoGeneral::ACTIVO->value,
            ]);

        $responseLab->assertRedirect(route('laboratorios.index'));
        $this->assertDatabaseHas('laboratorios', [
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Laboratorios Genfar',
        ]);

        $responsePA = $this->actingAs($this->adminA)
            ->post(route('principios-activos.store'), [
                'nombre' => 'Amoxicilina Trihidrato',
                'concentracion' => '500 mg',
                'descripcion' => 'Antibiótico betalactámico bactericida',
                'estado' => EstadoGeneral::ACTIVO->value,
            ]);

        $responsePA->assertRedirect(route('principios-activos.index'));
        $this->assertDatabaseHas('principios_activos', [
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Amoxicilina Trihidrato',
            'concentracion' => '500 mg',
        ]);
    }

    public function test_aislamiento_multiempresa_en_laboratorios(): void
    {
        Laboratorio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Laboratorio Secreto Alfa',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminB)
            ->get(route('laboratorios.index'));

        $response->assertOk();
        $response->assertDontSee('Laboratorio Secreto Alfa');
    }

    public function test_crear_lote_calcula_estado_segun_vencimiento(): void
    {
        // Lote vigente > 30 días
        $loteVigente = ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-2026-VIG',
            'fecha_fabricacion' => now()->subMonth(),
            'fecha_vencimiento' => now()->addMonths(6),
            'stock_inicial' => 50,
            'stock_actual' => 50,
            'costo_unitario' => 12000,
        ]);
        $loteVigente->actualizarEstadoAutomatico();
        $this->assertEquals(EstadoLote::DISPONIBLE, $loteVigente->estado);

        // Lote próximo a vencer (< 30 días)
        $loteProximo = ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-2026-PROX',
            'fecha_fabricacion' => now()->subYear(),
            'fecha_vencimiento' => now()->addDays(15),
            'stock_inicial' => 20,
            'stock_actual' => 20,
            'costo_unitario' => 12000,
        ]);
        $loteProximo->actualizarEstadoAutomatico();
        $this->assertEquals(EstadoLote::PROXIMO_VENCER, $loteProximo->estado);

        // Lote vencido
        $loteVencido = ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-2026-VENC',
            'fecha_fabricacion' => now()->subYears(2),
            'fecha_vencimiento' => now()->subDays(5),
            'stock_inicial' => 10,
            'stock_actual' => 10,
            'costo_unitario' => 12000,
        ]);
        $loteVencido->actualizarEstadoAutomatico();
        $this->assertEquals(EstadoLote::VENCIDO, $loteVencido->estado);
    }

    public function test_descontar_stock_del_lote_y_agotamiento(): void
    {
        $lote = ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-2026-DISP',
            'fecha_vencimiento' => now()->addYear(),
            'stock_inicial' => 10,
            'stock_actual' => 10,
            'costo_unitario' => 12000,
        ]);

        $lote->descontarStock(4);
        $this->assertEquals(6, $lote->fresh()->stock_actual);

        $lote->descontarStock(6);
        $this->assertEquals(0, $lote->fresh()->stock_actual);
        $this->assertEquals(EstadoLote::AGOTADO, $lote->fresh()->estado);

        // Falla si se intenta descontar más de lo disponible
        $this->expectException(\InvalidArgumentException::class);
        $lote->descontarStock(1);
    }

    public function test_servicio_alertas_y_notificaciones_farmacia(): void
    {
        ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-ALERTA-VENC',
            'fecha_vencimiento' => now()->subDays(2),
            'stock_inicial' => 15,
            'stock_actual' => 15,
            'costo_unitario' => 12000,
        ]);

        $service = app(FarmaciaAlertasService::class);
        $vencidos = $service->obtenerLotesVencidos($this->empresaA->id);
        $this->assertCount(1, $vencidos);
        $this->assertEquals('LOT-ALERTA-VENC', $vencidos->first()->numero_lote);

        $notificaciones = $service->sincronizarNotificacionesFarmacia($this->empresaA->id);
        $this->assertGreaterThanOrEqual(1, $notificaciones);

        $this->assertDatabaseHas('notificaciones_sistema', [
            'empresa_id' => $this->empresaA->id,
            'titulo' => 'Medicamento Vencido',
        ]);
    }

    public function test_dashboard_farmacia_se_renderiza_correctamente(): void
    {
        ProductoLote::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->medicamentoA->id,
            'numero_lote' => 'LOT-DASH-01',
            'fecha_vencimiento' => now()->addDays(10),
            'stock_inicial' => 30,
            'stock_actual' => 30,
            'costo_unitario' => 12000,
        ]);

        $response = $this->actingAs($this->adminA)
            ->get(route('farmacia.dashboard'));

        $response->assertOk();
        $response->assertSee('Módulo Farmacéutico');
        $response->assertSee('Amoxicilina 500mg Cápsulas');
    }
}
