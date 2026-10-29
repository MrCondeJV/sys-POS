<?php

namespace Tests\Feature\Modulos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Cada listado se carga con registros reales: así se ejecuta el markup de la
 * tabla de escritorio y el de las tarjetas móviles (ambos se renderizan en el servidor).
 */
class ListadosConDatosTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    private function verListado(string $ruta, array $textos): void
    {
        $response = $this->actingAs($this->admin)->get(route($ruta));

        $response->assertOk();
        foreach ($textos as $texto) {
            $response->assertSee($texto, false);
        }
    }

    public function test_ventas_lista_ventas_de_contado_y_credito(): void
    {
        $this->verListado('ventas.index', [
            $this->ventaContado->numero_venta,
            $this->ventaCredito->numero_venta,
            'Constructora Los Andes SAS',
        ]);
    }

    public function test_productos_lista_producto_con_categoria_y_marca(): void
    {
        $this->verListado('productos.index', ['Martillo Carpintero 16oz', 'MART-16', 'Herramientas Manuales', 'Stanley']);
    }

    public function test_inventario_lista_existencias_por_sede(): void
    {
        $this->verListado('inventario.index', ['Martillo Carpintero 16oz', 'Sede Principal Centro', 'Sede Norte']);
    }

    public function test_compras_lista_factura_y_proveedor(): void
    {
        $this->verListado('compras.index', ['FC-MOD-001', 'Distribuidora Herramientas del Caribe']);
    }

    public function test_proveedores_lista_proveedor_con_contacto(): void
    {
        $this->verListado('proveedores.index', ['Distribuidora Herramientas del Caribe', '800111222', 'Laura Gómez']);
    }

    public function test_clientes_lista_cliente_con_credito(): void
    {
        $this->verListado('clientes.index', ['Constructora Los Andes SAS', '901555444']);
    }

    public function test_cartera_lista_cuenta_con_abono_parcial(): void
    {
        $this->verListado('cartera.index', [$this->cuenta->numero_documento, 'Constructora Los Andes SAS', '% pagado']);
    }

    public function test_devoluciones_lista_devolucion_con_venta_origen(): void
    {
        $this->verListado('devoluciones.index', [$this->devolucion->numero_devolucion, $this->ventaContado->numero_venta]);
    }

    public function test_documentos_lista_ticket_y_factura(): void
    {
        $this->verListado('documentos.index', [
            $this->ventaContado->documentoVenta->numero_completo,
            $this->ventaCredito->documentoVenta->numero_completo,
        ]);
    }

    public function test_traslados_lista_ruta_entre_sedes(): void
    {
        $this->verListado('traslados.index', [$this->traslado->consecutivo, 'Sede Principal Centro', 'Sede Norte']);
    }

    public function test_listas_de_precios_lista_lista_con_descuento(): void
    {
        $this->verListado('listas-precios.index', ['Mayoristas Constructoras', 'MAY-01']);
    }

    public function test_listas_de_precios_cuentan_los_precios_especificos(): void
    {
        $this->lista->detalles()->create([
            'empresa_id' => $this->empresa->id,
            'producto_id' => $this->producto->id,
            'precio' => 31000,
        ]);

        $this->verListado('listas-precios.index', ['1 ítems']);
    }

    public function test_impuestos_lista_impuesto_predeterminado(): void
    {
        $this->verListado('impuestos.index', ['IVA General 19%', 'IVA19']);
    }

    public function test_resoluciones_lista_resolucion_vigente(): void
    {
        $this->verListado('resoluciones.index', ['18764999001', 'FEMO']);
    }

    public function test_facturacion_electronica_lista_documento_emitido(): void
    {
        $this->verListado('facturacion-electronica.index', [$this->documentoElectronico->consecutivo_completo]);
    }

    public function test_auditoria_lista_eventos_generados_por_las_operaciones(): void
    {
        $this->verListado('auditoria.index', ['Admin Módulos']);
    }

    public function test_sucursales_lista_ambas_sedes(): void
    {
        $this->verListado('sucursales.index', ['Sede Principal Centro', 'Sede Norte']);
    }

    public function test_usuarios_lista_colaboradores_de_la_empresa(): void
    {
        $this->verListado('usuarios.index', ['Admin Módulos', 'Cajera Turno Mañana']);
    }

    public function test_cajas_lista_caja_con_sesion_abierta(): void
    {
        $this->verListado('cajas.index', ['Caja Mostrador']);
    }

    public function test_suscripcion_muestra_historial_de_pagos(): void
    {
        $this->verListado('suscripcion.index', ['SUB-MOD-REF-0001']);
    }

    public function test_catalogos_lista_categoria_marca_y_unidad(): void
    {
        $this->verListado('catalogos.index', ['Herramientas Manuales', 'Stanley', 'UND']);
    }

    public function test_dashboard_carga_con_movimientos(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
    }
}
