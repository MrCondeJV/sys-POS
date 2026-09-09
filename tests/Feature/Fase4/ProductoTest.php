<?php

namespace Tests\Feature\Fase4;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected User $adminA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Ferretería El Tornillo']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Supermercado Central']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_admin_empresa_puede_ver_listado_productos_de_su_empresa_solamente(): void
    {
        $productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Martillo de Acero 16oz',
            'codigo' => 'MART-16',
            'precio_venta' => 25000,
            'stock' => 10,
            'stock_minimo' => 2,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Leche Entera 1L',
            'codigo' => 'LECHE-1L',
            'precio_venta' => 4500,
            'stock' => 50,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('productos.index'));

        $response->assertOk();
        $response->assertSee('Martillo de Acero 16oz');
        $response->assertDontSee('Leche Entera 1L');
    }

    public function test_admin_empresa_puede_crear_un_producto_con_precios_e_impuestos(): void
    {
        $categoria = Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Herramientas Manuales',
            'activo' => true,
        ]);

        $marca = Marca::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Stanley',
            'activo' => true,
        ]);

        $unidad = UnidadMedida::create([
            'empresa_id' => $this->empresaA->id,
            'codigo' => 'UND',
            'nombre' => 'Unidad',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('productos.store'), [
            'nombre' => 'Destornillador Phillips #2',
            'codigo' => 'DEST-PH2',
            'codigo_barras' => '7701234567890',
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'unidad_medida_id' => $unidad->id,
            'precio_compra' => 8000,
            'precio_venta' => 15000,
            'precio_mayorista' => 12000,
            'stock' => 25,
            'stock_minimo' => 5,
            'iva' => 19,
            'estado' => 'activo',
            // Intento de manipulación de tenant:
            'empresa_id' => $this->empresaB->id,
        ]);

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success');

        $producto = Producto::withoutGlobalScopes()->where('codigo', 'DEST-PH2')->first();
        $this->assertNotNull($producto);
        $this->assertEquals($this->empresaA->id, $producto->empresa_id);
        $this->assertNotEquals($this->empresaB->id, $producto->empresa_id);
        $this->assertEquals(15000, (float) $producto->precio_venta);
        $this->assertEquals(19, (float) $producto->iva);
        $this->assertEquals(17850, $producto->calcularPrecioConIva());
    }

    public function test_validacion_impide_asociar_categoria_o_marca_de_otra_empresa(): void
    {
        // Categoría que pertenece a Empresa B
        $categoriaB = Categoria::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Lácteos Ajena',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('productos.store'), [
            'nombre' => 'Producto con Categoría Inválida',
            'precio_venta' => 10000,
            'categoria_id' => $categoriaB->id,
        ]);

        $response->assertSessionHasErrors(['categoria_id']);
        $this->assertDatabaseMissing('productos', [
            'nombre' => 'Producto con Categoría Inválida',
        ]);
    }

    public function test_admin_empresa_puede_actualizar_su_producto(): void
    {
        $producto = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Cinta Métrica 5m',
            'codigo' => 'CINTA-5M',
            'precio_venta' => 12000,
            'stock' => 15,
            'stock_minimo' => 3,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('productos.update', $producto), [
            'nombre' => 'Cinta Métrica Profesional 5m',
            'codigo' => 'CINTA-5M',
            'precio_venta' => 14500,
            'stock' => 20,
            'stock_minimo' => 5,
            'iva' => 0,
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto->refresh();
        $this->assertEquals('Cinta Métrica Profesional 5m', $producto->nombre);
        $this->assertEquals(14500, (float) $producto->precio_venta);
        $this->assertEquals(20, (float) $producto->stock);
    }

    public function test_admin_empresa_no_puede_modificar_o_eliminar_producto_de_otra_empresa(): void
    {
        $productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Arroz Diana 1kg',
            'precio_venta' => 3800,
            'stock' => 100,
            'stock_minimo' => 10,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Intentar actualizar producto de Empresa B desde sesión de Empresa A
        $response = $this->actingAs($this->adminA)->put(route('productos.update', $productoB), [
            'nombre' => 'Arroz Hackeado',
            'precio_venta' => 100,
        ]);

        // Debe retornar 404 o 403 por aislamiento global de tenancy y policy
        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));

        // Intentar eliminar producto de Empresa B
        $deleteResponse = $this->actingAs($this->adminA)->delete(route('productos.destroy', $productoB));
        $this->assertTrue(in_array($deleteResponse->getStatusCode(), [403, 404]));
    }

    public function test_filtro_por_busqueda_y_bajo_stock(): void
    {
        Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor 600W',
            'codigo' => 'TAL-600',
            'codigo_barras' => '770999888111',
            'precio_venta' => 180000,
            'stock' => 1,
            'stock_minimo' => 2, // Bajo stock
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sierra Circular 7-1/4',
            'codigo' => 'SIER-714',
            'precio_venta' => 250000,
            'stock' => 10,
            'stock_minimo' => 2, // Stock normal
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Búsqueda por código de barras
        $responseBusqueda = $this->actingAs($this->adminA)->get(route('productos.index', ['buscar' => '770999888111']));
        $responseBusqueda->assertSee('Taladro Percutor 600W');
        $responseBusqueda->assertDontSee('Sierra Circular 7-1/4');

        // Filtro solo bajo stock
        $responseBajoStock = $this->actingAs($this->adminA)->get(route('productos.index', ['bajo_stock' => 1]));
        $responseBajoStock->assertSee('Taladro Percutor 600W');
        $responseBajoStock->assertDontSee('Sierra Circular 7-1/4');
    }

    public function test_vistas_create_y_edit_se_renderizan_correctamente(): void
    {
        $producto = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Llave Inglesa 10 Pulgadas',
            'codigo' => 'LLAV-10',
            'precio_venta' => 32000,
            'stock' => 8,
            'stock_minimo' => 2,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $resCreate = $this->actingAs($this->adminA)->get(route('productos.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('Registrar Nuevo Producto');

        $resEdit = $this->actingAs($this->adminA)->get(route('productos.edit', $producto));
        $resEdit->assertOk();
        $resEdit->assertSee('Llave Inglesa 10 Pulgadas');
    }

    public function test_admin_empresa_puede_crear_producto_con_imagen(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('producto_demo.jpg', 600, 600);

        $response = $this->actingAs($this->adminA)->post(route('productos.store'), [
            'nombre' => 'Pintura Blanca 1 Galón',
            'precio_venta' => 45000,
            'imagen' => $file,
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto = Producto::where('nombre', 'Pintura Blanca 1 Galón')->first();
        $this->assertNotNull($producto);
        $this->assertNotNull($producto->imagen_path);
        Storage::disk('public')->assertExists($producto->imagen_path);
        $this->assertNotNull($producto->imagen_url);
    }

    public function test_admin_empresa_puede_actualizar_y_eliminar_imagen_de_producto(): void
    {
        Storage::fake('public');

        $fileInicial = UploadedFile::fake()->image('inicial.jpg', 400, 400);
        $pathInicial = $fileInicial->store("productos/{$this->empresaA->id}", 'public');

        $producto = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Brocha 2 Pulgadas',
            'precio_venta' => 8500,
            'imagen_path' => $pathInicial,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Storage::disk('public')->assertExists($pathInicial);

        // 1. Eliminar imagen existente mediante checkbox/flag
        $resEliminar = $this->actingAs($this->adminA)->put(route('productos.update', $producto), [
            'nombre' => 'Brocha 2 Pulgadas',
            'precio_venta' => 8500,
            'eliminar_imagen' => 1,
        ]);

        $resEliminar->assertRedirect(route('productos.index'));
        $producto->refresh();
        $this->assertNull($producto->imagen_path);
        Storage::disk('public')->assertMissing($pathInicial);

        // 2. Reemplazar/Subir nueva imagen
        $nuevaImagen = UploadedFile::fake()->image('nueva_brocha.png', 500, 500);
        $resSubir = $this->actingAs($this->adminA)->put(route('productos.update', $producto), [
            'nombre' => 'Brocha 2 Pulgadas',
            'precio_venta' => 8500,
            'imagen' => $nuevaImagen,
        ]);

        $resSubir->assertRedirect(route('productos.index'));
        $producto->refresh();
        $this->assertNotNull($producto->imagen_path);
        Storage::disk('public')->assertExists($producto->imagen_path);
    }

    public function test_validacion_rechaza_archivos_no_imagen_o_demasiado_pesados(): void
    {
        Storage::fake('public');

        $archivoInvalido = UploadedFile::fake()->create('documento.pdf', 500);

        $response = $this->actingAs($this->adminA)->post(route('productos.store'), [
            'nombre' => 'Producto Invalido',
            'precio_venta' => 10000,
            'imagen' => $archivoInvalido,
        ]);

        $response->assertSessionHasErrors(['imagen']);
    }

    public function test_dashboard_renderiza_metricas_y_articulos_del_tenant(): void
    {
        // Crear producto bajo stock para Empresa A
        Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Producto Critico A',
            'precio_venta' => 12000,
            'stock' => 2,
            'stock_minimo' => 10,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Crear producto para Empresa B
        Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Producto Exclusivo B',
            'precio_venta' => 45000,
            'stock' => 1,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Producto Critico A');
        $response->assertDontSee('Producto Exclusivo B');
        $response->assertSee('Estado de Artículos en Catálogo');
    }
}
