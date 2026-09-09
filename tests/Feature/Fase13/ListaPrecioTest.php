<?php

namespace Tests\Feature\Fase13;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\ListasPrecios\ResolverPrecioProductoAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoAjusteListaPrecio;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\ListaPrecio;
use App\Models\ListaPrecioDetalle;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListaPrecioTest extends TestCase
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

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Empresa Alfa S.A.S.']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Empresa Beta S.A.S.']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalA = Sucursal::factory()->create(['empresa_id' => $this->empresaA->id, 'es_principal' => true]);
        $this->sucursalB = Sucursal::factory()->create(['empresa_id' => $this->empresaB->id, 'es_principal' => true]);

        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id, 'sucursal_id' => $this->sucursalB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor 500W',
            'codigo' => 'TAL-500',
            'precio_compra' => 100000.00,
            'precio_venta' => 150000.00,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 10,
            'stock_minimo' => 2,
        ]);

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Principal',
            'codigo' => 'CAJA-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        CompanyContext::clear();
    }

    public function test_invitado_no_puede_ver_listas_precios(): void
    {
        $response = $this->get(route('listas-precios.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_puede_ver_listado_de_listas_de_precios(): void
    {
        ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Público General',
            'codigo' => 'PUB',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('listas-precios.index'));

        $response->assertStatus(200);
        $response->assertViewIs('listas_precios.index');
        $response->assertSee('Público General');
    }

    public function test_admin_puede_crear_lista_de_precios_con_datos_validos(): void
    {
        $response = $this->actingAs($this->adminA)->post(route('listas-precios.store'), [
            'nombre' => 'Mayoristas Especial',
            'codigo' => 'MAY-ESP',
            'descripcion' => 'Descuento del 10% a mayoristas',
            'tipo_ajuste' => 'PORCENTAJE_DESCUENTO',
            'porcentaje_defecto' => 10.0,
            'es_predeterminada' => 0,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect(route('listas-precios.index'));
        $this->assertDatabaseHas('listas_precios', [
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Mayoristas Especial',
            'codigo' => 'MAY-ESP',
            'tipo_ajuste' => 'PORCENTAJE_DESCUENTO',
            'porcentaje_defecto' => 10.0,
        ]);
    }

    public function test_no_se_puede_crear_lista_con_codigo_duplicado_en_misma_empresa(): void
    {
        ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Lista 1',
            'codigo' => 'DIST',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('listas-precios.store'), [
            'nombre' => 'Lista 2',
            'codigo' => 'DIST',
            'tipo_ajuste' => 'FIJO',
            'estado' => 'ACTIVO',
        ]);

        $response->assertSessionHasErrors(['codigo']);
    }

    public function test_distintas_empresas_pueden_usar_mismo_codigo_de_lista(): void
    {
        ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Lista Alfa',
            'codigo' => 'COMUN',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminB)->post(route('listas-precios.store'), [
            'nombre' => 'Lista Beta',
            'codigo' => 'COMUN',
            'tipo_ajuste' => 'FIJO',
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect(route('listas-precios.index'));
        $this->assertDatabaseHas('listas_precios', [
            'empresa_id' => $this->empresaB->id,
            'codigo' => 'COMUN',
        ]);
    }

    public function test_marcar_lista_como_predeterminada_desmarca_la_anterior(): void
    {
        $lista1 = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Lista Inicial',
            'codigo' => 'INI',
            'es_predeterminada' => true,
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->actingAs($this->adminA)->post(route('listas-precios.store'), [
            'nombre' => 'Lista Nueva Predeterminada',
            'codigo' => 'NUEVA',
            'tipo_ajuste' => 'FIJO',
            'es_predeterminada' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->assertFalse((bool) $lista1->fresh()->es_predeterminada);
        $this->assertDatabaseHas('listas_precios', [
            'empresa_id' => $this->empresaA->id,
            'codigo' => 'NUEVA',
            'es_predeterminada' => true,
        ]);
    }

    public function test_admin_puede_asignar_precio_especifico_a_producto(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Super Distribuidor',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('listas-precios.precios.store', $lista), [
            'producto_id' => $this->productoA->id,
            'precio' => 135000.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lista_precio_detalles', [
            'empresa_id' => $this->empresaA->id,
            'lista_precio_id' => $lista->id,
            'producto_id' => $this->productoA->id,
            'precio' => 135000.00,
        ]);
    }

    public function test_resolver_precio_por_descuento_porcentual(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Descuento 15%',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO,
            'porcentaje_defecto' => 15.0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $resolver = app(ResolverPrecioProductoAction::class);
        $precio = $resolver->execute($this->productoA, listaPrecioId: $lista->id);

        // Precio base: 150000 - 15% (22500) = 127500
        $this->assertEquals(127500.00, $precio);
    }

    public function test_resolver_precio_por_aumento_porcentual(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Tarifa Remota +20%',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_AUMENTO,
            'porcentaje_defecto' => 20.0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $resolver = app(ResolverPrecioProductoAction::class);
        $precio = $resolver->execute($this->productoA, listaPrecioId: $lista->id);

        // Precio base: 150000 + 20% (30000) = 180000
        $this->assertEquals(180000.00, $precio);
    }

    public function test_precio_especifico_tiene_prioridad_sobre_regla_porcentual(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Descuento 10% con excepción',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO,
            'porcentaje_defecto' => 10.0,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Excepción puntual para productoA a 110000
        ListaPrecioDetalle::create([
            'empresa_id' => $this->empresaA->id,
            'lista_precio_id' => $lista->id,
            'producto_id' => $this->productoA->id,
            'precio' => 110000.00,
        ]);

        $resolver = app(ResolverPrecioProductoAction::class);
        $precio = $resolver->execute($this->productoA, listaPrecioId: $lista->id);

        $this->assertEquals(110000.00, $precio);
    }

    public function test_cliente_con_lista_asignada_aplica_precio_en_venta(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Tarifa VIP Oro',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO,
            'porcentaje_defecto' => 20.0, // 150000 -> 120000
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $cliente = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Cliente VIP Oro S.A.',
            'lista_precio_id' => $lista->id,
        ]);

        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->adminA, 10000.00);

        // Resolver precio para este cliente
        $precioCalculado = app(ResolverPrecioProductoAction::class)->execute($this->productoA, clienteId: $cliente->id);
        $this->assertEquals(120000.00, $precioCalculado);

        // Registrar venta
        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => $precioCalculado,
                    'impuesto_porcentaje' => 0,
                ],
            ],
            clienteId: $cliente->id,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $sesion->id,
            pagoCon: 120000.00
        );

        $this->assertEquals(120000.00, (float) $venta->total);
        $this->assertEquals($lista->id, $venta->lista_precio_id);
    }

    public function test_aislamiento_multiempresa_en_listas_de_precios(): void
    {
        $listaA = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Lista Secreta Alfa',
            'codigo' => 'SEC-A',
            'tipo_ajuste' => TipoAjusteListaPrecio::FIJO,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Admin B no puede ver ni editar la lista de Empresa A
        $response = $this->actingAs($this->adminB)->get(route('listas-precios.show', $listaA));
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));

        $responseEdit = $this->actingAs($this->adminB)->get(route('listas-precios.edit', $listaA));
        $this->assertTrue(in_array($responseEdit->getStatusCode(), [403, 404]));

        // Admin B no puede eliminar lista de Empresa A
        $responseDelete = $this->actingAs($this->adminB)->delete(route('listas-precios.destroy', $listaA));
        $this->assertTrue(in_array($responseDelete->getStatusCode(), [403, 404]));
    }
}
