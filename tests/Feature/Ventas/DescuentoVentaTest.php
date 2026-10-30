<?php

namespace Tests\Feature\Ventas;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Descuento en valor fijo sobre el total de una venta de contado: debe bajar exactamente el total cobrado
 * y quedar coherente en IVA, caja, devoluciones y reportes.
 */
class DescuentoVentaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Sucursal $sucursal;
    protected User $admin;
    protected User $cajero;
    protected Cliente $cliente;
    protected Producto $taladro;   // 100.000 + IVA 19%
    protected Producto $libro;     // 30.000 exento
    protected CajaSesion $sesion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresa = Empresa::factory()->create();
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);
        $this->sucursal = Sucursal::factory()->create(['empresa_id' => $this->empresa->id, 'es_principal' => true]);

        $this->admin = User::factory()->create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id]);
        setPermissionsTeamId($this->empresa->id);
        $this->admin->assignRole(RolSistema::ADMIN_EMPRESA->value);
        $this->cajero = User::factory()->create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id]);
        $this->cajero->assignRole(RolSistema::CAJERO->value);

        $this->cliente = Cliente::factory()->for($this->empresa)->create(['razon_social' => 'Cliente Descuento']);

        CompanyContext::setCompany($this->empresa);
        $this->taladro = Producto::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Taladro', 'codigo' => 'TAL',
            'precio_compra' => 60000, 'precio_venta' => 100000, 'iva' => 19, 'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->libro = Producto::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'Manual', 'codigo' => 'MAN',
            'precio_compra' => 20000, 'precio_venta' => 30000, 'iva' => 0, 'estado' => EstadoGeneral::ACTIVO,
        ]);
        foreach ([$this->taladro, $this->libro] as $p) {
            Inventario::create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id, 'producto_id' => $p->id, 'stock' => 20, 'stock_minimo' => 1]);
        }
        $caja = Caja::create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id, 'nombre' => 'Caja 1', 'codigo' => 'C1', 'estado' => EstadoCaja::ACTIVA]);
        CompanyContext::clear();

        $this->sesion = app(AbrirCajaAction::class)->execute($caja, $this->cajero, 0);
    }

    /** 1 taladro (119.000 con IVA) + 1 manual (30.000) = 149.000 antes del descuento. */
    private function vender(array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->cajero)->postJson(route('pos.procesar'), array_merge([
            'caja_sesion_id' => $this->sesion->id,
            'cliente_id' => $this->cliente->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 200000,
            'items' => [
                ['producto_id' => $this->taladro->id, 'cantidad' => 1, 'precio_unitario' => 100000],
                ['producto_id' => $this->libro->id, 'cantidad' => 1, 'precio_unitario' => 30000],
            ],
        ], $extra));
    }

    public function test_descuento_baja_exactamente_el_total_y_recalcula_el_iva(): void
    {
        $respuesta = $this->vender(['descuento_global' => 10000, 'motivo_descuento' => 'Cliente frecuente']);
        $respuesta->assertOk()->assertJson(['success' => true]);

        $venta = Venta::withoutGlobalScopes()->with('detalles')->findOrFail($respuesta->json('venta_id'));

        // 149.000 - 10.000 = 139.000 exactos
        $this->assertEquals(139000.00, (float) $venta->total);
        $this->assertEquals(61000.00, (float) $venta->cambio);
        $this->assertEquals(10000.00, (float) $venta->descuento_global);
        $this->assertSame('Cliente frecuente', $venta->motivo_descuento);

        // Las líneas cuadran con la cabecera
        $this->assertEquals((float) $venta->total, round((float) $venta->detalles->sum('total'), 2));
        $this->assertEquals((float) $venta->impuesto, round((float) $venta->detalles->sum('impuesto_monto'), 2));
        $this->assertEquals(
            (float) $venta->total,
            round((float) $venta->subtotal - (float) $venta->descuento + (float) $venta->impuesto, 2)
        );

        // El IVA se calcula sobre la base ya descontada (menor que el IVA sin descuento de 19.000)
        $this->assertLessThan(19000, (float) $venta->impuesto);
        $taladro = $venta->detalles->firstWhere('producto_id', $this->taladro->id);
        $this->assertEquals(round((float) $taladro->subtotal * 0.19, 2), (float) $taladro->impuesto_monto);
    }

    public function test_la_caja_registra_el_total_con_descuento_y_queda_auditado(): void
    {
        $ventaId = $this->vender(['descuento_global' => 10000, 'motivo_descuento' => 'Producto con detalle estético'])
            ->assertOk()->json('venta_id');

        $ingreso = MovimientoCaja::withoutGlobalScopes()->where('caja_sesion_id', $this->sesion->id)->where('comprobante', 'like', 'VTA-%')->sum('monto');
        $this->assertEquals(139000.00, (float) $ingreso);

        $this->assertDatabaseHas('auditorias', ['accion' => 'DESCUENTO_VENTA', 'auditable_id' => $ventaId]);
    }

    public function test_pago_mixto_con_descuento_valida_contra_el_total_descontado(): void
    {
        $base = ['descuento_global' => 9000, 'motivo_descuento' => 'Ajuste comercial', 'metodo_pago' => 'MIXTO', 'pago_con' => null];

        // 140.000 cubre el total descontado (149.000 - 9.000)
        $this->vender($base + ['pagos' => [
            ['metodo_pago' => 'EFECTIVO', 'monto' => 40000],
            ['metodo_pago' => 'TARJETA', 'monto' => 100000],
        ]])->assertOk()->assertJson(['total' => 140000.00]);

        // 139.000 ya no alcanza
        $this->vender($base + ['pagos' => [
            ['metodo_pago' => 'EFECTIVO', 'monto' => 39000],
            ['metodo_pago' => 'TARJETA', 'monto' => 100000],
        ]])->assertStatus(422);
    }

    public function test_descuento_no_aplica_a_credito(): void
    {
        $this->vender([
            'tipo_pago' => 'CREDITO', 'metodo_pago' => 'CREDITO', 'pago_con' => null,
            'descuento_global' => 10000, 'motivo_descuento' => 'No debería',
        ])->assertStatus(422)->assertJsonFragment(['error' => 'El descuento sobre el total solo aplica a ventas de contado.']);

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_descuento_exige_motivo(): void
    {
        $this->vender(['descuento_global' => 5000])->assertStatus(422)
            ->assertJsonFragment(['error' => 'Debes indicar el motivo del descuento.']);
        $this->vender(['descuento_global' => 5000, 'motivo_descuento' => '   '])->assertStatus(422);

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_descuento_no_puede_igualar_o_superar_el_total(): void
    {
        $this->vender(['descuento_global' => 149000, 'motivo_descuento' => 'Regalo'])->assertStatus(422);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_sin_descuento_la_venta_no_cambia(): void
    {
        $venta = Venta::withoutGlobalScopes()->findOrFail($this->vender(['descuento_global' => 0])->assertOk()->json('venta_id'));

        $this->assertEquals(149000.00, (float) $venta->total);
        $this->assertEquals(0.0, (float) $venta->descuento_global);
        $this->assertNull($venta->motivo_descuento);
    }

    public function test_devolucion_de_venta_con_descuento_reintegra_lo_cobrado_y_reingresa_al_costo(): void
    {
        $venta = Venta::withoutGlobalScopes()->with('detalles')
            ->findOrFail($this->vender(['descuento_global' => 10000, 'motivo_descuento' => 'Cliente frecuente'])->json('venta_id'));
        $lineaTaladro = $venta->detalles->firstWhere('producto_id', $this->taladro->id);

        CompanyContext::setCompany($this->empresa);
        $devolucion = app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->cajero->id,
            items: [['venta_detalle_id' => $lineaTaladro->id, 'cantidad' => 1]],
            tipoReintegro: TipoReintegroDevolucion::EFECTIVO,
            cajaSesionId: $this->sesion->id,
            motivo: 'Defectuoso'
        );

        // Se devuelve lo que se cobró por el taladro (con su parte del descuento), no los 119.000 de lista
        $this->assertEquals((float) $lineaTaladro->total, (float) $devolucion->total);
        $this->assertLessThan(119000, (float) $devolucion->total);

        $egreso = MovimientoCaja::withoutGlobalScopes()->where('caja_sesion_id', $this->sesion->id)->where('comprobante', $devolucion->numero_devolucion)->value('monto');
        $this->assertEquals((float) $lineaTaladro->total, (float) $egreso);

        // El inventario vuelve valorizado al costo (60.000), no al precio de venta
        $reingreso = MovimientoInventario::withoutGlobalScopes()->where('referencia', $devolucion->numero_devolucion)->firstOrFail();
        $this->assertEquals(60000.00, (float) $reingreso->costo_unitario);
    }

    public function test_reporte_de_impuestos_usa_la_base_neta_de_descuentos(): void
    {
        $venta = Venta::withoutGlobalScopes()
            ->findOrFail($this->vender(['descuento_global' => 10000, 'motivo_descuento' => 'Cliente frecuente'])->json('venta_id'));

        $respuesta = $this->actingAs($this->admin)->get(route('reportes.impuestos'))->assertOk();

        // Base gravable + IVA = total cobrado
        $base = (float) $venta->subtotal - (float) $venta->descuento;
        $respuesta->assertSee('$'.number_format($base, 2));
        $this->assertEquals((float) $venta->total, round($base + (float) $venta->impuesto, 2));
    }

    public function test_detalle_de_venta_muestra_descuento_y_motivo(): void
    {
        $venta = Venta::withoutGlobalScopes()
            ->findOrFail($this->vender(['descuento_global' => 10000, 'motivo_descuento' => 'Cliente frecuente'])->json('venta_id'));

        $this->actingAs($this->admin)->get(route('ventas.show', $venta))
            ->assertOk()
            ->assertSee('Descuento sobre el total')
            ->assertSee('Cliente frecuente')
            ->assertSee('10,000.00');
    }
}
