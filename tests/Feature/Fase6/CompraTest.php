<?php

namespace Tests\Feature\Fase6;

use App\Enums\EstadoCompra;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoMovimientoInventario;
use App\Enums\TipoPago;
use App\Models\Compra;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected Sucursal $sucursalA;

    protected Sucursal $sucursalB;

    protected User $adminA;

    protected User $adminB;

    protected Proveedor $proveedorA;

    protected Proveedor $proveedorB;

    protected Producto $productoA;

    protected Producto $productoB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Empresa Alfa S.A.S.']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Empresa Beta S.A.S.']);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalA = Sucursal::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Norte Alfa',
            'codigo' => 'ALF-01',
            'estado' => EstadoGeneral::ACTIVO,
            'es_principal' => true,
        ]);

        $this->sucursalB = Sucursal::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Sur Beta',
            'codigo' => 'BET-01',
            'estado' => EstadoGeneral::ACTIVO,
            'es_principal' => true,
        ]);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->proveedorA = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Proveedor Abarrotes del Norte',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900111222-3',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->proveedorB = Proveedor::create([
            'empresa_id' => $this->empresaB->id,
            'razon_social' => 'Proveedor Ferretero del Sur',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900333444-5',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Arroz Extra 500g',
            'codigo' => 'ARR-500',
            'precio_compra' => 2000,
            'precio_venta' => 3000,
            'stock' => 10,
            'stock_minimo' => 2,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 10,
            'stock_minimo' => 2,
        ]);

        $this->productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Cemento Gris 50kg',
            'codigo' => 'CEM-50',
            'precio_compra' => 25000,
            'precio_venta' => 35000,
            'stock' => 20,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'producto_id' => $this->productoB->id,
            'stock' => 20,
            'stock_minimo' => 5,
        ]);
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    public function test_admin_puede_ver_listado_compras_de_su_empresa_solamente(): void
    {
        $compraA = Compra::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'user_id' => $this->adminA->id,
            'numero_factura' => 'FAC-ALFA-001',
            'fecha_emision' => now()->format('Y-m-d'),
            'subtotal' => 10000,
            'impuestos' => 1900,
            'total' => 11900,
            'tipo_pago' => TipoPago::CONTADO,
            'estado' => EstadoCompra::REGISTRADA,
        ]);

        $compraB = Compra::create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'proveedor_id' => $this->proveedorB->id,
            'user_id' => $this->adminB->id,
            'numero_factura' => 'FAC-BETA-999',
            'fecha_emision' => now()->format('Y-m-d'),
            'subtotal' => 50000,
            'impuestos' => 9500,
            'total' => 59500,
            'tipo_pago' => TipoPago::CONTADO,
            'estado' => EstadoCompra::REGISTRADA,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('compras.index'));

        $response->assertStatus(200);
        $response->assertSee('FAC-ALFA-001');
        $response->assertDontSee('FAC-BETA-999');
    }

    public function test_admin_puede_ver_formulario_creacion_compra(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('compras.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrar Factura de Compra');
        $response->assertSee($this->proveedorA->razon_social);
        $response->assertDontSee($this->proveedorB->razon_social);
    }

    public function test_admin_puede_registrar_compra_e_ingresar_stock_al_kardex_transaccionalmente(): void
    {
        $payload = [
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'FAC-100234',
            'fecha_emision' => now()->format('Y-m-d'),
            'tipo_pago' => 'CONTADO',
            'descuento' => 500,
            'observaciones' => 'Entrega sin novedades',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 20,
                    'costo_unitario' => 2200, // Nuevo costo superior al original (2000)
                    'porcentaje_iva' => 19,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminA)->post(route('compras.store'), $payload);

        $response->assertRedirect();

        // 1. Verificar registro en compras
        $this->assertDatabaseHas('compras', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'FAC-100234',
            'subtotal' => 44000.00, // 20 * 2200
            'impuestos' => 8360.00, // 44000 * 0.19
            'descuento' => 500.00,
            'total' => 51860.00, // 44000 + 8360 - 500
            'estado' => EstadoCompra::REGISTRADA->value,
        ]);

        $compra = Compra::where('numero_factura', 'FAC-100234')->firstOrFail();

        // 2. Verificar detalle
        $this->assertDatabaseHas('compra_detalles', [
            'compra_id' => $compra->id,
            'producto_id' => $this->productoA->id,
            'cantidad' => 20.00,
            'costo_unitario' => 2200.00,
            'porcentaje_iva' => 19.00,
            'subtotal' => 44000.00,
        ]);

        // 3. Verificar incremento de inventario en sede (10 inicial + 20 comprado = 30)
        $inv = Inventario::where('sucursal_id', $this->sucursalA->id)
            ->where('producto_id', $this->productoA->id)
            ->first();
        $this->assertEquals(30.0, (float) $inv->stock);

        // 4. Verificar incremento en stock consolidado del producto
        $this->productoA->refresh();
        $this->assertEquals(30.0, (float) $this->productoA->stock);

        // 5. Verificar actualización del costo de reposición maestro
        $this->assertEquals(2200.0, (float) $this->productoA->precio_compra);

        // 6. Verificar asiento en Kardex
        $this->assertDatabaseHas('movimientos_inventario', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'tipo' => TipoMovimientoInventario::ENTRADA_COMPRA->value,
            'cantidad' => 20.0,
            'stock_anterior' => 10.0,
            'stock_posterior' => 30.0,
            'costo_unitario' => 2200.0,
        ]);
    }

    public function test_admin_no_puede_registrar_compra_con_productos_o_sedes_de_otra_empresa(): void
    {
        $payload = [
            'sucursal_id' => $this->sucursalB->id, // Sede de Empresa B
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'HACK-01',
            'fecha_emision' => now()->format('Y-m-d'),
            'tipo_pago' => 'CONTADO',
            'items' => [
                [
                    'producto_id' => $this->productoB->id, // Producto de Empresa B
                    'cantidad' => 5,
                    'costo_unitario' => 1000,
                    'porcentaje_iva' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminA)->post(route('compras.store'), $payload);

        $response->assertSessionHasErrors(['sucursal_id', 'items.0.producto_id']);
        $this->assertDatabaseMissing('compras', ['numero_factura' => 'HACK-01']);
    }

    public function test_admin_puede_ver_detalle_de_factura_de_su_empresa(): void
    {
        $compra = Compra::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'user_id' => $this->adminA->id,
            'numero_factura' => 'FAC-VIEW-01',
            'fecha_emision' => now()->format('Y-m-d'),
            'subtotal' => 20000,
            'impuestos' => 3800,
            'total' => 23800,
            'tipo_pago' => TipoPago::CONTADO,
            'estado' => EstadoCompra::REGISTRADA,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('compras.show', $compra));

        $response->assertStatus(200);
        $response->assertSee('FAC-VIEW-01');
        $response->assertSee($this->proveedorA->razon_social);
    }

    public function test_admin_no_puede_ver_detalle_de_factura_de_otra_empresa(): void
    {
        $compraB = Compra::create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'proveedor_id' => $this->proveedorB->id,
            'user_id' => $this->adminB->id,
            'numero_factura' => 'FAC-BETA-PRIVADA',
            'fecha_emision' => now()->format('Y-m-d'),
            'subtotal' => 10000,
            'impuestos' => 0,
            'total' => 10000,
            'tipo_pago' => TipoPago::CONTADO,
            'estado' => EstadoCompra::REGISTRADA,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('compras.show', $compraB));

        $response->assertStatus(403);
    }

    public function test_admin_puede_anular_compra_y_revertir_stock(): void
    {
        // Registrar compra primero
        $payload = [
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'FAC-ANULAR-01',
            'fecha_emision' => now()->format('Y-m-d'),
            'tipo_pago' => 'CONTADO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 15,
                    'costo_unitario' => 2100,
                    'porcentaje_iva' => 0,
                ],
            ],
        ];

        $this->actingAs($this->adminA)->post(route('compras.store'), $payload);

        $compra = Compra::where('numero_factura', 'FAC-ANULAR-01')->firstOrFail();

        // Existencias luego de compra: 10 + 15 = 25
        $this->assertEquals(25.0, (float) Inventario::where('sucursal_id', $this->sucursalA->id)->where('producto_id', $this->productoA->id)->value('stock'));

        // Anular compra
        $response = $this->actingAs($this->adminA)->post(route('compras.anular', $compra), [
            'motivo' => 'Factura con precios erróneos del proveedor',
        ]);

        $response->assertRedirect();
        $compra->refresh();

        // Estado debe ser ANULADA
        $this->assertEquals(EstadoCompra::ANULADA, $compra->estado);
        $this->assertStringContainsString('Factura con precios erróneos', $compra->observaciones);

        // Stock debe haber vuelto a 10
        $this->assertEquals(10.0, (float) Inventario::where('sucursal_id', $this->sucursalA->id)->where('producto_id', $this->productoA->id)->value('stock'));

        // Se debe haber generado movimiento de salida por devolución a proveedor en Kardex
        $this->assertDatabaseHas('movimientos_inventario', [
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'tipo' => TipoMovimientoInventario::DEVOLUCION_PROVEEDOR->value,
            'cantidad' => 15.0,
            'stock_anterior' => 25.0,
            'stock_posterior' => 10.0,
        ]);
    }

    public function test_no_se_puede_anular_compra_si_el_stock_fue_consumido(): void
    {
        // Registrar compra de 10 unidades
        $payload = [
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'FAC-CONSUMO-01',
            'fecha_emision' => now()->format('Y-m-d'),
            'tipo_pago' => 'CONTADO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 10,
                    'costo_unitario' => 2000,
                    'porcentaje_iva' => 0,
                ],
            ],
        ];

        $this->actingAs($this->adminA)->post(route('compras.store'), $payload);
        $compra = Compra::where('numero_factura', 'FAC-CONSUMO-01')->firstOrFail();

        // Simulamos que se vendió mercancía y el stock cayó a 5 (insuficiente para devolver las 10 unidades)
        Inventario::where('sucursal_id', $this->sucursalA->id)
            ->where('producto_id', $this->productoA->id)
            ->update(['stock' => 5]);

        $response = $this->actingAs($this->adminA)->post(route('compras.anular', $compra), [
            'motivo' => 'Intento de anulación sin stock',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $compra->refresh();
        $this->assertEquals(EstadoCompra::REGISTRADA, $compra->estado);
    }

    public function test_no_se_puede_registrar_compra_con_productos_duplicados_en_multiples_lineas(): void
    {
        $payload = [
            'sucursal_id' => $this->sucursalA->id,
            'proveedor_id' => $this->proveedorA->id,
            'numero_factura' => 'FAC-DUP-01',
            'fecha_emision' => now()->format('Y-m-d'),
            'tipo_pago' => 'CONTADO',
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 5,
                    'costo_unitario' => 2000,
                    'porcentaje_iva' => 0,
                ],
                [
                    'producto_id' => $this->productoA->id, // Mismo producto repetido
                    'cantidad' => 3,
                    'costo_unitario' => 2000,
                    'porcentaje_iva' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminA)->post(route('compras.store'), $payload);

        $response->assertSessionHasErrors(['items.0.producto_id', 'items.1.producto_id']);
        $this->assertDatabaseMissing('compras', ['numero_factura' => 'FAC-DUP-01']);
    }
}
