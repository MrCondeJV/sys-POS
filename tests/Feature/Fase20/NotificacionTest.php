<?php

namespace Tests\Feature\Fase20;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\NivelNotificacion;
use App\Enums\RolSistema;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoNotificacion;
use App\Models\Caja;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\NotificacionSistema;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected Producto $productoA;
    protected Caja $cajaA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Notif',
            'nit' => '901777111-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Alfa Notif',
            'codigo' => 'SUC-A1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Admin Alfa',
            'email' => 'admin@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta Notif',
            'nit' => '902888222-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Beta Notif',
            'codigo' => 'SUC-B1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'Admin Beta',
            'email' => 'admin@beta.test',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Producto y Caja en A
        CompanyContext::setCompany($this->empresaA);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Pintura Epóxica Gris',
            'codigo' => 'PIN-EPOX',
            'precio_compra' => 45000.00,
            'precio_venta' => 75000.00,
            'stock' => 10,
            'stock_minimo' => 5,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 10, 'costo_promedio_ponderado' => 45000.00]
        );

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Notif 01',
            'codigo' => 'NOTIF-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);
    }

    public function test_invitados_redirigen_al_login(): void
    {
        $response = $this->get(route('notificaciones.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_salida_que_reduce_stock_por_debajo_del_minimo_crea_notificacion_automatica(): void
    {
        CompanyContext::setCompany($this->empresaA);

        // Retirar 6 unidades de 10 disponibles (quedan 4, inferior a stock_minimo = 5)
        app(RegistrarMovimientoInventarioAction::class)->execute(new MovimientoInventarioDTO(
            productoId: $this->productoA->id,
            sucursalId: $this->sucursalA->id,
            tipo: TipoMovimientoInventario::SALIDA_VENTA,
            cantidad: 6,
            referencia: 'VENTA-TEST',
            userId: $this->adminA->id
        ));

        $this->assertDatabaseHas('notificaciones_sistema', [
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::STOCK_BAJO->value,
            'nivel' => NivelNotificacion::WARNING->value,
            'leida' => false,
        ]);

        $notificacion = NotificacionSistema::where('empresa_id', $this->empresaA->id)
            ->where('tipo', TipoNotificacion::STOCK_BAJO)
            ->first();

        $this->assertStringContainsString('Pintura Epóxica Gris', $notificacion->titulo);
        $this->assertEquals(4, $notificacion->datos['stock_actual']);
        $this->assertEquals(5, $notificacion->datos['stock_minimo']);
    }

    public function test_cierre_de_caja_crea_notificacion_automatica(): void
    {
        CompanyContext::setCompany($this->empresaA);

        // Abrir caja
        $sesion = app(AbrirCajaAction::class)->execute(
            caja: $this->cajaA,
            cajero: $this->adminA,
            montoApertura: 100000.00
        );

        // Cerrar caja
        app(CerrarCajaAction::class)->execute(
            sesion: $sesion,
            auditor: $this->adminA,
            montoContado: 95000.00, // $5,000 faltante
            observaciones: 'Arqueo nocturno'
        );

        $this->assertDatabaseHas('notificaciones_sistema', [
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::CAJA_CERRADA->value,
            'leida' => false,
        ]);

        $notif = NotificacionSistema::where('empresa_id', $this->empresaA->id)
            ->where('tipo', TipoNotificacion::CAJA_CERRADA)
            ->first();

        $this->assertStringContainsString('Cierre de Turno', $notif->titulo);
        $this->assertStringContainsString('Caja Notif 01', $notif->mensaje);
    }

    public function test_marcar_notificacion_como_leida(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $notif = NotificacionSistema::create([
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::SISTEMA_ERROR,
            'nivel' => NivelNotificacion::INFO,
            'titulo' => 'Aviso de Mantenimiento',
            'mensaje' => 'El sistema se actualizará a medianoche.',
            'leida' => false,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('notificaciones.marcar-leida', $notif));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notificaciones_sistema', [
            'id' => $notif->id,
            'leida' => true,
        ]);
    }

    public function test_marcar_todas_las_notificaciones_como_leidas(): void
    {
        CompanyContext::setCompany($this->empresaA);

        NotificacionSistema::create([
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::STOCK_BAJO,
            'nivel' => NivelNotificacion::WARNING,
            'titulo' => 'Alerta 1',
            'mensaje' => 'Mensaje 1',
            'leida' => false,
        ]);

        NotificacionSistema::create([
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::CAJA_CERRADA,
            'nivel' => NivelNotificacion::INFO,
            'titulo' => 'Alerta 2',
            'mensaje' => 'Mensaje 2',
            'leida' => false,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('notificaciones.marcar-todas'));
        $response->assertSessionHas('success');

        $this->assertEquals(0, NotificacionSistema::where('empresa_id', $this->empresaA->id)->noLeidas()->count());
    }

    public function test_endpoint_conteo_no_leidas_retorna_json(): void
    {
        CompanyContext::setCompany($this->empresaA);

        NotificacionSistema::create([
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoNotificacion::STOCK_BAJO,
            'nivel' => NivelNotificacion::WARNING,
            'titulo' => 'Alerta Unread',
            'mensaje' => 'Mensaje test',
            'leida' => false,
        ]);

        $response = $this->actingAs($this->adminA)->getJson(route('notificaciones.conteo'));
        $response->assertOk();
        $response->assertJsonStructure(['conteo', 'recientes']);
        $response->assertJson(['conteo' => 1]);
    }

    public function test_aislamiento_multitenant_empresa_a_no_puede_ver_ni_marcar_notificaciones_empresa_b(): void
    {
        CompanyContext::setCompany($this->empresaB);

        $notifB = NotificacionSistema::create([
            'empresa_id' => $this->empresaB->id,
            'tipo' => TipoNotificacion::STOCK_BAJO,
            'nivel' => NivelNotificacion::DANGER,
            'titulo' => 'Alerta Secreta Empresa B',
            'mensaje' => 'Solo para ojos de B',
            'leida' => false,
        ]);

        // Admin A lista notificaciones -> no debe ver la notificación de B
        $responseList = $this->actingAs($this->adminA)->get(route('notificaciones.index'));
        $responseList->assertOk();
        $responseList->assertDontSee('Alerta Secreta Empresa B');

        // Admin A intenta marcar como leída Notificación B -> 404 (gracias a BelongsToCompany)
        $responsePost = $this->actingAs($this->adminA)->post(route('notificaciones.marcar-leida', $notifB));
        $this->assertContains($responsePost->status(), [403, 404]);

        // Notificación de B sigue sin leerse
        $this->assertDatabaseHas('notificaciones_sistema', [
            'id' => $notifB->id,
            'leida' => false,
        ]);
    }
}
