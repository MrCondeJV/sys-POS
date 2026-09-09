<?php

namespace Tests\Feature\Fase28;

use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Exceptions\StockInsuficienteException;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrenciaVentasTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;
    protected Sucursal $sucursal;
    protected User $cajero;
    protected Producto $producto;
    protected RegistrarVentaAction $registrarVentaAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresa = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería El Candado SAS',
            'nit' => '900999000-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursal = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Principal',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);
        setPermissionsTeamId($this->empresa->id);

        $this->cajero = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->cajero->assignRole(RolSistema::CAJERO->value);

        CompanyContext::setCompanyId($this->empresa->id);
        BranchContext::setId($this->sucursal->id);

        $cat = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Herramientas']);
        $um = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Unidad', 'codigo' => 'UND', 'activo' => true]);

        // Producto con exactamente 10 unidades físicas en stock
        $this->producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $cat->id,
            'unidad_medida_id' => $um->id,
            'codigo_barras' => 'TAL-CONC',
            'nombre' => 'Taladro Inalámbrico 20V',
            'precio_compra' => 120000,
            'precio_venta' => 220000,
            'stock' => 10,
            'stock_minimo' => 2,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'producto_id' => $this->producto->id,
            'stock' => 10,
            'stock_minimo' => 2,
        ]);

        $this->registrarVentaAction = app(RegistrarVentaAction::class);
    }

    /**
     * Prueba de integridad concurrente:
     * Dos intentos de venta simultáneos que en conjunto exceden el stock disponible.
     * Stock inicial: 10 unidades.
     * Venta 1: requiere 7 unidades.
     * Venta 2: requiere 5 unidades.
     * Resultado requerido: Venta 1 pasa exitosamente (quedan 3); Venta 2 es rechazada con StockInsuficienteException.
     * El stock jamás puede quedar en número negativo (-2).
     */
    public function test_dos_ventas_simultaneas_no_dejan_stock_negativo(): void
    {
        // Venta 1: 7 unidades
        $venta1 = $this->registrarVentaAction->execute(
            empresaId: $this->empresa->id,
            sucursalId: $this->sucursal->id,
            userId: $this->cajero->id,
            items: [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 7,
                    'precio_unitario' => 220000,
                ],
            ]
        );

        $this->assertNotNull($venta1);

        $invDespuesVenta1 = Inventario::where('sucursal_id', $this->sucursal->id)
            ->where('producto_id', $this->producto->id)
            ->first();
        $this->assertEquals(3, (float) $invDespuesVenta1->stock);

        // Venta 2: intenta vender 5 unidades (solo quedan 3)
        $this->expectException(StockInsuficienteException::class);

        $this->registrarVentaAction->execute(
            empresaId: $this->empresa->id,
            sucursalId: $this->sucursal->id,
            userId: $this->cajero->id,
            items: [
                [
                    'producto_id' => $this->producto->id,
                    'cantidad' => 5,
                    'precio_unitario' => 220000,
                ],
            ]
        );
    }
}
