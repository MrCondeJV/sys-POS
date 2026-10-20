<?php

namespace Tests\Feature\Seguridad;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
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
     * 1B.1(a): Inactivar un usuario con sesión abierta lo expulsa en la siguiente petición HTTP.
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
     * 1B.1(b): Verifica que un usuario de una empresa en estado INACTIVO NO pueda iniciar sesión ni operar.
     */
    public function test_usuario_de_empresa_inactiva_no_deberia_poder_iniciar_sesion_ni_operar(): void
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

        $loginResponse = $this->post('/login', [
            'email' => 'operador@empresainactiva.com',
            'password' => 'secret1234',
        ]);

        $loginResponse->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * 1B.1(c): Verifica qué ocurre al crear un usuario con el mismo email de un usuario con soft delete.
     * Con la validación corregida, Laravel retorna error de validación 422 en vez de error 500 SQL.
     */
    public function test_reutilizar_email_de_usuario_con_soft_delete_no_deberia_lanzar_error_500_sql(): void
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
        $response = $this->actingAs($admin)->post(route('usuarios.store'), [
            'name' => 'Nuevo Colaborador',
            'email' => 'duplicado@comercio.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sucursal_id' => $sucursal->id,
            'rol' => RolSistema::CAJERO->value,
            'estado' => EstadoGeneral::ACTIVO->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 1B.2: Comprobante de cierre de caja renderiza cero URLs externas y contiene reglas @media print (Carta y 80mm).
     */
    public function test_comprobante_cierre_caja_renderiza_sin_urls_externas_y_con_reglas_print(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($empresa->id);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($empresa);
        $caja = Caja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Caja Principal 01',
            'codigo' => 'CAJ-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $sesion = app(AbrirCajaAction::class)->execute($caja, $admin, 100000.00);
        app(CerrarCajaAction::class)->execute($sesion, $admin, 100000.00, 'Arqueo cuadrado sin novedades.');

        $response = $this->actingAs($admin)->get(route('cajas.comprobante', $sesion));
        $response->assertOk();

        $html = $response->getContent();

        // Verificar presencia de reglas @media print y ambos formatos (Carta y Térmica 80mm)
        $this->assertStringContainsString('@media print', $html);
        $this->assertStringContainsString('size: letter portrait', $html);
        $this->assertStringContainsString('size: 80mm auto', $html);
        $this->assertStringContainsString('print-color-adjust: exact', $html);
        $this->assertStringContainsString('cambiarFormatoImpresion', $html);

        // Verificar cero recursos externos (ningún script/link/import hacia dominios externos)
        preg_match_all('/(?:src|href)=["\'](https?:\/\/[^"\']+)["\']/i', $html, $matches);
        $baseUrl = rtrim(url('/'), '/');
        $externalUrls = array_filter($matches[1] ?? [], function (string $url) use ($baseUrl) {
            return ! str_starts_with($url, 'http://localhost')
                && ! str_starts_with($url, 'http://127.0.0.1')
                && ! str_starts_with($url, 'http://sys-pos.test')
                && ! str_starts_with($url, $baseUrl)
                && ! str_starts_with($url, (string) config('app.url'));
        });

        $this->assertEmpty(
            $externalUrls,
            'El comprobante de cierre no debe cargar recursos externos: '.implode(', ', $externalUrls)
        );
    }

    /**
     * 1B.3: Modales de caja en cajas/index generan rutas con route() + placeholder y los endpoints responden.
     */
    public function test_modales_de_cajas_usan_rutas_nombradas_y_endpoints_funcionan(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $empresa = Empresa::factory()->create(['estado' => EstadoGeneral::ACTIVO]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id, 'es_principal' => true]);

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($empresa->id);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($empresa);
        $caja = Caja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Caja Verificación Rutas',
            'codigo' => 'CAJ-VR',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        // 1. Verificar que la vista renderiza las plantillas de ruta nombradas y no concatena '/cajas/' en duro
        $indexResponse = $this->actingAs($admin)->get(route('cajas.index'));
        $indexResponse->assertOk();
        $html = $indexResponse->getContent();

        $this->assertStringContainsString('urlAbrirTemplate', $html);
        $this->assertStringContainsString('urlMovimientoTemplate', $html);
        $this->assertStringContainsString('__CAJA__', $html);
        $this->assertStringNotContainsString("'/cajas/' +", $html);

        // 2. Simular el reemplazo JS del placeholder '__CAJA__' y enviar POST real al endpoint de apertura
        $abrirUrl = str_replace('__CAJA__', (string) $caja->id, route('cajas.abrir', '__CAJA__'));
        $this->actingAs($admin)->post($abrirUrl, [
            'monto_apertura' => 50000,
        ])->assertRedirect();

        $this->assertTrue($caja->fresh()->estaAbierta());

        // 3. Simular el reemplazo JS del placeholder '__CAJA__' y enviar POST real al endpoint de movimiento
        $movimientoUrl = str_replace('__CAJA__', (string) $caja->id, route('cajas.movimiento', '__CAJA__'));
        $this->actingAs($admin)->post($movimientoUrl, [
            'tipo' => TipoMovimientoCaja::INGRESO->value,
            'monto' => 15000,
            'concepto' => 'Base adicional de monedas',
            'metodo_pago' => 'EFECTIVO',
        ])->assertRedirect();

        $this->assertEquals(65000.00, $caja->fresh()->sesionActual->calcularSaldoEsperadoEfectivo());
    }
}
