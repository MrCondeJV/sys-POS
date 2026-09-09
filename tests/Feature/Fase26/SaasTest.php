<?php

namespace Tests\Feature\Fase26;

use App\Enums\EstadoGeneral;
use App\Enums\EstadoSuscripcion;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\Sucursal;
use App\Models\Suscripcion;
use App\Models\User;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected User $adminA;
    protected User $adminB;
    protected Plan $planBasico;
    protected Plan $planProfesional;
    protected Plan $planEmpresarial;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear planes SaaS de prueba
        $this->planBasico = Plan::create([
            'nombre' => 'Plan Básico',
            'slug' => 'basico',
            'descripcion' => 'Para pequeños negocios y tiendas',
            'precio_mensual' => 49000,
            'precio_anual' => 490000,
            'limite_sucursales' => 1,
            'limite_usuarios' => 2,
            'permite_facturacion_electronica' => false,
            'permite_api' => false,
            'activo' => true,
        ]);

        $this->planProfesional = Plan::create([
            'nombre' => 'Plan Profesional',
            'slug' => 'profesional',
            'descripcion' => 'Para comercios en expansión',
            'precio_mensual' => 99000,
            'precio_anual' => 990000,
            'limite_sucursales' => 3,
            'limite_usuarios' => 10,
            'permite_facturacion_electronica' => true,
            'permite_api' => false,
            'activo' => true,
        ]);

        $this->planEmpresarial = Plan::create([
            'nombre' => 'Plan Empresarial',
            'slug' => 'empresarial',
            'descripcion' => 'Cadenas y empresas con alta transaccionalidad',
            'precio_mensual' => 199000,
            'precio_anual' => 1990000,
            'limite_sucursales' => 0, // Ilimitado
            'limite_usuarios' => 0,   // Ilimitado
            'permite_facturacion_electronica' => true,
            'permite_api' => true,
            'activo' => true,
        ]);

        // Empresa A con Plan Básico
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Supertienda A SAS',
            'nit' => '900555666-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Principal A',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Suscripcion::create([
            'empresa_id' => $this->empresaA->id,
            'plan_id' => $this->planBasico->id,
            'estado' => EstadoSuscripcion::ACTIVA,
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addDays(30),
            'ciclo_facturacion' => 'MENSUAL',
            'precio_pago' => 49000,
        ]);

        // Empresa B con Plan Empresarial
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Mega Retail B SAS',
            'nit' => '900777888-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        setPermissionsTeamId($this->empresaA->id);
        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        setPermissionsTeamId($this->empresaB->id);
        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        setPermissionsTeamId($this->empresaA->id);
        CompanyContext::setCompanyId($this->empresaA->id);
        BranchContext::setId($this->sucursalA->id);
    }

    public function test_empresa_con_plan_basico_no_puede_superar_limite_de_sucursales(): void
    {
        // Empresa A ya tiene 1 sucursal (sedePrincipal). El Plan Básico permite máximo 1.
        $response = $this->actingAs($this->adminA)->post(route('sucursales.store'), [
            'nombre' => 'Segunda Sucursal Intento',
            'codigo' => 'SUC-02',
        ]);

        $response->assertSessionHasErrors('general');
        $this->assertDatabaseMissing('sucursales', [
            'nombre' => 'Segunda Sucursal Intento',
        ]);
    }

    public function test_empresa_puede_mejorar_a_plan_profesional_y_ampliar_limites(): void
    {
        // 1. Cambiar a Plan Profesional (permite 3 sucursales)
        $responseUpgrade = $this->actingAs($this->adminA)->post(route('saas.cambiar-plan'), [
            'plan_id' => $this->planProfesional->id,
            'ciclo' => 'MENSUAL',
        ]);

        $responseUpgrade->assertRedirect(route('saas.suscripcion'));
        $responseUpgrade->assertSessionHas('success');

        $this->empresaA->refresh();
        $this->assertEquals($this->planProfesional->id, $this->empresaA->obtenerPlan()->id);

        // 2. Ahora sí puede crear la segunda sucursal
        $responseCrear = $this->actingAs($this->adminA)->post(route('sucursales.store'), [
            'nombre' => 'Segunda Sucursal Exitosa',
            'codigo' => 'SUC-02',
        ]);

        $responseCrear->assertRedirect(route('sucursales.index'));
        $this->assertDatabaseHas('sucursales', [
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Segunda Sucursal Exitosa',
        ]);
    }

    public function test_verificacion_de_caracteristicas_segun_plan(): void
    {
        // En Plan Básico: facturacion electronica = false, api = false
        $this->assertFalse($this->empresaA->puedeUsarFacturacionElectronica());
        $this->assertFalse($this->empresaA->puedeUsarApi());

        // Actualizar a Empresarial
        $this->empresaA->suscripciones()->create([
            'plan_id' => $this->planEmpresarial->id,
            'estado' => EstadoSuscripcion::ACTIVA,
            'fecha_inicio' => now(),
            'ciclo_facturacion' => 'ANUAL',
            'precio_pago' => 1990000,
        ]);

        $this->empresaA->refresh();
        $this->assertTrue($this->empresaA->puedeUsarFacturacionElectronica());
        $this->assertTrue($this->empresaA->puedeUsarApi());
        $this->assertTrue($this->empresaA->puedeCrearSucursal());
        $this->assertTrue($this->empresaA->puedeCrearUsuario());
    }

    public function test_vistas_saas_se_renderizan_correctamente(): void
    {
        $responseSub = $this->actingAs($this->adminA)->get(route('saas.suscripcion'));
        $responseSub->assertStatus(200);
        $responseSub->assertSee('Mi Suscripción y Límites');
        $responseSub->assertSee('Plan Básico');

        $responsePlanes = $this->actingAs($this->adminA)->get(route('saas.planes'));
        $responsePlanes->assertStatus(200);
        $responsePlanes->assertSee('Planes y Precios Comerciales');
        $responsePlanes->assertSee('Plan Profesional');
        $responsePlanes->assertSee('Plan Empresarial');
    }
}
