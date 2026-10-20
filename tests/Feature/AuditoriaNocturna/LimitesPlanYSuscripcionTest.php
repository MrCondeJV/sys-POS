<?php

namespace Tests\Feature\AuditoriaNocturna;

use App\Enums\EstadoGeneral;
use App\Enums\EstadoSuscripcion;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Plan;
use App\Models\Sucursal;
use App\Models\Suscripcion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class LimitesPlanYSuscripcionTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    private Sucursal $sucursal;

    private User $admin;

    private Plan $planBasico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->planBasico = Plan::create([
            'nombre' => 'Plan Básico Pyme',
            'slug' => 'basico-pyme',
            'descripcion' => 'Hasta 1 sucursal y 2 usuarios',
            'precio_mensual' => 49900,
            'precio_anual' => 499000,
            'limite_sucursales' => 1,
            'limite_usuarios' => 2,
            'permite_facturacion_electronica' => false,
            'permite_api' => false,
            'activo' => true,
        ]);

        $this->empresa = Empresa::create([
            'nombre_comercial' => 'Comercializadora Límite S.A.S.',
            'razon_social' => 'Comercializadora Límite S.A.S.',
            'nit' => '901555666-1',
            'email' => 'admin@limite.co',
            'moneda' => 'COP',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Única',
            'codigo' => 'SUC-01',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Límite',
            'email' => 'admin@limite.co',
            'password' => 'password',
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($this->empresa->id);
        $this->admin->assignRole(RolSistema::ADMIN_EMPRESA->value);
    }

    public function test_limite_sucursales_se_respeta_cuando_suscripcion_esta_activa(): void
    {
        Suscripcion::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'plan_id' => $this->planBasico->id,
            'estado' => EstadoSuscripcion::ACTIVA,
            'fecha_inicio' => now()->subDays(5),
            'fecha_fin' => now()->addDays(25),
            'ciclo_facturacion' => 'MENSUAL',
            'precio_pago' => 49900,
        ]);

        // Ya existe 1 sucursal (Sede Única) y el plan permite máximo 1
        $response = $this->actingAs($this->admin)->post(route('sucursales.store'), [
            'nombre' => 'Segunda Sede No Permitida',
            'codigo' => 'SUC-02',
        ]);

        $response->assertSessionHasErrors('general');
        $this->assertEquals(1, Sucursal::withoutGlobalScopes()->where('empresa_id', $this->empresa->id)->count());
    }

    public function test_bug_007_user_controller_store_no_valida_limite_usuarios_del_plan(): void
    {
        Suscripcion::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'plan_id' => $this->planBasico->id,
            'estado' => EstadoSuscripcion::ACTIVA,
            'fecha_inicio' => now()->subDays(5),
            'fecha_fin' => now()->addDays(25),
            'ciclo_facturacion' => 'MENSUAL',
            'precio_pago' => 49900,
        ]);

        // Crear segundo usuario (alcanza el límite de 2 usuarios del plan)
        User::create([
            'name' => 'Cajero 1',
            'email' => 'cajero1@limite.co',
            'password' => 'password',
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->assertFalse($this->empresa->puedeCrearUsuario());

        // Intento de crear un 3er usuario vía UserController@store
        $response = $this->actingAs($this->admin)->post(route('usuarios.store'), [
            'name' => 'Cajero Excedente',
            'email' => 'cajero2@limite.co',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sucursal_id' => $this->sucursal->id,
            'rol' => RolSistema::CAJERO->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $this->assertFalse(User::withoutGlobalScopes()->where('email', 'cajero2@limite.co')->exists());
        $response->assertSessionHasErrors('general');
    }

    public function test_bug_008_suscripcion_vencida_o_suspendida_desactiva_limites_y_no_bloquea_escritura(): void
    {
        Suscripcion::withoutGlobalScopes()->create([
            'empresa_id' => $this->empresa->id,
            'plan_id' => $this->planBasico->id,
            'estado' => EstadoSuscripcion::VENCIDA,
            'fecha_inicio' => now()->subDays(40),
            'fecha_fin' => now()->subDays(10),
            'ciclo_facturacion' => 'MENSUAL',
            'precio_pago' => 49900,
        ]);

        $this->assertFalse($this->empresa->puedeCrearSucursal());
        $this->assertFalse($this->empresa->puedeCrearUsuario());
    }
}
