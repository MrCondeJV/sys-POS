<?php

namespace Tests\Feature\Fase2;

use App\Enums\PermisoSistema;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_asignacion_de_rol_cajero_y_verificacion_de_permisos(): void
    {
        $empresa = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);

        $cajero = User::factory()->create(['empresa_id' => $empresa->id]);
        setPermissionsTeamId($empresa->id);
        $cajero->assignRole(RolSistema::CAJERO->value);

        // Debe tener permisos de cajero
        $this->assertTrue($cajero->can(PermisoSistema::VENTAS_CREAR->value));
        $this->assertTrue($cajero->can(PermisoSistema::CAJA_ABRIR->value));
        $this->assertTrue($cajero->can(PermisoSistema::CAJA_CERRAR->value));

        // NO debe tener permisos de administración
        $this->assertFalse($cajero->can(PermisoSistema::EMPRESA_GESTIONAR->value));
        $this->assertFalse($cajero->can(PermisoSistema::PRODUCTOS_ELIMINAR->value));
    }

    public function test_super_admin_tiene_acceso_total_mediante_gate(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        setPermissionsTeamId(null);
        $superAdmin->assignRole(RolSistema::SUPER_ADMIN->value);

        $empresaCualquiera = Empresa::factory()->create();

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->actingAs($superAdmin);

        // Super Admin puede ver y editar cualquier empresa mediante Gate::before
        $this->assertTrue(Gate::allows('view', $empresaCualquiera));
        $this->assertTrue(Gate::allows('update', $empresaCualquiera));
    }

    public function test_roles_estan_aislados_por_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresaB);

        $user = User::factory()->create(['empresa_id' => $empresaA->id]);

        // Asignar rol Admin en Empresa A
        setPermissionsTeamId($empresaA->id);
        $user->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->assertTrue($user->hasRole(RolSistema::ADMIN_EMPRESA->value));

        // En contexto de Empresa B no debe tener el rol
        setPermissionsTeamId($empresaB->id);
        $user->unsetRelation('roles');
        $this->assertFalse($user->hasRole(RolSistema::ADMIN_EMPRESA->value));
    }
}
