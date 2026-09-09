<?php

namespace Tests\Feature\Fase24;

use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\RolSistema;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FerreteriaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected UnidadMedida $unidadMetro;
    protected UnidadMedida $unidadUnidad;
    protected CajaSesion $sesionA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A: Ferretería El Tornillo Feliz
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería El Tornillo Feliz Alfa',
            'nit' => '900888777-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Ferretería Sede Central',
            'codigo' => 'FERR-ALFA',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Ferretero Alfa',
            'email' => 'ferretero@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería Beta',
            'nit' => '900999111-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Beta',
            'codigo' => 'FERR-BETA',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'Ferretero Beta',
            'email' => 'ferretero@beta.test',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::setCompany($this->empresaA);
        BranchContext::setId($this->sucursalA->id);

        $this->unidadUnidad = UnidadMedida::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Unidad',
            'codigo' => 'UND',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->unidadMetro = UnidadMedida::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Metro',
            'codigo' => 'MTR',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $caja = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Mostrador',
            'codigo' => 'CJ-FERR',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $this->sesionA = CajaSesion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'caja_id' => $caja->id,
            'user_id' => $this->adminA->id,
            'fecha_apertura' => now(),
            'monto_apertura' => 50000,
            'estado' => EstadoSesionCaja::ABIERTA,
        ]);
    }

    public function test_admin_puede_crear_presentacion_con_factor_de_conversion(): void
    {
        $producto = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'unidad_medida_id' => $this->unidadUnidad->id,
            'nombre' => 'Tornillo Goloso 1 1/2 pulgada',
            'codigo' => 'TORN-001',
            'precio_compra' => 50.00,
            'precio_venta' => 150.00,
            'stock' => 1000,
            'stock_minimo' => 50,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $response = $this->actingAs($this->adminA)
            ->post(route('productos.presentaciones.store', $producto), [
                'nombre' => 'Caja x 100',
                'factor_conversion' => 100.0,
                'precio_venta' => 12000.00, // descuento por volumen
                'es_predeterminada' => 1,
            ]);

        $response->assertRedirect(route('productos.presentaciones.index', $producto));

        $this->assertDatabaseHas('producto_presentaciones', [
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $producto->id,
            'nombre' => 'Caja x 100',
            'factor_conversion' => 100.0000,
            'precio_venta' => 12000.00,
        ]);
    }

    public function test_venta_de_presentacion_descuenta_inventario_base_segun_factor(): void
    {
        $producto = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'unidad_medida_id' => $this->unidadUnidad->id,
            'nombre' => 'Arandela de Presión 3/8',
            'codigo' => 'ARAN-38',
            'precio_compra' => 20.00,
            'precio_venta' => 80.00,
            'stock' => 500,
            'stock_minimo' => 20,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $producto->id,
            'stock' => 500,
            'costo_promedio_ponderado' => 20.00,
        ]);

        // Presentación: Caja x 100 unidades (factor = 100)
        $presentacion = ProductoPresentacion::create([
            'empresa_id' => $this->empresaA->id,
            'producto_id' => $producto->id,
            'nombre' => 'Caja x 100',
            'factor_conversion' => 100.0,
            'precio_venta' => 7000.00,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Registrar venta de 2 cajas (debe descontar 200 unidades base)
        $action = app(RegistrarVentaAction::class);
        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            clienteId: null,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            items: [
                [
                    'producto_id' => $producto->id,
                    'presentacion_id' => $presentacion->id,
                    'cantidad' => 2, // 2 cajas
                    'precio_unitario' => 7000.00,
                ],
            ]
        );

        $this->assertNotNull($venta);
        $detalle = $venta->detalles->first();
        $this->assertEquals(2, $detalle->cantidad);
        $this->assertEquals(100.0, (float) $detalle->factor_conversion);
        $this->assertEquals($presentacion->id, $detalle->presentacion_id);

        // Verificar descuento en la unidad base (500 - 200 = 300)
        $inventario = Inventario::where('empresa_id', $this->empresaA->id)
            ->where('sucursal_id', $this->sucursalA->id)
            ->where('producto_id', $producto->id)
            ->first();

        $this->assertEquals(300, (float) $inventario->stock);
    }

    public function test_venta_fraccionada_por_metro(): void
    {
        $cable = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'unidad_medida_id' => $this->unidadMetro->id,
            'nombre' => 'Cable Eléctrico Dúplex #12',
            'codigo' => 'CAB-DUP-12',
            'precio_compra' => 1500.00,
            'precio_venta' => 3000.00,
            'stock' => 100,
            'stock_minimo' => 10,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $cable->id,
            'stock' => 100,
            'costo_promedio_ponderado' => 1500.00,
        ]);

        // Venta fraccionada de 15.5 metros
        $action = app(RegistrarVentaAction::class);
        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            clienteId: null,
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionA->id,
            items: [
                [
                    'producto_id' => $cable->id,
                    'cantidad' => 15.5, // 15.5 metros
                    'precio_unitario' => 3000.00,
                ],
            ]
        );

        $this->assertNotNull($venta);
        $detalle = $venta->detalles->first();
        $this->assertEquals(15.5, (float) $detalle->cantidad);
        $this->assertEquals(46500.00, (float) $detalle->total);

        // Inventario base restante: 100 - 15.5 = 84.5
        $inventario = Inventario::where('empresa_id', $this->empresaA->id)
            ->where('sucursal_id', $this->sucursalA->id)
            ->where('producto_id', $cable->id)
            ->first();

        $this->assertEquals(84.5, (float) $inventario->stock);
    }

    public function test_aislamiento_multiempresa_en_presentaciones(): void
    {
        $productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'unidad_medida_id' => $this->unidadUnidad->id,
            'nombre' => 'Tubo PVC 1/2 pulgada Empresa B',
            'codigo' => 'PVC-B01',
            'precio_compra' => 5000.00,
            'precio_venta' => 9000.00,
            'stock' => 40,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        ProductoPresentacion::create([
            'empresa_id' => $this->empresaB->id,
            'producto_id' => $productoB->id,
            'nombre' => 'Atado x 10 tubos',
            'factor_conversion' => 10,
            'precio_venta' => 80000,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Usuario A no puede ver ni gestionar presentaciones del producto de B
        $response = $this->actingAs($this->adminA)
            ->get(route('productos.presentaciones.index', $productoB));

        // BelongsToCompany aborts with 404 or 403
        $this->assertContains($response->status(), [403, 404]);
    }
}
