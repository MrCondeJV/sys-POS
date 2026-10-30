<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\EstadoResolucionFacturacion;
use App\Enums\RolSistema;
use App\Enums\TipoImpuesto;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Impuesto;
use App\Models\ListaPrecio;
use App\Models\ListaPrecioDetalle;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\ResolucionFacturacion;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Recorre el botón "Eliminar" de cada módulo: la vista debe ofrecerlo apuntando a la ruta correcta
 * y la eliminación debe completarse (borrado lógico) o rechazarse con un mensaje, nunca con error.
 */
class EliminacionesTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
        CompanyContext::setCompany($this->empresa);
    }

    /**
     * HTML decodificado para buscar URLs: entidades HTML y escapes de JSON dentro de JSON.parse('...').
     */
    private function legible(string $html): string
    {
        return str_replace(['\\\\/', '\/', '\u0022', '\u0027'], ['/', '/', '"', "'"], html_entity_decode($html, ENT_QUOTES));
    }

    /**
     * La vista debe incluir la URL de eliminación (cruda o escapada dentro de JSON de Alpine).
     */
    private function assertVistaOfreceEliminar(string $pagina, string $urlEliminar): string
    {
        $html = $this->actingAs($this->admin)->get($pagina)->assertOk()->getContent();
        // Las directivas Blade dentro de atributos de componentes no se compilan y rompen el JavaScript del botón
        $this->assertStringNotContainsString('@js(', $html, "La vista {$pagina} tiene directivas @js sin compilar");
        $legible = $this->legible($html);

        // La URL completa (cerrada por comillas) fuera de un enlace href: así no cuentan "Ver" (misma URL) ni "Editar" (prefijo)
        $this->assertMatchesRegularExpression(
            '#(?<!href=")'.preg_quote($urlEliminar, '#').'["\']#',
            $legible,
            "La vista {$pagina} no ofrece el botón de eliminar hacia {$urlEliminar}"
        );

        return $html;
    }

    private function assertEliminado(string $pagina, string $urlEliminar, Model $registro): void
    {
        $this->actingAs($this->admin)->from($pagina)->delete($urlEliminar)
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSoftDeleted($registro);
    }

    public function test_eliminar_producto_desde_listado_y_desde_edicion(): void
    {
        foreach (['index', 'edit'] as $vista) {
            $producto = Producto::create([
                'empresa_id' => $this->empresa->id,
                'nombre' => "Producto Eliminable {$vista}",
                'codigo' => "ELIM-{$vista}",
                'precio_compra' => 1000,
                'precio_venta' => 2000,
                'estado' => EstadoGeneral::ACTIVO,
            ]);
            $url = route('productos.destroy', $producto);
            $pagina = $vista === 'index' ? route('productos.index', ['buscar' => $producto->nombre]) : route('productos.edit', $producto);

            $html = $this->assertVistaOfreceEliminar($pagina, $url);
            // El botón de confirmar del modal debe enviar el formulario oculto de eliminación
            $this->assertStringContainsString("document.getElementById('deleteProductForm').submit()", html_entity_decode($html, ENT_QUOTES));

            $this->assertEliminado($pagina, $url, $producto);
        }
    }

    public function test_eliminar_cliente(): void
    {
        $cliente = Cliente::factory()->for($this->empresa)->create(['razon_social' => 'Cliente Eliminable']);
        $url = route('clientes.destroy', $cliente);
        $pagina = route('clientes.index', ['buscar' => 'Cliente Eliminable']);

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $cliente);
    }

    public function test_eliminar_proveedor(): void
    {
        $proveedor = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'tipo_documento' => 'NIT',
            'numero_documento' => '900555666',
            'razon_social' => 'Proveedor Eliminable',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $url = route('proveedores.destroy', $proveedor);
        $pagina = route('proveedores.index', ['buscar' => 'Proveedor Eliminable']);

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $proveedor);
    }

    public function test_eliminar_categoria_marca_y_unidad_sin_productos(): void
    {
        $casos = [
            'categorias' => Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Categoría Eliminable', 'activo' => true]),
            'marcas' => Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Marca Eliminable', 'activo' => true]),
            'unidades' => UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Unidad Eliminable', 'codigo' => 'UEL', 'activo' => true]),
        ];

        foreach ($casos as $tipo => $registro) {
            $url = route("catalogos.{$tipo}.destroy", $registro);
            $pagina = route('catalogos.index');

            $this->assertVistaOfreceEliminar($pagina, $url);
            $this->assertEliminado($pagina, $url, $registro);
        }
    }

    public function test_eliminar_sucursal_secundaria(): void
    {
        $sucursal = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Eliminable',
            'es_principal' => false,
        ]);
        $url = route('sucursales.destroy', $sucursal);
        $pagina = route('sucursales.index');

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $sucursal);
    }

    public function test_eliminar_usuario(): void
    {
        $usuario = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'name' => 'Usuario Eliminable',
        ]);
        setPermissionsTeamId($this->empresa->id);
        $usuario->assignRole(RolSistema::VENDEDOR->value);
        $url = route('usuarios.destroy', $usuario);
        $pagina = route('usuarios.index', ['buscar' => 'Usuario Eliminable']);

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $usuario);
    }

    public function test_eliminar_impuesto_sin_productos(): void
    {
        $impuesto = Impuesto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Impuesto Eliminable',
            'codigo' => 'ELIM5',
            'porcentaje' => 5,
            'tipo' => TipoImpuesto::IVA,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $url = route('impuestos.destroy', $impuesto);
        $pagina = route('impuestos.index');

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $impuesto);
    }

    public function test_eliminar_resolucion_sin_documentos(): void
    {
        $resolucion = ResolucionFacturacion::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'numero_resolucion' => '18760000999',
            'prefijo' => 'ELIM',
            'rango_desde' => 1,
            'rango_hasta' => 100,
            'consecutivo_actual' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_vigencia' => now()->addYear(),
            'clave_tecnica' => 'clave-eliminable',
            'estado' => EstadoResolucionFacturacion::ACTIVA,
            'es_predeterminada' => false,
        ]);
        $url = route('resoluciones.destroy', $resolucion);
        $pagina = route('resoluciones.index');

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $resolucion);
    }

    public function test_eliminar_presentacion_de_producto(): void
    {
        $presentacion = ProductoPresentacion::create([
            'empresa_id' => $this->empresa->id,
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $this->producto->unidad_medida_id,
            'nombre' => 'Caja x12 Eliminable',
            'factor_conversion' => 12,
            'precio_venta' => 50000,
            'es_predeterminada' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $url = route('productos.presentaciones.destroy', [$this->producto, $presentacion]);
        $pagina = route('productos.presentaciones.index', $this->producto);

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $presentacion);
    }

    public function test_quitar_precio_especial_de_lista(): void
    {
        $detalle = ListaPrecioDetalle::create([
            'empresa_id' => $this->empresa->id,
            'lista_precio_id' => $this->lista->id,
            'producto_id' => $this->producto->id,
            'precio' => 12345,
        ]);
        $url = route('listas-precios.precios.destroy', [$this->lista, $detalle]);
        $pagina = route('listas-precios.show', $this->lista);

        $html = $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertStringContainsString("document.getElementById('deletePriceForm').submit()", html_entity_decode($html, ENT_QUOTES));

        $this->actingAs($this->admin)->from($pagina)->delete($url)
            ->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertModelMissing($detalle);
    }

    public function test_eliminar_lista_de_precios(): void
    {
        $lista = ListaPrecio::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Lista Eliminable',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $url = route('listas-precios.destroy', $lista);
        $pagina = route('listas-precios.index');

        $this->assertVistaOfreceEliminar($pagina, $url);
        $this->assertEliminado($pagina, $url, $lista);
    }

    public function test_el_modal_de_confirmacion_abre_con_su_propio_estado(): void
    {
        Cliente::factory()->for($this->empresa)->create(['razon_social' => 'Cliente Modal']);

        $html = $this->actingAs($this->admin)->get(route('clientes.index', ['buscar' => 'Cliente Modal']))->assertOk()->getContent();

        // Dentro de los métodos de Alpine, un "open = true" sin this. reasigna window.open y el modal nunca se abre
        $this->assertStringContainsString('this.open = true;', $html);
        $this->assertStringContainsString('this.open = false;', $html);
        $this->assertDoesNotMatchRegularExpression('/(?<![\w.])open = (true|false);/', $html);
    }

    public function test_sin_permiso_de_eliminar_no_se_muestra_el_boton_de_clientes(): void
    {
        $cliente = Cliente::factory()->for($this->empresa)->create(['razon_social' => 'Cliente Protegido']);
        $url = route('clientes.destroy', $cliente);

        // El cajero puede ver clientes pero no tiene el permiso clientes.eliminar
        $html = $this->actingAs($this->cajero)->get(route('clientes.index', ['buscar' => 'Cliente Protegido']))
            ->assertOk()->assertSee('Cliente Protegido')->getContent();
        $legible = $this->legible($html);
        $this->assertDoesNotMatchRegularExpression('#'.preg_quote($url, '#').'["\']#', $legible);

        $this->actingAs($this->cajero)->delete($url)->assertForbidden();
        $this->assertNotSoftDeleted($cliente);
    }

    public function test_el_mensaje_de_exito_se_muestra_una_sola_vez(): void
    {
        $cliente = Cliente::factory()->for($this->empresa)->create(['razon_social' => 'Cliente Mensaje']);

        $this->actingAs($this->admin)->from(route('clientes.index'))
            ->followingRedirects()
            ->delete(route('clientes.destroy', $cliente))
            ->assertOk()
            ->assertSeeTextInOrder(['Cliente eliminado con éxito.']);

        $html = $this->actingAs($this->admin)->withSession(['success' => 'Mensaje único de prueba'])
            ->get(route('cotizaciones.index'))->getContent();
        $this->assertSame(1, substr_count($html, 'Mensaje único de prueba'));
    }
}
