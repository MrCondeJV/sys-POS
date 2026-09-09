<?php

namespace Tests\Feature\Fase3;

use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MultiempresaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        BranchContext::clear();
        parent::tearDown();
    }

    public function test_inyeccion_de_empresa_id_desde_el_frontend_es_ignorada_al_crear_sucursal(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresaA);

        $adminA = User::factory()->create(['empresa_id' => $empresaA->id]);
        setPermissionsTeamId($empresaA->id);
        $adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->actingAs($adminA);

        // El frontend malicioso intenta enviar empresa_id de Empresa B
        $response = $this->post('/sucursales', [
            'empresa_id' => $empresaB->id,
            'nombre' => 'Sucursal Inyectada',
            'codigo' => 'SUC-HACK',
            'ciudad' => 'Bogotá',
        ]);

        $response->assertRedirect('/sucursales');

        // La sucursal debe pertenecer a Empresa A, NUNCA a Empresa B
        $sucursalCreada = Sucursal::withoutGlobalScopes()->where('codigo', 'SUC-HACK')->first();
        $this->assertNotNull($sucursalCreada);
        $this->assertEquals($empresaA->id, $sucursalCreada->empresa_id);
        $this->assertNotEquals($empresaB->id, $sucursalCreada->empresa_id);
    }

    public function test_usuario_empresa_a_no_puede_modificar_sucursal_de_empresa_b(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresaA);

        $adminA = User::factory()->create(['empresa_id' => $empresaA->id]);
        setPermissionsTeamId($empresaA->id);
        $adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $sucursalB = Sucursal::factory()->create([
            'empresa_id' => $empresaB->id,
            'nombre' => 'Sede Original B',
        ]);

        $this->actingAs($adminA);

        $response = $this->put("/sucursales/{$sucursalB->id}", [
            'nombre' => 'Nombre Modificado Ilegalmente',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('sucursales', [
            'id' => $sucursalB->id,
            'nombre' => 'Sede Original B',
        ]);
    }

    public function test_usuario_empresa_a_no_puede_eliminar_sucursal_de_empresa_b(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresaA);

        $adminA = User::factory()->create(['empresa_id' => $empresaA->id]);
        setPermissionsTeamId($empresaA->id);
        $adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $sucursalB = Sucursal::factory()->create([
            'empresa_id' => $empresaB->id,
            'es_principal' => false,
        ]);

        $this->actingAs($adminA);

        $response = $this->delete("/sucursales/{$sucursalB->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('sucursales', ['id' => $sucursalB->id]);
    }

    public function test_regla_belongs_to_active_company_valida_apropiadamente(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $sucursalA = Sucursal::factory()->create(['empresa_id' => $empresaA->id]);
        $sucursalB = Sucursal::factory()->create(['empresa_id' => $empresaB->id]);

        CompanyContext::setCompanyId($empresaA->id);

        $rule = new BelongsToActiveCompany('sucursales');

        // Validar sucursal que sí pertenece a la empresa
        $validatorA = Validator::make(['sucursal_id' => $sucursalA->id], ['sucursal_id' => [$rule]]);
        $this->assertTrue($validatorA->passes());

        // Validar sucursal que pertenece a OTRA empresa (debe fallar)
        $validatorB = Validator::make(['sucursal_id' => $sucursalB->id], ['sucursal_id' => [$rule]]);
        $this->assertTrue($validatorB->fails());
    }

    public function test_usuario_no_puede_seleccionar_como_activa_una_sucursal_de_otra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        $userA = User::factory()->create(['empresa_id' => $empresaA->id]);
        $sucursalB = Sucursal::factory()->create(['empresa_id' => $empresaB->id]);

        $this->actingAs($userA);

        $response = $this->post('/sucursales/seleccionar', [
            'sucursal_id' => $sucursalB->id,
        ]);

        $response->assertSessionHasErrors('sucursal_id');
        $this->assertNotEquals($sucursalB->id, BranchContext::getId());
    }

    public function test_vistas_responsivas_se_renderizan_con_status_200(): void
    {
        $empresa = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);

        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        setPermissionsTeamId($empresa->id);
        $user->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->actingAs($user);

        // 1. Dashboard
        $responseDashboard = $this->get('/dashboard');
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertSee($empresa->nombre_comercial);

        // 2. Perfil de Empresa
        $responsePerfil = $this->get('/empresa/perfil');
        $responsePerfil->assertStatus(200);
        $responsePerfil->assertSee('Identificación Tributaria');

        // 3. Listado de Sucursales
        $responseSucursales = $this->get('/sucursales');
        $responseSucursales->assertStatus(200);
        $responseSucursales->assertSee('Sucursales y Puntos de Venta');
    }
}
