<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\ProductoPresentacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Acciones de escritura con datos válidos que no tenían ningún test:
 * deben guardar realmente el cambio, no solo responder con éxito.
 */
class FlujosDeEscrituraTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    private function superAdmin(): User
    {
        $superAdmin = $this->crearSuperAdmin();
        $this->assertTrue($superAdmin->isSuperAdmin());

        return $superAdmin;
    }

    private function datosEmpresa(array $extra = []): array
    {
        return array_merge([
            'nombre_comercial' => 'Droguería Nueva Esperanza',
            'razon_social' => 'Droguería Nueva Esperanza SAS',
            'tipo_documento' => 'NIT',
            'nit' => '901999888',
            'dv' => '1',
            'email' => 'contacto@nuevaesperanza.test',
            'ciudad' => 'Medellín',
            'moneda' => 'COP',
            'simbolo_moneda' => '$',
        ], $extra);
    }

    public function test_super_admin_crea_empresa_con_sede_y_administrador(): void
    {
        $this->actingAs($this->superAdmin())
            ->post(route('empresas.store'), $this->datosEmpresa([
                'sucursal_nombre' => 'Sede Centro Medellín',
                'admin_name' => 'Laura Restrepo',
                'admin_email' => 'laura@nuevaesperanza.test',
                'admin_password' => 'password123',
                'admin_password_confirmation' => 'password123',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $empresa = Empresa::where('nit', '901999888')->firstOrFail();
        $this->assertDatabaseHas('sucursales', ['empresa_id' => $empresa->id, 'nombre' => 'Sede Centro Medellín']);

        $admin = User::where('email', 'laura@nuevaesperanza.test')->firstOrFail();
        $this->assertSame($empresa->id, $admin->empresa_id);
        setPermissionsTeamId($empresa->id);
        $this->assertTrue($admin->fresh()->hasRole(RolSistema::ADMIN_EMPRESA->value));
    }

    public function test_super_admin_edita_empresa(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('empresas.update', $this->empresa), $this->datosEmpresa([
                'nombre_comercial' => 'Ferretería Módulos Renovada',
                'nit' => $this->empresa->nit,
                'estado' => 'ACTIVO',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('empresas.index'));

        $this->assertSame('Ferretería Módulos Renovada', $this->empresa->fresh()->nombre_comercial);
    }

    public function test_super_admin_suspende_y_reactiva_empresa(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)->post(route('empresas.toggle-estado', $this->empresa))->assertRedirect();
        $this->assertSame(EstadoGeneral::INACTIVO, $this->empresa->fresh()->estado);

        $this->actingAs($superAdmin)->post(route('empresas.toggle-estado', $this->empresa))->assertRedirect();
        $this->assertSame(EstadoGeneral::ACTIVO, $this->empresa->fresh()->estado);
    }

    public function test_admin_de_empresa_no_puede_crear_ni_suspender_empresas(): void
    {
        $this->actingAs($this->admin)->post(route('empresas.store'), $this->datosEmpresa())->assertForbidden();
        $this->actingAs($this->admin)->post(route('empresas.toggle-estado', $this->empresa))->assertForbidden();
    }

    public function test_admin_edita_lista_de_precios(): void
    {
        $this->actingAs($this->admin)
            ->put(route('listas-precios.update', $this->lista), [
                'nombre' => 'Mayoristas Premium',
                'codigo' => 'MAY-01',
                'tipo_ajuste' => 'PORCENTAJE_DESCUENTO',
                'porcentaje_defecto' => 15,
                'estado' => 'ACTIVO',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $lista = $this->lista->fresh();
        $this->assertSame('Mayoristas Premium', $lista->nombre);
        $this->assertEquals(15, (float) $lista->porcentaje_defecto);
    }

    public function test_admin_quita_precio_especifico_de_una_lista(): void
    {
        $detalle = $this->lista->detalles()->create([
            'empresa_id' => $this->empresa->id,
            'producto_id' => $this->producto->id,
            'precio' => 31000,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('listas-precios.precios.destroy', [$this->lista, $detalle]))
            ->assertRedirect();

        $this->assertNull($detalle->fresh());
    }

    public function test_admin_elimina_presentacion_de_producto(): void
    {
        $presentacion = ProductoPresentacion::create([
            'empresa_id' => $this->empresa->id,
            'producto_id' => $this->producto->id,
            'nombre' => 'Caja x12',
            'factor_conversion' => 12,
            'precio_venta' => 400000,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('productos.presentaciones.destroy', [$this->producto, $presentacion]))
            ->assertRedirect(route('productos.presentaciones.index', $this->producto));

        $this->assertSoftDeletedOrMissing($presentacion);
    }

    public function test_no_se_puede_eliminar_presentacion_usando_otro_producto_en_la_url(): void
    {
        $otroProducto = \App\Models\Producto::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => 'OTRO-01',
            'nombre' => 'Destornillador',
            'precio_compra' => 5000,
            'precio_venta' => 9000,
            'stock' => 0,
            'stock_minimo' => 1,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $presentacion = ProductoPresentacion::create([
            'empresa_id' => $this->empresa->id,
            'producto_id' => $this->producto->id,
            'nombre' => 'Caja x12',
            'factor_conversion' => 12,
            'precio_venta' => 400000,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('productos.presentaciones.destroy', [$otroProducto, $presentacion]))
            ->assertNotFound();

        $this->assertNotNull($presentacion->fresh());
    }

    private function assertSoftDeletedOrMissing(ProductoPresentacion $presentacion): void
    {
        $registro = ProductoPresentacion::withTrashed()->find($presentacion->id);
        $this->assertTrue($registro === null || $registro->trashed(), 'La presentación no se eliminó');
    }
}
