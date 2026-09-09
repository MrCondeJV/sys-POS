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

class MenuPermisosTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected Sucursal $sucursal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create();
        $this->sucursal = Sucursal::factory()->create(['empresa_id' => $this->empresa->id]);
        CompanyContext::setCompany($this->empresa);

        // Sembrar roles y permisos
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_admin_empresa_ve_todos_los_modulos_en_el_menu(): void
    {
        setPermissionsTeamId($this->empresa->id);

        $admin = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('href="'.route('empresa.perfil').'"', false);
        $response->assertSee('href="'.route('sucursales.index').'"', false);
        $response->assertSee('href="'.route('productos.index').'"', false);
        $response->assertSee('href="'.route('inventario.index').'"', false);
        $response->assertSee('href="'.route('compras.index').'"', false);
        $response->assertSee('href="'.route('proveedores.index').'"', false);
        $response->assertSee('href="'.route('ventas.index').'"', false);
        $response->assertSee('href="'.route('cajas.index').'"', false);
        $response->assertSee('href="'.route('pos.index').'"', false);
        $response->assertSee('href="'.route('reportes.index').'"', false);
        $response->assertSee('href="'.route('auditoria.index').'"', false);
        $response->assertSee('href="'.route('impuestos.index').'"', false);
    }

    public function test_cajero_solo_ve_modulos_permitidos_y_no_ve_modulos_administrativos(): void
    {
        setPermissionsTeamId($this->empresa->id);

        $cajero = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $cajero->assignRole(RolSistema::CAJERO->value);

        $response = $this->actingAs($cajero)->get('/dashboard');

        $response->assertStatus(200);

        // Módulos que el cajero DEBE ver
        $response->assertSee('href="'.route('pos.index').'"', false);
        $response->assertSee('href="'.route('cajas.index').'"', false);
        $response->assertSee('href="'.route('productos.index').'"', false);
        $response->assertSee('href="'.route('clientes.index').'"', false);

        // Módulos que el cajero NO DEBE ver en el menú ni accesos directos
        $response->assertDontSee('href="'.route('empresa.perfil').'"', false);
        $response->assertDontSee('href="'.route('sucursales.index').'"', false);
        $response->assertDontSee('href="'.route('compras.index').'"', false);
        $response->assertDontSee('href="'.route('proveedores.index').'"', false);
        $response->assertDontSee('href="'.route('auditoria.index').'"', false);
        $response->assertDontSee('href="'.route('impuestos.index').'"', false);
        $response->assertDontSee('Gestión Empresarial');
        $response->assertDontSee('Compras & Proveedores');
    }

    public function test_vendedor_no_ve_cajas_ni_compras_ni_gestion_empresarial(): void
    {
        setPermissionsTeamId($this->empresa->id);

        $vendedor = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $vendedor->assignRole(RolSistema::VENDEDOR->value);

        $response = $this->actingAs($vendedor)->get('/dashboard');

        $response->assertStatus(200);

        // Vendedor ve POS, ventas, productos y clientes
        $response->assertSee('href="'.route('pos.index').'"', false);
        $response->assertSee('href="'.route('ventas.index').'"', false);
        $response->assertSee('href="'.route('productos.index').'"', false);
        $response->assertSee('href="'.route('clientes.index').'"', false);

        // Vendedor NO ve cajas, compras ni gestión empresarial
        $response->assertDontSee('href="'.route('cajas.index').'"', false);
        $response->assertDontSee('href="'.route('compras.index').'"', false);
        $response->assertDontSee('href="'.route('empresa.perfil').'"', false);
        $response->assertDontSee('href="'.route('sucursales.index').'"', false);
        $response->assertDontSee('href="'.route('auditoria.index').'"', false);
        $response->assertDontSee('href="'.route('impuestos.index').'"', false);
        $response->assertDontSee('Gestión Empresarial');
        $response->assertDontSee('Compras & Proveedores');
    }

    public function test_contador_no_ve_pos_ni_cajas_ni_gestion_empresarial(): void
    {
        setPermissionsTeamId($this->empresa->id);

        $contador = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $contador->assignRole(RolSistema::CONTADOR->value);

        $response = $this->actingAs($contador)->get('/dashboard');

        $response->assertStatus(200);

        // Contador ve compras, reportes, impuestos y auditoría
        $response->assertSee('href="'.route('compras.index').'"', false);
        $response->assertSee('href="'.route('reportes.index').'"', false);
        $response->assertSee('href="'.route('impuestos.index').'"', false);
        $response->assertSee('href="'.route('auditoria.index').'"', false);

        // Contador NO ve POS, Cajas, ni configuración de empresa
        $response->assertDontSee('href="'.route('pos.index').'"', false);
        $response->assertDontSee('href="'.route('cajas.index').'"', false);
        $response->assertDontSee('href="'.route('empresa.perfil').'"', false);
        $response->assertDontSee('href="'.route('sucursales.index').'"', false);
        $response->assertDontSee('Operaciones & Caja');
        $response->assertDontSee('Gestión Empresarial');
    }
}
