<?php

namespace Tests\Feature\Modulos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Enviar formularios vacíos o con datos imposibles debe devolver errores de
 * validación al usuario, no excepciones ni registros corruptos.
 */
class ValidacionesFormulariosTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    public function test_formularios_vacios_devuelven_errores_de_validacion(): void
    {
        $formularios = [
            'clientes.store' => [],
            'productos.store' => [],
            'proveedores.store' => [],
            'compras.store' => [],
            'cartera.store' => [],
            'impuestos.store' => [],
            'resoluciones.store' => [],
            'listas-precios.store' => [],
            'usuarios.store' => [],
            'sucursales.store' => [],
            'traslados.store' => [],
            'inventario.ajuste.store' => [],
            'inventario.traslado.store' => [],
            'cajas.store' => [],
            'catalogos.categorias.store' => [],
            'catalogos.marcas.store' => [],
            'catalogos.unidades.store' => [],
        ];

        $fallos = [];
        foreach ($formularios as $ruta => $payload) {
            $response = $this->actingAs($this->admin)->from('/dashboard')->post(route($ruta), $payload);

            if ($response->status() >= 500) {
                $fallos[] = "{$ruta}: error {$response->status()}";
            } elseif (! session()->has('errors') || session('errors')->isEmpty()) {
                $fallos[] = "{$ruta}: aceptó el formulario vacío (status {$response->status()})";
            }
        }

        $this->assertSame([], $fallos, implode("\n", $fallos));
    }

    public function test_producto_rechaza_precios_negativos(): void
    {
        $this->actingAs($this->admin)->from(route('productos.create'))
            ->post(route('productos.store'), [
                'nombre' => 'Producto Negativo',
                'codigo' => 'NEG-01',
                'precio_compra' => -100,
                'precio_venta' => -500,
                'stock_minimo' => -3,
            ])
            ->assertSessionHasErrors(['precio_venta']);

        $this->assertDatabaseMissing('productos', ['codigo' => 'NEG-01']);
    }

    public function test_cliente_rechaza_documento_duplicado_en_la_empresa(): void
    {
        $this->actingAs($this->admin)->from(route('clientes.create'))
            ->post(route('clientes.store'), [
                'tipo_persona' => 'JURIDICA',
                'tipo_documento' => 'NIT',
                'numero_documento' => '901555444',
                'razon_social' => 'Duplicado SAS',
            ])
            ->assertSessionHasErrors(['numero_documento']);
    }

    public function test_abono_mayor_al_saldo_es_rechazado(): void
    {
        $saldoAntes = (float) $this->cuenta->saldo_pendiente;

        $response = $this->actingAs($this->admin)->from(route('cartera.show', $this->cuenta))
            ->post(route('cartera.abono.store', $this->cuenta), [
                'monto' => $saldoAntes + 1000000,
                'metodo_pago' => 'EFECTIVO',
                'fecha_pago' => now()->toDateString(),
            ]);

        $this->assertLessThan(500, $response->status());
        $this->assertEquals($saldoAntes, (float) $this->cuenta->fresh()->saldo_pendiente);
    }

    public function test_devolucion_mayor_a_lo_vendido_es_rechazada(): void
    {
        $detalle = $this->ventaCredito->detalles->first();

        $response = $this->actingAs($this->admin)->from(route('devoluciones.create', $this->ventaCredito))
            ->post(route('devoluciones.store', $this->ventaCredito), [
                'items' => [[
                    'venta_detalle_id' => $detalle->id,
                    'cantidad' => (float) $detalle->cantidad + 50,
                    'reingresa_inventario' => 1,
                ]],
                'tipo_reintegro' => 'EFECTIVO',
                'motivo' => 'Intento de devolver más de lo vendido',
            ]);

        $this->assertLessThan(500, $response->status());
        $this->assertDatabaseCount('devoluciones', 1);
    }

    public function test_traslado_entre_la_misma_sede_es_rechazado(): void
    {
        $response = $this->actingAs($this->admin)->from(route('traslados.create'))
            ->post(route('traslados.store'), [
                'sucursal_origen_id' => $this->sucursal->id,
                'sucursal_destino_id' => $this->sucursal->id,
                'motivo' => 'Misma sede',
                'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1]],
            ]);

        $this->assertLessThan(500, $response->status());
        $this->assertDatabaseCount('traslados_sucursal', 1);
    }

    private function datosResolucion(array $extra = []): array
    {
        return array_merge([
            'numero_resolucion' => '18764999555',
            'prefijo' => 'NUEV',
            'rango_desde' => 1,
            'rango_hasta' => 1000,
            'consecutivo_actual' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_vigencia' => now()->addYear()->toDateString(),
            'estado' => 'ACTIVA',
            'es_predeterminada' => 0,
        ], $extra);
    }

    public function test_admin_puede_crear_y_eliminar_resolucion_sin_documentos(): void
    {
        $this->actingAs($this->admin)->post(route('resoluciones.store'), $this->datosResolucion())
            ->assertRedirect(route('resoluciones.index'))
            ->assertSessionHasNoErrors();

        $nueva = \App\Models\ResolucionFacturacion::where('prefijo', 'NUEV')->firstOrFail();

        $this->actingAs($this->admin)->delete(route('resoluciones.destroy', $nueva))
            ->assertRedirect(route('resoluciones.index'));
        $this->assertSoftDeleted('resoluciones_facturacion', ['id' => $nueva->id]);
    }

    public function test_resolucion_con_documentos_emitidos_no_se_puede_eliminar(): void
    {
        $this->actingAs($this->admin)->delete(route('resoluciones.destroy', $this->resolucion))
            ->assertRedirect(route('resoluciones.index'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('resoluciones_facturacion', ['id' => $this->resolucion->id]);
    }

    public function test_resolucion_no_acepta_sucursal_de_otra_empresa(): void
    {
        $sedeAjena = $this->crearEmpresaAjena()['sede'];

        $this->actingAs($this->admin)->from(route('resoluciones.create'))
            ->post(route('resoluciones.store'), $this->datosResolucion(['sucursal_id' => $sedeAjena->id]))
            ->assertSessionHasErrors(['sucursal_id']);
    }

    public function test_venta_pos_sin_items_es_rechazada(): void
    {
        $response = $this->actingAs($this->cajero)
            ->postJson(route('pos.procesar'), ['items' => [], 'tipo_pago' => 'CONTADO', 'metodo_pago' => 'EFECTIVO']);

        $this->assertContains($response->status(), [400, 422], "POS respondió {$response->status()} a una venta sin ítems");
    }
}
