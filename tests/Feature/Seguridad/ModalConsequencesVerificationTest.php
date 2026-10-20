<?php

namespace Tests\Feature\Seguridad;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalConsequencesVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    /**
     * 4(a): Inactivar un usuario con sesión abierta lo expulsa en la siguiente petición HTTP.
     */
    public function test_inactivar_usuario_con_sesion_abierta_lo_expulsa_en_siguiente_peticion(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $cajero = User::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($empresa->id);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);
        $cajero->assignRole(RolSistema::CAJERO->value);

        // 1. El cajero tiene sesión abierta y opera normalmente
        $this->actingAs($cajero)->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($cajero);

        // 2. El administrador inactiva al cajero mediante toggle-estado
        $this->actingAs($admin)
            ->post(route('usuarios.toggle-estado', $cajero))
            ->assertRedirect();

        $this->assertEquals(EstadoGeneral::INACTIVO, $cajero->fresh()->estado);

        // 3. En la siguiente petición del cajero, SetCompanyContext cierra su sesión y redirige a login
        $response = $this->actingAs($cajero->fresh())->get(route('dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * 4(b): Evidencia del comportamiento actual cuando una empresa está en estado INACTIVO:
     * Sus usuarios activos AÚN pueden iniciar sesión y operar (Bug de Severidad Alta reportado).
     */
    public function test_evidencia_usuario_de_empresa_inactiva_aun_puede_iniciar_sesion_y_operar(): void
    {
        $empresaInactiva = Empresa::factory()->create([
            'estado' => EstadoGeneral::INACTIVO,
        ]);
        Sucursal::factory()->create([
            'empresa_id' => $empresaInactiva->id,
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->seed(RolesAndPermissionsSeeder::class);

        $usuario = User::factory()->create([
            'email' => 'operador@empresainactiva.com',
            'password' => 'secret1234',
            'empresa_id' => $empresaInactiva->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($empresaInactiva->id);
        $usuario->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Login exitoso a pesar de que la empresa está INACTIVO
        $loginResponse = $this->post('/login', [
            'email' => 'operador@empresainactiva.com',
            'password' => 'secret1234',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        // Puede operar en /dashboard con respuesta 200
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertOk();
    }

    /**
     * 4(c): Evidencia del comportamiento actual al crear un usuario con el mismo email
     * de un usuario eliminado con soft delete:
     * La validación Rule::unique('users', 'email')->whereNull('deleted_at') pasa,
     * pero el índice UNIQUE físico de la tabla `users.email` lanza UniqueConstraintViolationException (500).
     */
    public function test_evidencia_reutilizar_email_de_usuario_con_soft_delete_falla_por_indice_unico_db(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $colaborador = User::factory()->create([
            'email' => 'duplicado@comercio.com',
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($empresa->id);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);
        $colaborador->assignRole(RolSistema::CAJERO->value);

        // 1. Eliminar (soft delete) al colaborador
        $this->actingAs($admin)
            ->delete(route('usuarios.destroy', $colaborador))
            ->assertRedirect(route('usuarios.index'));

        $this->assertSoftDeleted('users', ['id' => $colaborador->id]);

        // 2. Intentar registrar otro usuario con el mismo email 'duplicado@comercio.com'
        $this->withoutExceptionHandling();
        $this->expectException(UniqueConstraintViolationException::class);

        $this->actingAs($admin)->post(route('usuarios.store'), [
            'name' => 'Nuevo Colaborador',
            'email' => 'duplicado@comercio.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sucursal_id' => $sucursal->id,
            'rol' => RolSistema::CAJERO->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);
    }
}
