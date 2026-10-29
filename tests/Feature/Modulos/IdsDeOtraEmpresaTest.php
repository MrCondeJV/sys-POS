<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoAjusteListaPrecio;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\ListaPrecio;
use App\Models\ProductoPresentacion;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Los formularios y la API no pueden recibir IDs de registros de otra empresa
 * (productos, clientes, cajas, listas, sedes, unidades): deben rechazarse sin
 * crear ni modificar nada.
 */
class IdsDeOtraEmpresaTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    private array $ajena;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
        $this->ajena = $this->crearEmpresaAjena();

        $empresaAjenaId = $this->ajena['ajena']->id;
        $sedeAjenaId = $this->ajena['sede']->id;

        $cajaAjena = Caja::withoutGlobalScopes()->create([
            'empresa_id' => $empresaAjenaId,
            'sucursal_id' => $sedeAjenaId,
            'nombre' => 'Caja Ajena',
            'codigo' => 'CAJA-AJ',
            'estado' => EstadoCaja::ACTIVA,
        ]);
        $cajeroAjeno = User::factory()->create(['empresa_id' => $empresaAjenaId, 'sucursal_id' => $sedeAjenaId]);

        $this->ajena['caja'] = $cajaAjena;
        $this->ajena['sesion'] = CajaSesion::withoutGlobalScopes()->create([
            'empresa_id' => $empresaAjenaId,
            'sucursal_id' => $sedeAjenaId,
            'caja_id' => $cajaAjena->id,
            'user_id' => $cajeroAjeno->id,
            'fecha_apertura' => now(),
            'monto_apertura' => 50000,
            'estado' => EstadoSesionCaja::ABIERTA,
        ]);
        $this->ajena['lista'] = ListaPrecio::withoutGlobalScopes()->create([
            'empresa_id' => $empresaAjenaId,
            'nombre' => 'Lista Ajena',
            'codigo' => 'AJ-01',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO,
            'porcentaje_defecto' => 90,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->ajena['unidad'] = UnidadMedida::withoutGlobalScopes()->create([
            'empresa_id' => $empresaAjenaId,
            'nombre' => 'Unidad Ajena',
            'codigo' => 'AJN',
            'activo' => true,
        ]);
    }

    private function ventaPos(array $cambios = []): array
    {
        return array_replace_recursive([
            'caja_sesion_id' => $this->sesionCaja->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 100000,
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1, 'precio_unitario' => 35000]],
        ], $cambios);
    }

    private function assertRechazado($response, string $caso): void
    {
        $status = $response->baseResponse->getStatusCode();
        $esErrorValidacion = $status === 422 || ($status === 302 && session()->has('errors'));

        $this->assertTrue(
            $esErrorValidacion || in_array($status, [403, 404]),
            "{$caso}: se esperaba rechazo, se obtuvo status {$status}"
        );
    }

    // --- POS ---

    public function test_pos_rechaza_producto_de_otra_empresa(): void
    {
        $ventasAntes = Venta::withoutGlobalScopes()->count();

        $response = $this->actingAs($this->cajero)->postJson(route('pos.procesar'), $this->ventaPos([
            'items' => [['producto_id' => $this->ajena['producto']->id, 'cantidad' => 1, 'precio_unitario' => 2000]],
        ]));

        $this->assertRechazado($response, 'POS con producto ajeno');
        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    public function test_pos_rechaza_cliente_de_otra_empresa(): void
    {
        $ventasAntes = Venta::withoutGlobalScopes()->count();

        $response = $this->actingAs($this->cajero)->postJson(route('pos.procesar'), $this->ventaPos([
            'cliente_id' => $this->ajena['cliente']->id,
        ]));

        $this->assertRechazado($response, 'POS con cliente ajeno');
        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    public function test_pos_rechaza_caja_de_otra_empresa(): void
    {
        $ventasAntes = Venta::withoutGlobalScopes()->count();

        $response = $this->actingAs($this->cajero)->postJson(route('pos.procesar'), $this->ventaPos([
            'caja_sesion_id' => $this->ajena['sesion']->id,
        ]));

        $this->assertRechazado($response, 'POS con caja ajena');
        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    public function test_pos_rechaza_lista_de_precios_de_otra_empresa(): void
    {
        $ventasAntes = Venta::withoutGlobalScopes()->count();

        $response = $this->actingAs($this->cajero)->postJson(route('pos.procesar'), $this->ventaPos([
            'lista_precio_id' => $this->ajena['lista']->id,
        ]));

        $this->assertRechazado($response, 'POS con lista ajena');
        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    // --- Ventas (formulario web) ---

    public function test_venta_web_rechaza_registros_de_otra_empresa(): void
    {
        $ventasAntes = Venta::withoutGlobalScopes()->count();
        $base = [
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1, 'precio_unitario' => 35000]],
        ];

        $casos = [
            'producto ajeno' => ['items' => [['producto_id' => $this->ajena['producto']->id, 'cantidad' => 1, 'precio_unitario' => 2000]]],
            'cliente ajeno' => ['cliente_id' => $this->ajena['cliente']->id],
            'caja ajena' => ['caja_sesion_id' => $this->ajena['sesion']->id],
        ];

        foreach ($casos as $caso => $cambios) {
            $response = $this->actingAs($this->admin)->from(route('ventas.index'))
                ->post(route('ventas.store'), array_replace_recursive($base, $cambios));
            $this->assertRechazado($response, "Venta web con {$caso}");
        }

        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    // --- API v1 ---

    public function test_api_ventas_rechaza_registros_de_otra_empresa(): void
    {
        Sanctum::actingAs($this->admin);
        $ventasAntes = Venta::withoutGlobalScopes()->count();
        $base = [
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1, 'precio_unitario' => 35000]],
        ];

        $casos = [
            'sede ajena' => ['sucursal_id' => $this->ajena['sede']->id],
            'producto ajeno' => ['items' => [['producto_id' => $this->ajena['producto']->id, 'cantidad' => 1, 'precio_unitario' => 2000]]],
            'cliente ajeno' => ['cliente_id' => $this->ajena['cliente']->id],
            'caja ajena' => ['caja_sesion_id' => $this->ajena['sesion']->id],
        ];

        foreach ($casos as $caso => $cambios) {
            $response = $this->postJson(route('api.v1.ventas.store'), array_replace_recursive($base, $cambios));
            $this->assertRechazado($response, "API venta con {$caso}");
        }

        $this->assertSame($ventasAntes, Venta::withoutGlobalScopes()->count());
    }

    public function test_api_caja_rechaza_abrir_o_cerrar_cajas_de_otra_empresa(): void
    {
        Sanctum::actingAs($this->admin);

        $this->assertRechazado(
            $this->postJson(route('api.v1.caja.abrir'), ['caja_id' => $this->ajena['caja']->id, 'monto_apertura' => 1000]),
            'API abrir caja ajena'
        );
        $this->assertRechazado(
            $this->postJson(route('api.v1.caja.cerrar'), ['sesion_id' => $this->ajena['sesion']->id, 'monto_contado' => 0]),
            'API cerrar caja ajena'
        );

        $this->assertSame(EstadoSesionCaja::ABIERTA, $this->ajena['sesion']->fresh()->estado);
    }

    // --- Usuarios y presentaciones ---

    public function test_usuario_no_puede_asignarse_a_sede_de_otra_empresa(): void
    {
        $response = $this->actingAs($this->admin)->from(route('usuarios.create'))->post(route('usuarios.store'), [
            'name' => 'Usuario Sede Ajena',
            'email' => 'sede.ajena@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sucursal_id' => $this->ajena['sede']->id,
            'rol' => 'CAJERO',
            'estado' => 'ACTIVO',
        ]);

        $this->assertRechazado($response, 'Usuario con sede ajena');
        $this->assertDatabaseMissing('users', ['email' => 'sede.ajena@test.com']);

        $response = $this->actingAs($this->admin)->from(route('usuarios.edit', $this->cajero))->put(route('usuarios.update', $this->cajero), [
            'name' => $this->cajero->name,
            'email' => $this->cajero->email,
            'sucursal_id' => $this->ajena['sede']->id,
            'rol' => 'CAJERO',
            'estado' => 'ACTIVO',
        ]);

        $this->assertRechazado($response, 'Editar usuario con sede ajena');
        $this->assertSame($this->sucursal->id, $this->cajero->fresh()->sucursal_id);
    }

    public function test_presentacion_rechaza_unidad_de_medida_de_otra_empresa(): void
    {
        $response = $this->actingAs($this->admin)->from(route('productos.presentaciones.index', $this->producto))
            ->post(route('productos.presentaciones.store', $this->producto), [
                'nombre' => 'Caja x6',
                'unidad_medida_id' => $this->ajena['unidad']->id,
                'factor_conversion' => 6,
                'precio_venta' => 200000,
            ]);

        $this->assertRechazado($response, 'Presentación con unidad ajena');
        $this->assertSame(0, ProductoPresentacion::withoutGlobalScopes()->count());
    }
}
