<?php

namespace Tests\Feature\Modulos;

use App\Enums\RolSistema;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

class CatalogoEdicionTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
        CompanyContext::setCompany($this->empresa);
    }

    public function test_la_vista_ofrece_editar_categorias_marcas_y_unidades_incluso_en_uso(): void
    {
        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Categoria Editable Visible', 'activo' => true]);
        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Marca Editable Visible', 'activo' => true]);
        $unidad = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Unidad Editable Visible', 'codigo' => 'UEV', 'activo' => true]);

        $html = $this->actingAs($this->admin)->get(route('catalogos.index'))->assertOk()->getContent();
        $legible = str_replace(['\\\\/', '\/', '\u0022', '\u0027'], ['/', '/', '"', "'"], html_entity_decode($html, ENT_QUOTES));

        $this->assertStringNotContainsString('@js(', $html, 'Hay directivas sin compilar en la vista');
        foreach ([
            route('catalogos.categorias.update', $categoria),
            route('catalogos.marcas.update', $marca),
            route('catalogos.unidades.update', $unidad),
            // La categoría del producto del escenario está en uso y también debe poder editarse
            route('catalogos.categorias.update', $this->producto->categoria_id),
        ] as $url) {
            $this->assertStringContainsString('abrirEdicion(', $legible);
            $this->assertStringContainsString('"action":"'.$url.'"', $legible, "Falta el botón de editar hacia {$url}");
        }
    }

    public function test_editar_categoria(): void
    {
        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Pinturs', 'activo' => true]);

        $this->actingAs($this->admin)->from(route('catalogos.index'))
            ->put(route('catalogos.categorias.update', $categoria), ['nombre' => 'Pinturas', 'descripcion' => 'Vinilos y esmaltes'])
            ->assertRedirect(route('catalogos.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'nombre' => 'Pinturas', 'descripcion' => 'Vinilos y esmaltes']);
    }

    public function test_editar_marca_conservando_su_mismo_nombre(): void
    {
        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Truper', 'activo' => true]);

        $this->actingAs($this->admin)->from(route('catalogos.index'))
            ->put(route('catalogos.marcas.update', $marca), ['nombre' => 'Truper', 'descripcion' => 'Herramienta mexicana'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('marcas', ['id' => $marca->id, 'descripcion' => 'Herramienta mexicana']);
    }

    public function test_editar_unidad(): void
    {
        $unidad = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Metr', 'codigo' => 'MT', 'activo' => true]);

        $this->actingAs($this->admin)->from(route('catalogos.index'))
            ->put(route('catalogos.unidades.update', $unidad), ['nombre' => 'Metro', 'codigo' => 'M'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('unidades_medida', ['id' => $unidad->id, 'nombre' => 'Metro', 'codigo' => 'M']);
    }

    public function test_no_permite_nombre_duplicado_al_editar(): void
    {
        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Bosch', 'activo' => true]);
        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Makita', 'activo' => true]);

        $this->actingAs($this->admin)->from(route('catalogos.index'))
            ->put(route('catalogos.marcas.update', $marca), ['nombre' => 'Bosch'])
            ->assertSessionHasErrors('nombre');

        $this->assertDatabaseHas('marcas', ['id' => $marca->id, 'nombre' => 'Makita']);
    }

    public function test_otra_empresa_no_puede_editar(): void
    {
        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Eléctricos', 'activo' => true]);
        $ajena = $this->crearEmpresaAjena();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($ajena['ajena']);
        $adminAjeno = User::factory()->create(['empresa_id' => $ajena['ajena']->id, 'sucursal_id' => $ajena['sede']->id]);
        setPermissionsTeamId($ajena['ajena']->id);
        $adminAjeno->assignRole(RolSistema::ADMIN_EMPRESA->value);
        setPermissionsTeamId($this->empresa->id);

        $respuesta = $this->actingAs($adminAjeno)->put(route('catalogos.categorias.update', $categoria), ['nombre' => 'Hackeada']);

        $this->assertContains($respuesta->status(), [403, 404]);
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'nombre' => 'Eléctricos']);
    }
}
