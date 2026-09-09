<?php

namespace Tests\Feature\Fase5;

use App\Enums\RolSistema;
use App\Enums\TipoMovimientoInventario;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected Sucursal $sucursalA1;

    protected Sucursal $sucursalA2;

    protected Sucursal $sucursalB;

    protected User $adminA;

    protected User $adminB;

    protected Producto $productoA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A con dos sucursales para traslados
        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Ferretería El Tornillo']);
        $this->sucursalA1 = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Principal',
            'es_principal' => true,
        ]);
        $this->sucursalA2 = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Norte',
            'es_principal' => false,
        ]);

        // Empresa B con una sucursal para pruebas de aislamiento
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Supermercado Central']);
        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sucursal Central B',
            'es_principal' => true,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Producto inicial en Empresa A con existencias en Sede Principal
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor 650W',
            'codigo' => 'TAL-650',
            'codigo_barras' => '7701234567890',
            'precio_compra' => 120000,
            'precio_venta' => 180000,
            'stock' => 10,
            'stock_minimo' => 2,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoA->id,
            'stock' => 10,
            'stock_minimo' => 2,
            'ubicacion' => 'Pasillo 1',
        ]);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_admin_empresa_puede_ver_panel_de_inventario(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('inventario.index'));

        $response->assertStatus(200);
        $response->assertSee('Control de Inventario');
        $response->assertSee('Taladro Percutor 650W');
        $response->assertSee('TAL-650');
        $response->assertSee('Valoración a Costo');
    }

    public function test_ajuste_positivo_incrementa_stock_sucursal_y_producto_y_genera_kardex(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.ajuste.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'tipo' => 'AJUSTE_POSITIVO',
            'cantidad' => 5,
            'motivo' => 'Conteo físico: sobrante en bodega',
            'notas' => 'Auditoría interna #402',
        ]);

        $response->assertRedirect(route('inventario.kardex', $this->productoA->id));
        $response->assertSessionHas('success');

        // Verificar existencias en la sucursal
        $inv = Inventario::where('sucursal_id', $this->sucursalA1->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(15.0, (float) $inv->stock);

        // Verificar sincronización global en el producto
        $this->productoA->refresh();
        $this->assertEquals(15.0, (float) $this->productoA->stock);

        // Verificar asiento inmutable en Kardex
        $this->assertDatabaseHas('movimientos_inventario', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'producto_id' => $this->productoA->id,
            'user_id' => $this->adminA->id,
            'tipo' => TipoMovimientoInventario::AJUSTE_POSITIVO->value,
            'cantidad' => 5,
            'stock_anterior' => 10,
            'stock_posterior' => 15,
            'referencia' => 'Conteo físico: sobrante en bodega',
        ]);
    }

    public function test_ajuste_negativo_decrementa_stock_sucursal_y_producto_y_genera_kardex(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.ajuste.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'tipo' => 'AJUSTE_NEGATIVO',
            'cantidad' => 3,
            'motivo' => 'Merma por daño durante manipulación',
        ]);

        $response->assertRedirect(route('inventario.kardex', $this->productoA->id));

        $inv = Inventario::where('sucursal_id', $this->sucursalA1->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(7.0, (float) $inv->stock);

        $this->productoA->refresh();
        $this->assertEquals(7.0, (float) $this->productoA->stock);

        $this->assertDatabaseHas('movimientos_inventario', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'tipo' => TipoMovimientoInventario::AJUSTE_NEGATIVO->value,
            'cantidad' => 3,
            'stock_anterior' => 10,
            'stock_posterior' => 7,
        ]);
    }

    public function test_ajuste_negativo_con_stock_insuficiente_es_rechazado_y_no_altera_el_balance(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.ajuste.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_id' => $this->sucursalA1->id,
            'tipo' => 'AJUSTE_NEGATIVO',
            'cantidad' => 50, // Stock actual es 10
            'motivo' => 'Intento inválido de merma excesiva',
        ]);

        $response->assertSessionHasErrors('cantidad');

        // El stock no debe haber cambiado
        $inv = Inventario::where('sucursal_id', $this->sucursalA1->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(10.0, (float) $inv->stock);

        $this->productoA->refresh();
        $this->assertEquals(10.0, (float) $this->productoA->stock);
    }

    public function test_traslado_entre_sucursales_mueve_stock_atomicamente_y_crea_ambos_movimientos_en_kardex(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.traslado.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_origen_id' => $this->sucursalA1->id,
            'sucursal_destino_id' => $this->sucursalA2->id,
            'cantidad' => 4,
            'motivo' => 'Transferencia para surtido de Sede Norte',
        ]);

        $response->assertRedirect(route('inventario.kardex', $this->productoA->id));
        $response->assertSessionHas('success');

        // Origen debe quedar con 10 - 4 = 6
        $invOrigen = Inventario::where('sucursal_id', $this->sucursalA1->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(6.0, (float) $invOrigen->stock);

        // Destino debe quedar con 0 + 4 = 4
        $invDestino = Inventario::where('sucursal_id', $this->sucursalA2->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertNotNull($invDestino);
        $this->assertEquals(4.0, (float) $invDestino->stock);

        // Stock global del producto debe seguir siendo 10 (conservación de materia)
        $this->productoA->refresh();
        $this->assertEquals(10.0, (float) $this->productoA->stock);

        // Asientos en Kardex: TRASLADO_SALIDA en origen y TRASLADO_ENTRADA en destino
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $this->sucursalA1->id,
            'sucursal_destino_id' => $this->sucursalA2->id,
            'tipo' => TipoMovimientoInventario::TRASLADO_SALIDA->value,
            'cantidad' => 4,
            'stock_anterior' => 10,
            'stock_posterior' => 6,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $this->sucursalA2->id,
            'sucursal_destino_id' => $this->sucursalA1->id,
            'tipo' => TipoMovimientoInventario::TRASLADO_ENTRADA->value,
            'cantidad' => 4,
            'stock_anterior' => 0,
            'stock_posterior' => 4,
        ]);
    }

    public function test_traslado_falla_si_sucursal_origen_no_tiene_stock_suficiente(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.traslado.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_origen_id' => $this->sucursalA1->id,
            'sucursal_destino_id' => $this->sucursalA2->id,
            'cantidad' => 20, // Stock disponible es solo 10
            'motivo' => 'Transferencia excesiva',
        ]);

        $response->assertSessionHasErrors('cantidad');

        // Las existencias no deben modificarse
        $invOrigen = Inventario::where('sucursal_id', $this->sucursalA1->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(10.0, (float) $invOrigen->stock);
    }

    public function test_traslado_falla_si_origen_y_destino_son_la_misma_sucursal(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.traslado.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_origen_id' => $this->sucursalA1->id,
            'sucursal_destino_id' => $this->sucursalA1->id, // Misma sucursal
            'cantidad' => 2,
            'motivo' => 'Traslado a sí mismo',
        ]);

        $response->assertSessionHasErrors('sucursal_destino_id');
    }

    public function test_aislamiento_multiempresa_usuario_empresa_a_no_puede_ver_kardex_de_empresa_b(): void
    {
        $productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Aceite Vegetal 3000ml',
            'codigo' => 'ACE-3000',
            'precio_venta' => 28000,
            'stock' => 20,
            'stock_minimo' => 5,
        ]);

        // Intentar ver Kardex de Empresa B siendo usuario de Empresa A
        $response = $this->actingAs($this->adminA)->get(route('inventario.kardex', $productoB->id));

        $response->assertStatus(403);
    }

    public function test_aislamiento_multiempresa_usuario_a_no_puede_ajustar_stock_de_producto_empresa_b(): void
    {
        $productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Producto Empresa B',
            'precio_venta' => 10000,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('inventario.ajuste.store'), [
            'producto_id' => $productoB->id,
            'sucursal_id' => $this->sucursalA1->id,
            'tipo' => 'AJUSTE_POSITIVO',
            'cantidad' => 5,
            'motivo' => 'Ajuste cruzado no autorizado',
        ]);

        $response->assertSessionHasErrors('producto_id');
    }

    public function test_aislamiento_multiempresa_no_se_puede_trasladar_hacia_sucursal_de_otra_empresa(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('inventario.traslado.store'), [
            'producto_id' => $this->productoA->id,
            'sucursal_origen_id' => $this->sucursalA1->id,
            'sucursal_destino_id' => $this->sucursalB->id, // Sucursal de Empresa B
            'cantidad' => 2,
            'motivo' => 'Fuga de inventario inter-empresa',
        ]);

        $response->assertSessionHasErrors('sucursal_destino_id');
    }

    public function test_vistas_create_ajuste_y_create_traslado_se_renderizan_correctamente(): void
    {
        $resAjuste = $this->actingAs($this->adminA)->get(route('inventario.ajuste.create', ['producto_id' => $this->productoA->id]));
        $resAjuste->assertStatus(200);
        $resAjuste->assertSee('Nuevo Ajuste de Inventario');

        $resTraslado = $this->actingAs($this->adminA)->get(route('inventario.traslado.create', ['producto_id' => $this->productoA->id]));
        $resTraslado->assertStatus(200);
        $resTraslado->assertSee('Traslado de Existencias entre Sucursales');
    }
}
