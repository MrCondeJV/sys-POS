<?php

namespace Tests\Feature\Modulos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Los datos de otra empresa no aparecen en los listados ni se pueden abrir
 * escribiendo su URL directamente.
 */
class AislamientoEntreEmpresasTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    private array $ajena;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
        $this->ajena = $this->crearEmpresaAjena();
    }

    public function test_listados_no_muestran_registros_de_otra_empresa(): void
    {
        $this->actingAs($this->admin)->get(route('clientes.index'))->assertOk()->assertDontSee('Cliente Secreto Ajeno');
        $this->actingAs($this->admin)->get(route('productos.index'))->assertOk()->assertDontSee('Producto Secreto Ajeno');
        $this->actingAs($this->admin)->get(route('inventario.index'))->assertOk()->assertDontSee('Producto Secreto Ajeno');
        $this->actingAs($this->admin)->get(route('proveedores.index'))->assertOk()->assertDontSee('Proveedor Secreto Ajeno');
        $this->actingAs($this->admin)->get(route('sucursales.index'))->assertOk()->assertDontSee('Sede Ajena Única');
    }

    public function test_buscador_no_encuentra_registros_de_otra_empresa(): void
    {
        $this->actingAs($this->admin)->get(route('clientes.index', ['buscar' => 'Secreto']))->assertOk()->assertDontSee('Cliente Secreto Ajeno');
        $this->actingAs($this->admin)->get(route('productos.index', ['buscar' => 'AJENO-01']))->assertOk()->assertDontSee('Producto Secreto Ajeno');
    }

    public function test_acceso_directo_por_url_a_registros_ajenos_es_bloqueado(): void
    {
        $urls = [
            route('clientes.edit', $this->ajena['cliente']->id),
            route('productos.edit', $this->ajena['producto']->id),
            route('inventario.kardex', $this->ajena['producto']->id),
            route('cartera.estado-cuenta', $this->ajena['cliente']->id),
        ];

        foreach ($urls as $url) {
            $status = $this->actingAs($this->admin)->get($url)->status();
            $this->assertContains($status, [403, 404], "{$url} respondió {$status} para un registro de otra empresa");
        }
    }

    public function test_no_se_pueden_modificar_ni_eliminar_registros_ajenos(): void
    {
        $this->actingAs($this->admin)
            ->put(route('clientes.update', $this->ajena['cliente']->id), ['razon_social' => 'Hackeado'])
            ->assertStatus(404);
        $this->actingAs($this->admin)->delete(route('productos.destroy', $this->ajena['producto']->id))->assertStatus(404);
        $this->actingAs($this->admin)->delete(route('proveedores.destroy', $this->ajena['proveedor']->id))->assertStatus(404);

        $this->assertDatabaseHas('clientes', ['id' => $this->ajena['cliente']->id, 'razon_social' => 'Cliente Secreto Ajeno']);
        $this->assertDatabaseHas('productos', ['id' => $this->ajena['producto']->id, 'deleted_at' => null]);
    }

    public function test_cajero_no_accede_a_modulos_administrativos(): void
    {
        foreach (['usuarios.index', 'empresa.perfil', 'impuestos.index', 'auditoria.index'] as $ruta) {
            $status = $this->actingAs($this->cajero)->get(route($ruta))->status();
            $this->assertContains($status, [302, 403], "El cajero abrió {$ruta} (status {$status})");
        }
    }

    public function test_cajero_no_puede_crear_editar_ni_eliminar_resoluciones_dian(): void
    {
        $this->actingAs($this->cajero)->get(route('resoluciones.create'))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('resoluciones.edit', $this->resolucion))->assertForbidden();

        $this->actingAs($this->cajero)
            ->put(route('resoluciones.update', $this->resolucion), [
                'numero_resolucion' => '18764999001',
                'prefijo' => 'HACK',
                'rango_desde' => 1,
                'rango_hasta' => 999999,
                'consecutivo_actual' => 0,
                'clave_tecnica' => 'x',
                'fecha_inicio' => now()->toDateString(),
                'fecha_vigencia' => now()->addYears(10)->toDateString(),
                'estado' => 'ACTIVA',
            ])
            ->assertForbidden();
        $this->actingAs($this->cajero)->delete(route('resoluciones.destroy', $this->resolucion))->assertForbidden();

        $this->assertNotSoftDeleted('resoluciones_facturacion', ['id' => $this->resolucion->id, 'prefijo' => 'FEMO']);
    }
}
