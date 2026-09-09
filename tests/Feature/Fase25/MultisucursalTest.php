<?php

namespace Tests\Feature\Fase25;

use App\Enums\EstadoGeneral;
use App\Enums\EstadoTraslado;
use App\Enums\RolSistema;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultisucursalTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sedeCentroA;
    protected Sucursal $sedeNorteA;
    protected Sucursal $sedeCentroB;
    protected User $adminA;
    protected User $cajeroA;
    protected User $adminB;
    protected Producto $productoA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería Central SAS',
            'nit' => '900111222-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Distribuciones del Norte SAS',
            'nit' => '900333444-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sedeCentroA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Centro',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sedeNorteA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Norte',
            'es_principal' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sedeCentroB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Principal B',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        setPermissionsTeamId($this->empresaA->id);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sedeCentroA->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sedeCentroA->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        setPermissionsTeamId($this->empresaB->id);
        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sedeCentroB->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        setPermissionsTeamId($this->empresaA->id);
        CompanyContext::setCompanyId($this->empresaA->id);
        BranchContext::setId($this->sedeCentroA->id);

        $cat = Categoria::create(['empresa_id' => $this->empresaA->id, 'nombre' => 'Herramientas']);
        $um = UnidadMedida::create(['empresa_id' => $this->empresaA->id, 'nombre' => 'Unidad', 'codigo' => 'UND', 'activo' => true]);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'categoria_id' => $cat->id,
            'unidad_medida_id' => $um->id,
            'codigo_barras' => 'TAL-001',
            'nombre' => 'Taladro Percutor 500W',
            'precio_compra' => 100000,
            'precio_venta' => 160000,
            'stock' => 50,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sedeCentroA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sedeNorteA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 0,
            'stock_minimo' => 5,
        ]);
    }

    public function test_admin_puede_despachar_traslado_entre_sucursales_y_descuenta_stock_origen(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('traslados.store'), [
            'sucursal_origen_id' => $this->sedeCentroA->id,
            'sucursal_destino_id' => $this->sedeNorteA->id,
            'motivo' => 'Envío de stock para abastecer Sede Norte',
            'observaciones' => 'Chofer Juan Camión ABC-123',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 15,
                    'observaciones' => 'Cajas selladas',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('traslados_sucursal', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_origen_id' => $this->sedeCentroA->id,
            'sucursal_destino_id' => $this->sedeNorteA->id,
            'estado' => EstadoTraslado::EN_TRANSITO->value,
            'motivo' => 'Envío de stock para abastecer Sede Norte',
        ]);

        $this->assertDatabaseHas('traslado_detalles', [
            'producto_id' => $this->productoA->id,
            'cantidad_enviada' => 15,
            'cantidad_recibida' => 0,
        ]);

        // Stock de origen se redujo inmediatamente
        $invOrigen = Inventario::where('sucursal_id', $this->sedeCentroA->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(35, (float) $invOrigen->stock);

        // Stock de destino sigue en 0 hasta que sea recibido físicamente
        $invDestino = Inventario::where('sucursal_id', $this->sedeNorteA->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(0, (float) $invDestino->stock);
    }

    public function test_recepcion_de_traslado_ingresa_stock_a_destino_y_cambia_a_recibido(): void
    {
        // 1. Despachar
        $this->actingAs($this->adminA)->post(route('traslados.store'), [
            'sucursal_origen_id' => $this->sedeCentroA->id,
            'sucursal_destino_id' => $this->sedeNorteA->id,
            'motivo' => 'Envío para recepción',
            'items' => [
                ['producto_id' => $this->productoA->id, 'cantidad' => 20],
            ],
        ]);

        $traslado = \App\Models\TrasladoSucursal::latest('id')->first();
        $this->assertNotNull($traslado);
        $this->assertEquals(EstadoTraslado::EN_TRANSITO, $traslado->estado);

        // 2. Recibir en destino
        $response = $this->actingAs($this->adminA)->post(route('traslados.recibir', $traslado), [
            'observaciones' => 'Mercancía recibida completa y en perfecto estado',
        ]);

        $response->assertRedirect(route('traslados.show', $traslado));

        $traslado->refresh();
        $this->assertEquals(EstadoTraslado::RECIBIDO, $traslado->estado);
        $this->assertNotNull($traslado->fecha_recepcion);

        // Stock en destino aumentó a 20
        $invDestino = Inventario::where('sucursal_id', $this->sedeNorteA->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(20, (float) $invDestino->stock);

        // Stock consolidado en producto
        $this->productoA->refresh();
        $this->assertEquals(50, (float) $this->productoA->stock);
    }

    public function test_rechazo_de_traslado_reversa_stock_a_origen(): void
    {
        // 1. Despachar 10 unidades
        $this->actingAs($this->adminA)->post(route('traslados.store'), [
            'sucursal_origen_id' => $this->sedeCentroA->id,
            'sucursal_destino_id' => $this->sedeNorteA->id,
            'motivo' => 'Traslado que se rechazará',
            'items' => [
                ['producto_id' => $this->productoA->id, 'cantidad' => 10],
            ],
        ]);

        $traslado = \App\Models\TrasladoSucursal::latest('id')->first();
        $invOrigenAntes = Inventario::where('sucursal_id', $this->sedeCentroA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(40, (float) $invOrigenAntes->stock);

        // 2. Rechazar en destino
        $response = $this->actingAs($this->adminA)->post(route('traslados.rechazar', $traslado), [
            'motivo_rechazo' => 'Cajas averiadas por lluvia en transporte',
        ]);

        $response->assertRedirect(route('traslados.show', $traslado));

        $traslado->refresh();
        $this->assertEquals(EstadoTraslado::RECHAZADO, $traslado->estado);

        // El stock en origen volvió a 50
        $invOrigenDespues = Inventario::where('sucursal_id', $this->sedeCentroA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(50, (float) $invOrigenDespues->stock);

        // El stock en destino se mantuvo en 0
        $invDestino = Inventario::where('sucursal_id', $this->sedeNorteA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(0, (float) $invDestino->stock);
    }

    public function test_usuario_restringido_no_puede_cambiar_a_sucursal_no_asignada(): void
    {
        // El cajeroA solo tiene sucursal_id = sedeCentroA, no está en sucursal_user de sedeNorteA
        $response = $this->actingAs($this->cajeroA)->post(route('sucursales.seleccionar'), [
            'sucursal_id' => $this->sedeNorteA->id,
        ]);

        $response->assertSessionHasErrors('sucursal_id');

        // Ahora se le asigna la sedeNorteA en sucursal_user
        $this->cajeroA->sucursales()->attach($this->sedeNorteA->id, ['empresa_id' => $this->empresaA->id]);

        $responseOk = $this->actingAs($this->cajeroA)->post(route('sucursales.seleccionar'), [
            'sucursal_id' => $this->sedeNorteA->id,
        ]);

        $responseOk->assertSessionHasNoErrors();
        $responseOk->assertSessionHas('success');
    }

    public function test_aislamiento_multiempresa_en_traslados(): void
    {
        // Despacho en empresa A
        $this->actingAs($this->adminA)->post(route('traslados.store'), [
            'sucursal_origen_id' => $this->sedeCentroA->id,
            'sucursal_destino_id' => $this->sedeNorteA->id,
            'motivo' => 'Traslado confidencial empresa A',
            'items' => [
                ['producto_id' => $this->productoA->id, 'cantidad' => 5],
            ],
        ]);

        $trasladoA = \App\Models\TrasladoSucursal::latest('id')->first();

        // Admin de empresa B no puede ver ni recibir traslado de empresa A
        $responseShow = $this->actingAs($this->adminB)->get(route('traslados.show', $trasladoA));
        $this->assertTrue(in_array($responseShow->status(), [403, 404]));

        $responseRecibir = $this->actingAs($this->adminB)->post(route('traslados.recibir', $trasladoA), [
            'observaciones' => 'Intento ilegítimo',
        ]);
        $this->assertTrue(in_array($responseRecibir->status(), [403, 404]));
    }

    public function test_reporte_multisucursal_consolida_existencias_y_ventas_por_sede(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('reportes.multisucursal'));
        $response->assertStatus(200);
        $response->assertSee('Consolidado Multisucursal');
        $response->assertSee('Taladro Percutor 500W');
        $response->assertSee('Sede Centro');
        $response->assertSee('Sede Norte');
    }
}
