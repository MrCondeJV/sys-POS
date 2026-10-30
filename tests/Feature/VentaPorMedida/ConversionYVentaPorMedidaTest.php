<?php

namespace Tests\Feature\VentaPorMedida;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Console\Commands\ConvertirProductosAMedida;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\ListaPrecio;
use App\Models\ListaPrecioDetalle;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Pisos y baldosas: productos que estaban por caja pasan a m² con la caja como presentación,
 * y se pueden vender por caja o por m² (con fracciones) manteniendo inventario, costo y devoluciones.
 */
class ConversionYVentaPorMedidaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected Sucursal $sucursal;

    protected User $admin;

    protected User $cajero;

    protected UnidadMedida $m2;

    protected UnidadMedida $caja;

    protected Producto $piso;

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

        CompanyContext::setCompany($this->empresa);
        $this->m2 = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Metro cuadrado', 'codigo' => 'M2', 'permite_decimales' => true, 'activo' => true]);
        $this->caja = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Caja', 'codigo' => 'CAJ', 'activo' => true]);

        // Como está hoy en producción: unidad Caja, stock en cajas (con fracción), precio y costo por caja
        $this->piso = Producto::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'PISO ARABELLA 60X60 (XCAJA)', 'codigo' => 'PA6060',
            'unidad_medida_id' => $this->caja->id, 'precio_compra' => 79700, 'precio_venta' => 112000,
            'iva' => 19, 'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id, 'producto_id' => $this->piso->id, 'stock' => 10.5, 'stock_minimo' => 2]);

        $cajaPos = Caja::create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id, 'nombre' => 'Caja 1', 'codigo' => 'C1', 'estado' => EstadoCaja::ACTIVA]);
        CompanyContext::clear();
        $this->sesion = app(AbrirCajaAction::class)->execute($cajaPos, $this->cajero, 0);
    }

    private function convertir(bool $ejecutar = true): PendingCommand
    {
        return $this->artisan('app:convertir-productos-a-medida', array_filter([
            'empresa_id' => $this->empresa->id,
            '--item' => ['PA6060=1.44'],
            '--ejecutar' => $ejecutar,
        ]));
    }

    private function vender(array $item): TestResponse
    {
        return $this->actingAs($this->cajero)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $this->sesion->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 1000000,
            'items' => [array_merge(['producto_id' => $this->piso->id, 'precio_unitario' => 0], $item)],
        ]);
    }

    private function stock(): float
    {
        return (float) Inventario::withoutGlobalScopes()->where('producto_id', $this->piso->id)->value('stock');
    }

    public function test_simulacion_no_modifica_nada(): void
    {
        $this->convertir(false)->expectsOutputToContain('Simulación')->assertSuccessful();

        $this->assertEquals(10.5, $this->stock());
        $this->assertEquals($this->caja->id, $this->piso->fresh()->unidad_medida_id);
        $this->assertDatabaseCount('producto_presentaciones', 0);
    }

    public function test_conversion_pasa_stock_y_costo_a_m2_y_conserva_la_caja_como_presentacion(): void
    {
        $this->convertir()->assertSuccessful();

        $piso = $this->piso->fresh();
        $this->assertEquals($this->m2->id, $piso->unidad_medida_id);
        $this->assertEquals(0.0, (float) $piso->precio_venta, 'El precio por m² lo define el negocio');
        $this->assertEquals(round(79700 / 1.44, 2), (float) $piso->precio_compra, 'Costo por m²');

        // 10,5 cajas × 1,44 = 15,12 m² (sin perder decimales)
        $this->assertEquals(15.12, $this->stock());
        $this->assertEquals(2.88, (float) Inventario::withoutGlobalScopes()->where('producto_id', $piso->id)->value('stock_minimo'));

        $caja = ProductoPresentacion::withoutGlobalScopes()->where('producto_id', $piso->id)->firstOrFail();
        $this->assertSame('Caja', $caja->nombre);
        $this->assertEquals(1.44, (float) $caja->factor_conversion);
        $this->assertEquals(112000.00, (float) $caja->precio_venta);

        $mov = MovimientoInventario::withoutGlobalScopes()->where('referencia', ConvertirProductosAMedida::REFERENCIA)->firstOrFail();
        $this->assertEquals(10.5, (float) $mov->stock_anterior);
        $this->assertEquals(15.12, (float) $mov->stock_posterior);
        $this->assertDatabaseHas('auditorias', ['accion' => 'CONVERSION_UNIDAD', 'auditable_id' => $piso->id]);
    }

    public function test_archivo_csv_calcula_m2_desde_piezas_y_deja_pendientes_los_vacios(): void
    {
        CompanyContext::setCompany($this->empresa);
        $pared = Producto::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'PARED CUSCO GREY 30X60 (XCAJA)', 'codigo' => 'PCG3060',
            'unidad_medida_id' => $this->caja->id, 'precio_compra' => 60000, 'precio_venta' => 94000, 'estado' => EstadoGeneral::ACTIVO,
        ]);
        $sinDato = Producto::create([
            'empresa_id' => $this->empresa->id, 'nombre' => 'PISO PIZARRA NEGRO 50X50 (XCAJA)', 'codigo' => 'PPN5050',
            'unidad_medida_id' => $this->caja->id, 'precio_venta' => 146300, 'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create(['empresa_id' => $this->empresa->id, 'sucursal_id' => $this->sucursal->id, 'producto_id' => $pared->id, 'stock' => 8, 'stock_minimo' => 0]);
        CompanyContext::clear();

        $archivo = tempnam(sys_get_temp_dir(), 'pisos').'.csv';
        file_put_contents($archivo, implode("\n", [
            'codigo;m2_por_caja;producto;medida_cm;m2_por_pieza;piezas_por_caja;stock_cajas_actual',
            'PA6060;;PISO ARABELLA;60x60;0,36;4;10',     // 4 piezas × 0,36 = 1,44
            'PCG3060;1,62;PARED CUSCO GREY;30x60;0,18;;8', // m² por caja escrito directo
            'PPN5050;;PISO PIZARRA NEGRO;50x50;0,25;;0',   // sin dato: pendiente
        ]));

        $this->artisan('app:convertir-productos-a-medida', ['empresa_id' => $this->empresa->id, '--archivo' => $archivo, '--ejecutar' => true])
            ->expectsOutputToContain('Pendientes sin m² por caja (no se convierten todavía): PPN5050')
            ->assertSuccessful();

        $this->assertEquals(1.44, (float) ProductoPresentacion::withoutGlobalScopes()->where('producto_id', $this->piso->id)->value('factor_conversion'));
        $this->assertEquals(15.12, $this->stock());
        $this->assertEquals(round(8 * 1.62, 4), (float) Inventario::withoutGlobalScopes()->where('producto_id', $pared->id)->value('stock'));
        $this->assertEquals($this->caja->id, $sinDato->fresh()->unidad_medida_id, 'Sin dato queda como estaba');
        @unlink($archivo);
    }

    public function test_conversion_es_idempotente(): void
    {
        $this->convertir()->assertSuccessful();
        $this->convertir()->expectsOutputToContain('No hay productos para convertir')->assertSuccessful();

        $this->assertEquals(15.12, $this->stock());
        $this->assertDatabaseCount('producto_presentaciones', 1);
    }

    public function test_vender_por_caja_descuenta_m2_y_registra_costo_de_la_caja(): void
    {
        $this->convertir();
        $caja = ProductoPresentacion::withoutGlobalScopes()->where('producto_id', $this->piso->id)->firstOrFail();

        $venta = Venta::withoutGlobalScopes()->with('detalles')
            ->findOrFail($this->vender(['cantidad' => 2, 'presentacion_id' => $caja->id])->assertOk()->json('venta_id'));

        $this->assertEquals(round(2 * 112000 * 1.19, 2), (float) $venta->total);
        $this->assertEquals(round(15.12 - 2.88, 4), $this->stock());

        $linea = $venta->detalles->first();
        $this->assertEquals(round(79700 / 1.44 * 1.44, 2), (float) $linea->costo_unitario, 'Costo por caja vendida');
        $this->assertEquals(1.44, (float) $linea->factor_conversion);
    }

    public function test_no_se_vende_por_m2_mientras_no_tenga_precio(): void
    {
        $this->convertir();

        $this->vender(['cantidad' => 2.5])->assertStatus(422)
            ->assertJsonFragment(['error' => "El producto 'PISO ARABELLA 60X60 (XCAJA)' aún no tiene precio por M2. Véndelo por su presentación (ej. caja) o define el precio por M2."]);
        $this->assertEquals(15.12, $this->stock());
    }

    public function test_con_precio_definido_se_venden_fracciones_de_m2(): void
    {
        $this->convertir();
        $this->piso->fresh()->update(['precio_venta' => 85000]);

        $venta = Venta::withoutGlobalScopes()
            ->findOrFail($this->vender(['cantidad' => 2.35])->assertOk()->json('venta_id'));

        $this->assertEquals(round(2.35 * 85000 * 1.19, 2), (float) $venta->total);
        $this->assertEquals(round(15.12 - 2.35, 4), $this->stock());
    }

    public function test_devolucion_de_una_caja_reingresa_sus_m2_al_costo(): void
    {
        $this->convertir();
        $caja = ProductoPresentacion::withoutGlobalScopes()->where('producto_id', $this->piso->id)->firstOrFail();
        $venta = Venta::withoutGlobalScopes()->with('detalles')
            ->findOrFail($this->vender(['cantidad' => 2, 'presentacion_id' => $caja->id])->json('venta_id'));

        CompanyContext::setCompany($this->empresa);
        $devolucion = app(RegistrarDevolucionAction::class)->execute(
            venta: $venta,
            userId: $this->cajero->id,
            items: [['venta_detalle_id' => $venta->detalles->first()->id, 'cantidad' => 1]],
            tipoReintegro: TipoReintegroDevolucion::EFECTIVO,
            cajaSesionId: $this->sesion->id,
            motivo: 'Sobró'
        );

        // Vendió 2 cajas (2,88 m²) y devolvió 1 caja = 1,44 m²
        $this->assertEquals(round(15.12 - 2.88 + 1.44, 4), $this->stock());
        $reingreso = MovimientoInventario::withoutGlobalScopes()->where('referencia', $devolucion->numero_devolucion)->firstOrFail();
        $this->assertEquals(1.44, (float) $reingreso->cantidad);
        $this->assertEquals(round(79700 / 1.44, 2), (float) $reingreso->costo_unitario);
    }

    public function test_bloquea_productos_con_precios_especiales_en_listas(): void
    {
        $lista = ListaPrecio::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Mayoristas', 'estado' => EstadoGeneral::ACTIVO]);
        ListaPrecioDetalle::create(['empresa_id' => $this->empresa->id, 'lista_precio_id' => $lista->id, 'producto_id' => $this->piso->id, 'precio' => 100000]);

        $this->convertir()->expectsOutputToContain('No hay productos para convertir')->assertSuccessful();
        $this->assertEquals(10.5, $this->stock());
    }

    public function test_catalogo_guarda_si_la_unidad_permite_fracciones(): void
    {
        CompanyContext::setCompany($this->empresa);
        $this->actingAs($this->admin)->post(route('catalogos.unidades.store'), [
            'codigo' => 'ML', 'nombre' => 'Metro lineal', 'permite_decimales' => '1',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('unidades_medida', ['codigo' => 'ML', 'permite_decimales' => true]);

        $this->actingAs($this->admin)->put(route('catalogos.unidades.update', $this->caja), ['codigo' => 'CAJ', 'nombre' => 'Caja'])
            ->assertSessionHas('success');
        $this->assertFalse((bool) $this->caja->fresh()->permite_decimales);
    }

    // ---------------- Fase 2: POS, cotizaciones y documentos ----------------

    private function presentacionCaja(): ProductoPresentacion
    {
        return ProductoPresentacion::withoutGlobalScopes()->where('producto_id', $this->piso->id)->firstOrFail();
    }

    public function test_el_pos_envia_las_presentaciones_y_si_la_unidad_permite_fracciones(): void
    {
        $this->convertir();
        $caja = $this->presentacionCaja();

        $html = $this->actingAs($this->cajero)->get(route('pos.index'))->assertOk()->getContent();
        $legible = str_replace(['\\\\/', '\/', '\u0022'], ['/', '/', '"'], html_entity_decode($html, ENT_QUOTES));

        $this->assertStringContainsString('"permite_decimales":true', $legible);
        $this->assertStringContainsString('"presentaciones":[{"id":'.$caja->id.',"nombre":"Caja","factor":1.44,"precio":112000', $legible);
        $this->assertStringContainsString('¿Cómo se vende?', $html);
    }

    public function test_cotizacion_por_caja_y_por_m2(): void
    {
        $this->convertir();
        $caja = $this->presentacionCaja();
        $this->piso->fresh()->update(['precio_venta' => 85000]);

        $this->actingAs($this->admin)->post(route('cotizaciones.store'), [
            'items' => [
                ['producto_id' => $this->piso->id, 'cantidad' => 3, 'presentacion_id' => $caja->id],
                ['producto_id' => $this->piso->id, 'cantidad' => 0.75],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $cotizacion = Cotizacion::withoutGlobalScopes()->with('detalles')->latest('id')->firstOrFail();
        $porCaja = $cotizacion->detalles->firstWhere('presentacion_id', $caja->id);
        $porM2 = $cotizacion->detalles->firstWhere('presentacion_id', null);

        $this->assertEquals(112000.00, (float) $porCaja->precio_unitario);
        $this->assertEquals(1.44, (float) $porCaja->factor_conversion);
        $this->assertEquals(85000.00, (float) $porM2->precio_unitario);
        $this->assertEquals(0.75, (float) $porM2->cantidad);

        $this->actingAs($this->admin)->get(route('cotizaciones.show', $cotizacion))->assertOk()->assertSee('Caja (1,44 M2)');
        $pdf = $this->actingAs($this->admin)->get(route('cotizaciones.pdf', $cotizacion));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_cotizacion_por_m2_sin_precio_se_rechaza(): void
    {
        $this->convertir();

        $this->actingAs($this->admin)->post(route('cotizaciones.store'), [
            'items' => [['producto_id' => $this->piso->id, 'cantidad' => 2]],
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('cotizaciones', 0);
    }

    public function test_convertir_cotizacion_por_caja_conserva_la_presentacion(): void
    {
        $this->convertir();
        $caja = $this->presentacionCaja();
        $this->actingAs($this->admin)->post(route('cotizaciones.store'), [
            'items' => [['producto_id' => $this->piso->id, 'cantidad' => 2, 'presentacion_id' => $caja->id]],
        ]);
        $cotizacion = Cotizacion::withoutGlobalScopes()->latest('id')->firstOrFail();

        // El POS recibe la presentación para precargar la línea en cajas
        $html = $this->actingAs($this->cajero)->get(route('pos.index', ['cotizacion' => $cotizacion->id]))->assertOk()->getContent();
        $legible = str_replace(['\\\\/', '\/', '\u0022'], ['/', '/', '"'], html_entity_decode($html, ENT_QUOTES));
        $this->assertStringContainsString('"presentacion_id":'.$caja->id, $legible);

        $this->actingAs($this->cajero)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $this->sesion->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 1000000,
            'cotizacion_id' => $cotizacion->id,
            'items' => [['producto_id' => $this->piso->id, 'cantidad' => 2, 'presentacion_id' => $caja->id, 'precio_unitario' => 0]],
        ])->assertOk();

        $this->assertEquals(round(15.12 - 2.88, 4), $this->stock());
    }

    public function test_documentos_muestran_la_unidad_vendida(): void
    {
        $this->convertir();
        $caja = $this->presentacionCaja();
        $venta = Venta::withoutGlobalScopes()
            ->findOrFail($this->vender(['cantidad' => 2, 'presentacion_id' => $caja->id])->json('venta_id'));

        $this->actingAs($this->admin)->get(route('ventas.show', $venta))->assertOk()->assertSee('2 Caja (1,44 M2)');
        $this->actingAs($this->admin)->get(route('imprimir.venta', $venta))->assertOk()->assertSee('Caja (1,44 M2)');
        $this->actingAs($this->admin)->get(route('imprimir.venta', ['venta' => $venta, 'formato' => 'carta']))->assertOk()->assertSee('Caja (1,44 M2)');

        $pdf = $this->actingAs($this->admin)->get(route('ventas.pdf', $venta));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_formulario_de_producto_indica_precio_por_m2(): void
    {
        $this->actingAs($this->admin)->get(route('productos.create'))
            ->assertOk()
            ->assertSee('m2_por_caja')
            ->assertSee('precio_venta_m2')
            ->assertSee('x-show="Number(m2PorCaja) > 0"', false);
        $this->actingAs($this->admin)->get(route('productos.edit', $this->piso))
            ->assertOk()
            ->assertSee('m2_por_caja')
            ->assertSee('precio_venta_m2');

        $this->convertir();

        $this->actingAs($this->admin)->get(route('productos.edit', $this->piso))
            ->assertOk()
            ->assertDontSee('m2_por_caja')
            ->assertSee('Precio por M2 ($)', false);
    }

    public function test_registrar_producto_con_factor_convierte_stock_y_costo_automaticamente(): void
    {
        $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'PISO NUEVO POR CAJA',
            'codigo' => 'PISO-NUEVO',
            'unidad_medida_id' => $this->caja->id,
            'precio_compra' => 79700,
            'precio_venta' => 112000,
            'precio_venta_m2' => 85000,
            'precio_mayorista' => 100000,
            'precio_distribuidor' => 90000,
            'stock' => 2,
            'stock_minimo' => 1,
            'iva' => 19,
            'm2_por_caja' => 1.44,
        ])->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto convertido a M2 con precio configurado; ya se puede vender por caja o por M2.');

        $producto = Producto::withoutGlobalScopes()->where('codigo', 'PISO-NUEVO')->firstOrFail();
        $presentacion = ProductoPresentacion::withoutGlobalScopes()
            ->where('producto_id', $producto->id)
            ->firstOrFail();

        $this->assertEquals($this->m2->id, $producto->unidad_medida_id);
        $this->assertEquals(2.88, (float) $producto->stock);
        $this->assertEquals(round(79700 / 1.44, 2), (float) $producto->precio_compra);
        $this->assertEquals(85000, (float) $producto->precio_venta);
        $this->assertEquals(round(100000 / 1.44, 2), (float) $producto->precio_mayorista);
        $this->assertEquals(round(90000 / 1.44, 2), (float) $producto->precio_distribuidor);
        $this->assertEquals(1.44, (float) $presentacion->factor_conversion);
        $this->assertEquals(112000, (float) $presentacion->precio_venta);
        $this->assertEquals(2.88, (float) Inventario::withoutGlobalScopes()->where('producto_id', $producto->id)->value('stock'));
    }

    public function test_editar_producto_con_factor_convierte_existencias_y_conserva_stock(): void
    {
        $this->actingAs($this->admin)->put(route('productos.update', $this->piso), [
            'nombre' => $this->piso->nombre,
            'codigo' => $this->piso->codigo,
            'unidad_medida_id' => $this->caja->id,
            'precio_compra' => 79700,
            'precio_venta' => 112000,
            'precio_venta_m2' => 87000,
            'stock_minimo' => 2,
            'iva' => 19,
            'm2_por_caja' => 1.44,
        ])->assertRedirect(route('productos.index'));

        $this->assertEquals($this->m2->id, $this->piso->fresh()->unidad_medida_id);
        $this->assertEquals(87000, (float) $this->piso->fresh()->precio_venta);
        $this->assertEquals(15.12, $this->stock());
        $this->assertEquals(2.88, (float) Inventario::withoutGlobalScopes()->where('producto_id', $this->piso->id)->value('stock_minimo'));
        $this->assertEquals(1.44, (float) $this->presentacionCaja()->factor_conversion);
    }
}
