<?php

namespace Tests\Feature\Modulos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Pantallas de detalle, edición, creación e impresión de cada módulo con datos reales.
 */
class DetallesYFormulariosTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    private function verPagina(string $url, array $textos = []): void
    {
        $response = $this->actingAs($this->admin)->get($url);

        $response->assertOk();
        foreach ($textos as $texto) {
            $response->assertSee($texto, false);
        }
    }

    public function test_formularios_de_creacion_cargan(): void
    {
        foreach ([
            'clientes.create', 'productos.create', 'compras.create', 'cartera.create',
            'traslados.create', 'listas-precios.create', 'impuestos.create', 'resoluciones.create',
            'usuarios.create', 'inventario.ajuste.create', 'inventario.traslado.create',
        ] as $ruta) {
            $status = $this->actingAs($this->admin)->get(route($ruta))->status();
            $this->assertSame(200, $status, "El formulario {$ruta} respondió {$status}");
        }
    }

    public function test_detalle_de_venta_y_ticket(): void
    {
        $this->verPagina(route('ventas.show', $this->ventaContado), [$this->ventaContado->numero_venta, 'Martillo Carpintero 16oz']);
        $this->verPagina(route('ventas.ticket', $this->ventaContado));
        $this->verPagina(route('imprimir.venta', $this->ventaContado));
    }

    public function test_edicion_de_producto_y_presentaciones(): void
    {
        $this->verPagina(route('productos.edit', $this->producto), ['Martillo Carpintero 16oz']);
        $this->verPagina(route('productos.presentaciones.index', $this->producto));
    }

    /**
     * Sede principal: compra +50, ventas -4 y -10, devolución +1, traslado -5 => saldo 32.
     */
    public function test_kardex_muestra_saldo_operador_y_referencia_de_cada_movimiento(): void
    {
        $this->verPagina(route('inventario.kardex', $this->producto), [
            '32.00',
            'Cajera Turno Mañana',
            'Admin Módulos',
            'FC-MOD-001',
            $this->traslado->consecutivo,
        ]);
    }

    public function test_detalle_de_compra(): void
    {
        $this->verPagina(route('compras.show', $this->compra), ['FC-MOD-001', 'Distribuidora Herramientas del Caribe']);
    }

    public function test_edicion_de_cliente(): void
    {
        $this->verPagina(route('clientes.edit', $this->cliente), ['Constructora Los Andes SAS']);
    }

    public function test_detalle_de_cuenta_estado_de_cuenta_y_recibo(): void
    {
        $this->verPagina(route('cartera.show', $this->cuenta), [$this->cuenta->numero_documento]);
        $this->verPagina(route('cartera.estado-cuenta', $this->cliente), ['Constructora Los Andes SAS']);
        $this->verPagina(route('cartera.estado-cuenta.print', $this->cliente));

        $pago = $this->cuenta->pagos()->firstOrFail();
        $this->verPagina(route('cartera.recibo', $pago));
        $this->verPagina(route('imprimir.abono', $pago));
    }

    public function test_detalle_y_comprobante_de_devolucion(): void
    {
        $this->verPagina(route('devoluciones.show', $this->devolucion), [$this->devolucion->numero_devolucion]);
        $this->verPagina(route('devoluciones.comprobante', $this->devolucion));
        $this->verPagina(route('devoluciones.create', $this->ventaCredito));
    }

    public function test_detalle_e_impresion_de_documento_de_venta(): void
    {
        $documento = $this->ventaCredito->documentoVenta;

        $this->verPagina(route('documentos.show', $documento), [$documento->numero_completo]);
        $this->verPagina(route('imprimir.documento', $documento));
    }

    public function test_detalle_de_factura_electronica_y_xml(): void
    {
        $this->verPagina(route('facturacion-electronica.show', $this->documentoElectronico), [$this->documentoElectronico->consecutivo_completo]);
        $this->actingAs($this->admin)
            ->get(route('facturacion-electronica.descargar-xml', $this->documentoElectronico))
            ->assertOk();
    }

    public function test_detalle_de_traslado(): void
    {
        $this->verPagina(route('traslados.show', $this->traslado), [$this->traslado->consecutivo, 'Martillo Carpintero 16oz']);
    }

    public function test_detalle_y_edicion_de_lista_de_precios(): void
    {
        $this->verPagina(route('listas-precios.show', $this->lista), ['Mayoristas Constructoras']);
        $this->verPagina(route('listas-precios.edit', $this->lista), ['Mayoristas Constructoras']);
    }

    public function test_edicion_de_impuesto_y_resolucion(): void
    {
        $this->verPagina(route('impuestos.edit', $this->impuesto), ['IVA General 19%']);
        $this->verPagina(route('resoluciones.edit', $this->resolucion), ['18764999001']);
    }

    public function test_caja_detalle_cierre_y_comprobante(): void
    {
        $caja = $this->sesionCaja->caja;

        $this->verPagina(route('cajas.show', $caja), ['Caja Mostrador']);
        $this->verPagina(route('cajas.cierre', $caja));
        $this->verPagina(route('imprimir.caja', $this->sesionCaja));
    }

    public function test_comprobante_impreso_de_cierre_muestra_esperado_y_contado(): void
    {
        $this->sesionCaja->recalcularTotales();
        $esperado = $this->sesionCaja->calcularSaldoEsperadoEfectivo();
        app(\App\Actions\Caja\CerrarCajaAction::class)->execute($this->sesionCaja, $this->admin, $esperado - 5000, 'Arqueo con faltante');

        $this->verPagina(route('imprimir.caja', $this->sesionCaja), [
            '$' . number_format($esperado, 2),
            '$' . number_format($esperado - 5000, 2),
        ]);
    }

    public function test_estado_de_cuenta_impreso_muestra_nombre_de_la_empresa(): void
    {
        $this->verPagina(route('cartera.estado-cuenta.print', $this->cliente), ['Ferretería Módulos SAS']);
    }

    public function test_pos_envia_la_unidad_de_medida_real_de_cada_producto(): void
    {
        $metro = \App\Models\UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Metro', 'codigo' => 'MT', 'activo' => true]);
        $this->producto->update(['unidad_medida_id' => $metro->id]);

        $response = $this->actingAs($this->cajero)->get(route('pos.index'))->assertOk();
        $productos = $response->viewData('productos');

        $this->assertSame('MT', collect($productos)->firstWhere('id', $this->producto->id)['unidad']);
    }

    public function test_detalle_de_auditoria(): void
    {
        $evento = \App\Models\Auditoria::query()->latest('id')->firstOrFail();

        $this->verPagina(route('auditoria.show', $evento));
    }

    public function test_perfil_de_empresa_y_edicion_de_usuario(): void
    {
        $this->verPagina(route('empresa.perfil'), ['Ferretería Módulos SAS']);
        $this->verPagina(route('usuarios.edit', $this->cajero), ['Cajera Turno Mañana']);
    }

    public function test_reportes_cargan_con_movimientos(): void
    {
        foreach ([
            'reportes.index', 'reportes.ventas', 'reportes.cajas', 'reportes.cartera', 'reportes.compras',
            'reportes.impuestos', 'reportes.inventario', 'reportes.metodos-pago', 'reportes.utilidad',
            'reportes.multisucursal', 'reportes.ventas.imprimir', 'reportes.cajas.imprimir',
            'reportes.cartera.imprimir', 'reportes.compras.imprimir', 'reportes.inventario.imprimir',
            'reportes.utilidad.imprimir',
        ] as $ruta) {
            $status = $this->actingAs($this->admin)->get(route($ruta))->status();
            $this->assertSame(200, $status, "El reporte {$ruta} respondió {$status}");
        }
    }
}
