<?php

namespace Tests\Feature\Fase4;

use App\Enums\RolSistema;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
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

    public function test_admin_empresa_puede_ver_vista_catalogos(): void
    {
        Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Tornillos y Tuercas',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('catalogos.index'));

        $response->assertOk();
        $response->assertSee('Tornillos y Tuercas');
        $response->assertSee('Clasificación y Catálogos');
    }

    public function test_admin_empresa_puede_crear_categoria_marca_y_unidad_aisladas(): void
    {
        // 1. Crear Categoría
        $resCat = $this->actingAs($this->adminA)->post(route('catalogos.categorias.store'), [
            'nombre' => 'Pinturas y Solventes',
            'descripcion' => 'Línea de acabados arquitectónicos',
            'empresa_id' => $this->empresaB->id, // Intento de inyección
        ]);
        $resCat->assertRedirect();
        $cat = Categoria::withoutGlobalScopes()->where('nombre', 'Pinturas y Solventes')->first();
        $this->assertNotNull($cat);
        $this->assertEquals($this->empresaA->id, $cat->empresa_id);

        // 2. Crear Marca
        $resMarca = $this->actingAs($this->adminA)->post(route('catalogos.marcas.store'), [
            'nombre' => 'Pintuco',
            'empresa_id' => $this->empresaB->id,
        ]);
        $resMarca->assertRedirect();
        $marca = Marca::withoutGlobalScopes()->where('nombre', 'Pintuco')->first();
        $this->assertNotNull($marca);
        $this->assertEquals($this->empresaA->id, $marca->empresa_id);

        // 3. Crear Unidad de Medida
        $resUnidad = $this->actingAs($this->adminA)->post(route('catalogos.unidades.store'), [
            'codigo' => 'GAL',
            'nombre' => 'Galón',
            'empresa_id' => $this->empresaB->id,
        ]);
        $resUnidad->assertRedirect();
        $unidad = UnidadMedida::withoutGlobalScopes()->where('codigo', 'GAL')->first();
        $this->assertNotNull($unidad);
        $this->assertEquals($this->empresaA->id, $unidad->empresa_id);
    }

    public function test_nombres_de_categoria_son_unicos_por_empresa_pero_permiten_mismo_nombre_en_otra_empresa(): void
    {
        Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Ferretería General',
            'activo' => true,
        ]);

        // Intentar crear misma categoría en Empresa A debe fallar
        $resDuplicado = $this->actingAs($this->adminA)->post(route('catalogos.categorias.store'), [
            'nombre' => 'Ferretería General',
        ]);
        $resDuplicado->assertSessionHasErrors(['nombre']);

        // Pero Empresa B sí puede registrar el mismo nombre
        CompanyContext::setCompany($this->empresaB);
        $catB = Categoria::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Ferretería General',
            'activo' => true,
        ]);
        $this->assertNotNull($catB);
    }

    public function test_admin_empresa_puede_eliminar_categoria_propia(): void
    {
        $cat = Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Categoría Temporal',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('catalogos.categorias.destroy', $cat));
        $response->assertRedirect();

        $this->assertSoftDeleted('categorias', ['id' => $cat->id]);
    }

    public function test_usuario_no_puede_eliminar_categoria_de_otra_empresa(): void
    {
        $catB = Categoria::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Categoría de Empresa B',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('catalogos.categorias.destroy', $catB));

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
        $this->assertDatabaseHas('categorias', ['id' => $catB->id, 'deleted_at' => null]);
    }
}
