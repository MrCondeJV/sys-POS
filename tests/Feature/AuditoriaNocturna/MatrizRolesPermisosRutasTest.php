<?php

namespace Tests\Feature\AuditoriaNocturna;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class MatrizRolesPermisosRutasTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected Sucursal $sucursal;

    protected User $superAdmin;

    protected User $adminEmpresa;

    protected User $adminSucursal;

    protected User $cajero;

    protected User $vendedor;

    protected User $contador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresa = Empresa::factory()->create([
            'nombre_comercial' => 'Matriz Roles S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);

        $this->sucursal = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->superAdmin = User::factory()->create([
            'empresa_id' => null,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId(null);
        $this->superAdmin->assignRole(RolSistema::SUPER_ADMIN->value);

        setPermissionsTeamId($this->empresa->id);

        $this->adminEmpresa = $this->createRoleUser(RolSistema::ADMIN_EMPRESA);
        $this->adminSucursal = $this->createRoleUser(RolSistema::ADMIN_SUCURSAL);
        $this->cajero = $this->createRoleUser(RolSistema::CAJERO);
        $this->vendedor = $this->createRoleUser(RolSistema::VENDEDOR);
        $this->contador = $this->createRoleUser(RolSistema::CONTADOR);

        CompanyContext::clear();
    }

    protected function createRoleUser(RolSistema $rol): User
    {
        $user = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresa->id);
        $user->assignRole($rol->value);

        return $user;
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_invitado_no_autenticado_es_redirigido_a_login_en_todas_las_rutas_protegidas(): void
    {
        $rutasProtegidas = [
            route('dashboard'),
            route('empresas.index'),
            route('empresa.perfil'),
            route('usuarios.index'),
            route('sucursales.index'),
            route('productos.index'),
            route('catalogos.index'),
            route('inventario.index'),
            route('proveedores.index'),
            route('compras.index'),
            route('clientes.index'),
            route('cartera.index'),
            route('cajas.index'),
            route('ventas.index'),
            route('pos.index'),
            route('listas-precios.index'),
            route('devoluciones.index'),
            route('reportes.index'),
            route('auditoria.index'),
            route('impuestos.index'),
            route('documentos.index'),
            route('notificaciones.index'),
            route('facturacion-electronica.index'),
            route('traslados.index'),
        ];

        foreach ($rutasProtegidas as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_matriz_super_admin_con_y_sin_contexto_de_empresa(): void
    {
        // Sin contexto de empresa: accede a rutas de plataforma y /dashboard redirige a empresas.index
        $this->actingAs($this->superAdmin)->get(route('empresas.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('usuarios.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('auditoria.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('dashboard'))->assertRedirect(route('empresas.index'));

        // Sin contexto de empresa: redirige a empresas.index al intentar rutas de negocio
        $this->actingAs($this->superAdmin)
            ->get(route('productos.index'))
            ->assertRedirect(route('empresas.index'));

        // Entrar a la empresa
        $this->actingAs($this->superAdmin)
            ->post(route('empresas.seleccionar'), ['empresa_id' => $this->empresa->id])
            ->assertRedirect(route('dashboard'));

        // Con contexto activo: opera rutas del negocio
        $this->actingAs($this->superAdmin)->get(route('productos.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('cajas.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('ventas.index'))->assertOk();
    }

    public function test_matriz_admin_empresa_accede_a_todo_su_tenant_pero_no_a_empresas_saas(): void
    {
        // Bloqueado en gestión global de empresas SaaS (redirige a dashboard o 403)
        $resIndex = $this->actingAs($this->adminEmpresa)->get(route('empresas.index'));
        $this->assertTrue(in_array($resIndex->getStatusCode(), [302, 403], true));
        $resCreate = $this->actingAs($this->adminEmpresa)->get(route('empresas.create'));
        $this->assertTrue(in_array($resCreate->getStatusCode(), [302, 403], true));

        // Permitido en todos los módulos de su empresa
        $permitidas = [
            'dashboard',
            'empresa.perfil',
            'usuarios.index',
            'sucursales.index',
            'productos.index',
            'catalogos.index',
            'inventario.index',
            'proveedores.index',
            'compras.index',
            'clientes.index',
            'cartera.index',
            'cajas.index',
            'ventas.index',
            'pos.index',
            'listas-precios.index',
            'devoluciones.index',
            'reportes.index',
            'auditoria.index',
            'impuestos.index',
            'documentos.index',
        ];

        foreach ($permitidas as $routeName) {
            $res = $this->actingAs($this->adminEmpresa)->get(route($routeName));
            $this->assertEquals(200, $res->getStatusCode(), "ADMIN_EMPRESA falló en ruta permitida [{$routeName}]");
        }
    }

    public function test_matriz_cajero_permisos_y_restricciones(): void
    {
        $permitidas = [
            'dashboard',
            'pos.index',
            'cajas.index',
            'ventas.index',
            'clientes.index',
            'cartera.index',
            'productos.index',
            'inventario.index',
            'listas-precios.index',
            'documentos.index',
        ];

        foreach ($permitidas as $routeName) {
            $res = $this->actingAs($this->cajero)->get(route($routeName));
            $this->assertEquals(200, $res->getStatusCode(), "CAJERO debería acceder a [{$routeName}]");
        }

        $prohibidas = [
            'empresas.index',
            'usuarios.index',
            'compras.index',
            'proveedores.index',
            'reportes.index',
            'auditoria.index',
            'impuestos.index',
        ];

        foreach ($prohibidas as $routeName) {
            $res = $this->actingAs($this->cajero)->get(route($routeName));
            $expected = $routeName === 'empresas.index' ? [302, 403] : [403];
            $this->assertTrue(in_array($res->getStatusCode(), $expected, true), "CAJERO NO debería acceder a [{$routeName}] (obtuvo {$res->getStatusCode()})");
        }

        // Mutaciones administrativas sí bloquean con 403
        $this->actingAs($this->cajero)->put(route('empresa.perfil.update'), [])->assertForbidden();
        $this->actingAs($this->cajero)->post(route('sucursales.store'), [])->assertForbidden();
    }

    public function test_matriz_vendedor_permisos_y_restricciones(): void
    {
        $permitidas = [
            'dashboard',
            'ventas.index',
            'clientes.index',
            'productos.index',
            'cartera.index',
            'documentos.index',
        ];

        foreach ($permitidas as $routeName) {
            $res = $this->actingAs($this->vendedor)->get(route($routeName));
            $this->assertEquals(200, $res->getStatusCode(), "VENDEDOR debería acceder a [{$routeName}]");
        }

        $prohibidas = [
            'empresas.index',
            'usuarios.index',
            'cajas.index',
            'compras.index',
            'proveedores.index',
            'reportes.index',
            'auditoria.index',
            'impuestos.index',
        ];

        foreach ($prohibidas as $routeName) {
            $res = $this->actingAs($this->vendedor)->get(route($routeName));
            $expected = $routeName === 'empresas.index' ? [302, 403] : [403];
            $this->assertTrue(in_array($res->getStatusCode(), $expected, true), "VENDEDOR NO debería acceder a [{$routeName}] (obtuvo {$res->getStatusCode()})");
        }

        $this->actingAs($this->vendedor)->put(route('empresa.perfil.update'), [])->assertForbidden();
        $this->actingAs($this->vendedor)->post(route('sucursales.store'), [])->assertForbidden();
    }

    public function test_matriz_contador_permisos_y_restricciones(): void
    {
        $permitidas = [
            'dashboard',
            'reportes.index',
            'auditoria.index',
            'ventas.index',
            'compras.index',
            'proveedores.index',
            'clientes.index',
            'cartera.index',
            'inventario.index',
            'documentos.index',
            'impuestos.index',
        ];

        foreach ($permitidas as $routeName) {
            $res = $this->actingAs($this->contador)->get(route($routeName));
            $this->assertEquals(200, $res->getStatusCode(), "CONTADOR debería acceder a [{$routeName}]");
        }

        $prohibidas = [
            'empresas.index',
            'usuarios.index',
            'cajas.index',
            'pos.index',
        ];

        foreach ($prohibidas as $routeName) {
            $res = $this->actingAs($this->contador)->get(route($routeName));
            $expected = $routeName === 'empresas.index' ? [302, 403] : [403];
            $this->assertTrue(in_array($res->getStatusCode(), $expected, true), "CONTADOR NO debería acceder a [{$routeName}] (obtuvo {$res->getStatusCode()})");
        }

        $this->actingAs($this->contador)->put(route('empresa.perfil.update'), [])->assertForbidden();
        $this->actingAs($this->contador)->post(route('sucursales.store'), [])->assertForbidden();
    }

    public function test_get_empresa_perfil_y_sucursales_index_deberian_bloquear_roles_sin_permiso(): void
    {
        $resPerfil = $this->actingAs($this->cajero)->get(route('empresa.perfil'));
        $resSucursales = $this->actingAs($this->cajero)->get(route('sucursales.index'));

        $resPerfil->assertForbidden();
        $resSucursales->assertForbidden();
    }
}
