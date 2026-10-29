<?php

namespace Tests\Feature\Modulos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Los buscadores encuentran lo que existe, descartan lo que no, y los filtros
 * con valores inválidos nunca provocan un error 500.
 */
class FiltrosYBusquedaTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();
    }

    private function buscar(string $ruta, array $params)
    {
        return $this->actingAs($this->admin)->get(route($ruta, $params))->assertOk();
    }

    public function test_buscador_de_productos(): void
    {
        $this->buscar('productos.index', ['buscar' => 'Martillo'])->assertSee('Martillo Carpintero 16oz');
        $this->buscar('productos.index', ['buscar' => 'MART-16'])->assertSee('Martillo Carpintero 16oz');
        $this->buscar('productos.index', ['buscar' => 'zzz-no-existe'])->assertDontSee('Martillo Carpintero 16oz');
    }

    public function test_buscador_de_clientes(): void
    {
        $this->buscar('clientes.index', ['buscar' => 'Andes'])->assertSee('Constructora Los Andes SAS');
        $this->buscar('clientes.index', ['buscar' => '901555444'])->assertSee('Constructora Los Andes SAS');
        $this->buscar('clientes.index', ['buscar' => 'zzz-no-existe'])->assertDontSee('Constructora Los Andes SAS');
    }

    public function test_buscador_de_proveedores(): void
    {
        $this->buscar('proveedores.index', ['buscar' => 'Caribe'])->assertSee('Distribuidora Herramientas del Caribe');
        $this->buscar('proveedores.index', ['buscar' => 'zzz-no-existe'])->assertDontSee('Distribuidora Herramientas del Caribe');
    }

    public function test_buscador_de_ventas(): void
    {
        $numero = $this->ventaCredito->numero_venta;

        $this->buscar('ventas.index', ['buscar' => $numero])->assertSee($numero);
        $this->buscar('ventas.index', ['tipo_pago' => 'CREDITO'])
            ->assertSee($numero)
            ->assertDontSee($this->ventaContado->numero_venta);
    }

    public function test_buscador_de_compras(): void
    {
        $this->buscar('compras.index', ['buscar' => 'FC-MOD-001'])->assertSee('FC-MOD-001');
        $this->buscar('compras.index', ['buscar' => 'zzz-no-existe'])->assertDontSee('FC-MOD-001');
    }

    public function test_buscador_de_cartera(): void
    {
        $numero = $this->cuenta->numero_documento;

        $this->buscar('cartera.index', ['buscar' => 'Andes'])->assertSee($numero);
        $this->buscar('cartera.index', ['buscar' => 'zzz-no-existe'])->assertDontSee($numero);
    }

    public function test_buscador_de_inventario(): void
    {
        $this->buscar('inventario.index', ['buscar' => 'Martillo'])->assertSee('Martillo Carpintero 16oz');
        $this->buscar('inventario.index', ['buscar' => 'zzz-no-existe'])->assertDontSee('Martillo Carpintero 16oz');
    }

    public function test_buscador_de_documentos(): void
    {
        $numero = $this->ventaCredito->documentoVenta->numero_completo;

        $this->buscar('documentos.index', ['q' => $numero])->assertSee($numero);
    }

    public function test_buscador_de_listas_de_precios(): void
    {
        $this->buscar('listas-precios.index', ['search' => 'Mayoristas'])->assertSee('Mayoristas Constructoras');
        $this->buscar('listas-precios.index', ['search' => 'zzz-no-existe'])->assertDontSee('Mayoristas Constructoras');
    }

    public function test_buscador_de_usuarios(): void
    {
        $this->buscar('usuarios.index', ['buscar' => 'Cajera'])
            ->assertSee('Cajera Turno Mañana')
            ->assertDontSee('Admin Módulos</span>', false);
    }

    /**
     * Valores de filtro basura (estados inexistentes, fechas mal formadas, ids no
     * numéricos, páginas fuera de rango) deben responder sin error de servidor.
     */
    public function test_filtros_con_valores_invalidos_no_rompen_los_listados(): void
    {
        $basura = [
            'estado' => 'NO_EXISTE', 'fecha_desde' => 'no-es-fecha', 'fecha_hasta' => '2026-13-45',
            'desde' => 'ayer', 'hasta' => '31/31/2026', 'cliente_id' => 'abc', 'proveedor_id' => '-1',
            'sucursal_id' => '99999', 'categoria_id' => 'x', 'tipo' => 'RARO', 'tipo_pago' => 'TRUEQUE',
            'tipo_persona' => 'MARCIANA', 'estado_stock' => 'infinito', 'rol' => 'pirata', 'user_id' => 'z',
            'origen_id' => 'a', 'destino_id' => 'b', 'modulo' => '<script>', 'accion' => "' OR 1=1 --",
            'buscar' => "'; DROP TABLE ventas; --", 'search' => '%%%', 'q' => '_', 'term' => '\\', 'page' => 9999,
        ];

        $rutas = [
            'ventas.index', 'productos.index', 'inventario.index', 'compras.index', 'proveedores.index',
            'clientes.index', 'cartera.index', 'devoluciones.index', 'documentos.index', 'traslados.index',
            'listas-precios.index', 'facturacion-electronica.index', 'auditoria.index', 'usuarios.index',
        ];

        $fallos = [];
        foreach ($rutas as $ruta) {
            $status = $this->actingAs($this->admin)->get(route($ruta, $basura))->status();
            if ($status >= 500) {
                $fallos[] = "{$ruta} => {$status}";
            }
        }

        $this->assertSame([], $fallos, 'Listados con error de servidor ante filtros inválidos: ' . implode(', ', $fallos));
    }
}
