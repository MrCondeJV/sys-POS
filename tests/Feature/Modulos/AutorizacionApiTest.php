<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * La API v1 aplica los mismos permisos que la interfaz web: un token no da
 * acceso a lo que el rol del usuario no puede ver o hacer en pantalla.
 */
class AutorizacionApiTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    private function usuarioConRol(RolSistema $rol): User
    {
        $usuario = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresa->id);
        $usuario->assignRole($rol->value);

        return $usuario;
    }

    private function venta(): array
    {
        return [
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1, 'precio_unitario' => 35000]],
        ];
    }

    public function test_vendedor_no_accede_por_api_a_lo_que_no_tiene_permiso(): void
    {
        Sanctum::actingAs($this->usuarioConRol(RolSistema::VENDEDOR));

        $prohibidas = [
            'compras' => fn () => $this->getJson(route('api.v1.compras.index')),
            'detalle de compra' => fn () => $this->getJson(route('api.v1.compras.show', $this->compra)),
            'proveedores' => fn () => $this->getJson(route('api.v1.proveedores.index')),
            'inventario' => fn () => $this->getJson(route('api.v1.inventario.index')),
            'kardex' => fn () => $this->getJson(route('api.v1.inventario.kardex', ['producto_id' => $this->producto->id])),
            'resumen financiero' => fn () => $this->getJson(route('api.v1.reportes.resumen')),
            'estado de caja' => fn () => $this->getJson(route('api.v1.caja.estado')),
            'movimientos de caja' => fn () => $this->getJson(route('api.v1.caja.movimientos', $this->sesionCaja)),
            'abrir caja' => fn () => $this->postJson(route('api.v1.caja.abrir'), ['caja_id' => $this->sesionCaja->caja_id, 'monto_apertura' => 0]),
        ];

        $permitidas = [];
        foreach ($prohibidas as $caso => $peticion) {
            $status = $peticion()->status();
            if ($status !== 403) {
                $permitidas[] = "{$caso} ({$status})";
            }
        }

        $this->assertSame([], $permitidas, 'El vendedor accedió por API a: ' . implode(', ', $permitidas));
    }

    public function test_contador_no_registra_ventas_ni_clientes_por_api(): void
    {
        Sanctum::actingAs($this->usuarioConRol(RolSistema::CONTADOR));
        $ventasAntes = \App\Models\Venta::count();

        $this->postJson(route('api.v1.ventas.store'), $this->venta())->assertForbidden();
        $this->postJson(route('api.v1.clientes.store'), [
            'tipo_persona' => 'NATURAL',
            'tipo_documento' => 'CC',
            'numero_documento' => '1010101010',
            'razon_social' => 'Cliente por API',
        ])->assertForbidden();

        $this->assertSame($ventasAntes, \App\Models\Venta::count());
        $this->assertDatabaseMissing('clientes', ['numero_documento' => '1010101010']);
    }

    public function test_cajero_no_cierra_por_api_la_caja_de_otro_usuario_sin_permiso(): void
    {
        $vendedor = $this->usuarioConRol(RolSistema::VENDEDOR);
        Sanctum::actingAs($vendedor);

        $this->postJson(route('api.v1.caja.cerrar'), ['sesion_id' => $this->sesionCaja->id, 'monto_contado' => 0])
            ->assertForbidden();

        $this->assertSame(\App\Enums\EstadoSesionCaja::ABIERTA, $this->sesionCaja->fresh()->estado);
    }

    public function test_roles_con_permiso_conservan_el_acceso_por_api(): void
    {
        Sanctum::actingAs($this->usuarioConRol(RolSistema::CONTADOR));
        $this->getJson(route('api.v1.ventas.index'))->assertOk();
        $this->getJson(route('api.v1.compras.index'))->assertOk();
        $this->getJson(route('api.v1.reportes.resumen'))->assertOk();
        $this->getJson(route('api.v1.inventario.index'))->assertOk();

        Sanctum::actingAs($this->usuarioConRol(RolSistema::VENDEDOR));
        $this->getJson(route('api.v1.productos.index'))->assertOk();
        $this->getJson(route('api.v1.clientes.index'))->assertOk();
        $this->postJson(route('api.v1.ventas.store'), $this->venta())->assertSuccessful();
    }
}
