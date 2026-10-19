<?php

namespace Tests\Feature\Seguridad;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Sucursal $sucursal;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear una empresa de prueba y sucursal primero
        $this->empresa = Empresa::factory()->create([
            'nombre_comercial' => 'Comercio Alfa',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursal = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sucursal Principal Alfa',
            'es_principal' => true,
        ]);

        // Sembrar roles y permisos base
        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear Super Admin global (sin empresa_id)
        $this->superAdmin = User::factory()->superAdmin()->create([
            'email' => 'superadmin@pos.com',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId(null);
        $this->superAdmin->assignRole(RolSistema::SUPER_ADMIN->value);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_super_admin_por_defecto_tiene_navbar_limpio_sin_modulos_operativos(): void
    {
        // Al acceder a /dashboard sin negocio activo, debe redirigir a /empresas
        $response = $this->actingAs($this->superAdmin)->get('/dashboard');
        $response->assertRedirect(route('empresas.index'));

        // En /empresas el navbar debe estar limpio
        $responseIndex = $this->actingAs($this->superAdmin)->get('/empresas');
        $responseIndex->assertStatus(200);

        // Debe ver las opciones de administración SaaS
        $responseIndex->assertSee('href="'.route('empresas.index').'"', false);
        $responseIndex->assertSee('href="'.route('usuarios.index').'"', false);
        $responseIndex->assertSee('href="'.route('auditoria.index').'"', false);
        $responseIndex->assertSee('Plataforma SaaS');

        // NO debe ver módulos operativos de un comercio en el navbar
        $responseIndex->assertDontSee('href="'.route('productos.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('pos.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('cajas.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('ventas.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('compras.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('inventario.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('facturacion-electronica.index').'"', false);
        $responseIndex->assertDontSee('Gestión Empresarial');
        $responseIndex->assertDontSee('Inventario & Productos');
        $responseIndex->assertDontSee('Operaciones & Caja');

        // Debe ver el botón para "Entrar al Negocio"
        $responseIndex->assertSee('Entrar al Negocio');
    }

    public function test_super_admin_puede_entrar_opcionalmente_a_un_negocio_y_ver_sus_modulos(): void
    {
        // El Super Admin entra explícitamente a un negocio desde el panel
        $response = $this->actingAs($this->superAdmin)->post(route('empresas.seleccionar'), [
            'empresa_id' => $this->empresa->id,
        ]);

        // Debe redirigir al dashboard del negocio
        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($this->empresa->id, session('superadmin_empresa_id'));

        // Al consultar el dashboard en Modo Soporte
        $responseDashboard = $this->actingAs($this->superAdmin)->get('/dashboard');
        $responseDashboard->assertStatus(200);

        // Debe mostrar el banner de Modo Soporte
        $responseDashboard->assertSee('MODO SOPORTE SUPER ADMIN');
        $responseDashboard->assertSee('Comercio Alfa');
        $responseDashboard->assertSee('Salir al Panel Super Admin');
        $responseDashboard->assertSee('Salir del Negocio');

        // Ahora SÍ debe ver los módulos operativos del negocio
        $responseDashboard->assertSee('href="'.route('productos.index').'"', false);
        $responseDashboard->assertSee('href="'.route('pos.index').'"', false);
        $responseDashboard->assertSee('href="'.route('cajas.index').'"', false);
        $responseDashboard->assertSee('href="'.route('ventas.index').'"', false);
        $responseDashboard->assertSee('href="'.route('compras.index').'"', false);
    }

    public function test_super_admin_puede_salir_del_negocio_y_retorna_al_panel_super_admin_limpio(): void
    {
        // Simular que el Super Admin está dentro de un negocio
        session(['superadmin_empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($this->superAdmin)->post(route('empresas.salir'));

        // Debe redirigir a /empresas
        $response->assertRedirect(route('empresas.index'));
        $this->assertNull(session('superadmin_empresa_id'));

        // El panel de empresas vuelve a mostrar el menú limpio
        $responseIndex = $this->actingAs($this->superAdmin)->get('/empresas');
        $responseIndex->assertStatus(200);
        $responseIndex->assertDontSee('href="'.route('pos.index').'"', false);
        $responseIndex->assertDontSee('href="'.route('cajas.index').'"', false);
        $responseIndex->assertDontSee('MODO SOPORTE SUPER ADMIN');
    }

    public function test_super_admin_sin_negocio_redirige_si_intenta_acceder_a_rutas_operativas_directamente(): void
    {
        // Sin negocio en sesión, intentar entrar al POS
        $responsePos = $this->actingAs($this->superAdmin)->get(route('pos.index'));
        $responsePos->assertRedirect(route('empresas.index'));
        $responsePos->assertSessionHas('warning');

        // Intentar entrar a catálogo de productos
        $responseProd = $this->actingAs($this->superAdmin)->get(route('productos.index'));
        $responseProd->assertRedirect(route('empresas.index'));
        $responseProd->assertSessionHas('warning');

        // Intentar entrar a cajas
        $responseCajas = $this->actingAs($this->superAdmin)->get(route('cajas.index'));
        $responseCajas->assertRedirect(route('empresas.index'));
        $responseCajas->assertSessionHas('warning');
    }

    public function test_usuario_regular_de_empresa_mantiene_acceso_a_sus_modulos_normalmente(): void
    {
        setPermissionsTeamId($this->empresa->id);

        $adminEmpresa = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $adminEmpresa->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $response = $this->actingAs($adminEmpresa)->get('/dashboard');
        $response->assertStatus(200);

        // Debe ver sus módulos normales
        $response->assertSee('href="'.route('productos.index').'"', false);
        $response->assertSee('href="'.route('pos.index').'"', false);
        $response->assertSee('href="'.route('cajas.index').'"', false);

        // NO debe ver las opciones exclusivas de Super Admin SaaS
        $response->assertDontSee('Plataforma SaaS');
        $response->assertDontSee('href="'.route('empresas.index').'"', false);
    }
}
