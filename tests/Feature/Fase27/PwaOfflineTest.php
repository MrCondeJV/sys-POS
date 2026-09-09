<?php

namespace Tests\Feature\Fase27;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PwaOfflineTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $cajeroA;
    protected User $cajeroB;
    protected Producto $productoA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Minimarket Alfa SAS',
            'nit' => '900444555-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Supermercado Beta SAS',
            'nit' => '900666777-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Principal Alfa',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Beta',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        setPermissionsTeamId($this->empresaA->id);
        $this->cajeroA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        setPermissionsTeamId($this->empresaB->id);
        $this->cajeroB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->cajeroB->assignRole(RolSistema::CAJERO->value);

        setPermissionsTeamId($this->empresaA->id);
        CompanyContext::setCompanyId($this->empresaA->id);
        BranchContext::setId($this->sucursalA->id);

        $cat = Categoria::create(['empresa_id' => $this->empresaA->id, 'nombre' => 'Bebidas']);
        $um = UnidadMedida::create(['empresa_id' => $this->empresaA->id, 'nombre' => 'Unidad', 'codigo' => 'UND', 'activo' => true]);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'categoria_id' => $cat->id,
            'unidad_medida_id' => $um->id,
            'codigo_barras' => 'BEB-001',
            'nombre' => 'Jugo Natural 500ml',
            'precio_compra' => 2000,
            'precio_venta' => 4500,
            'stock' => 50,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);
    }

    public function test_manifest_pwa_es_accesible_y_contiene_configuracion_valida(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $content = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($content);
        $this->assertEquals('sys-POS', $content['short_name']);
        $this->assertEquals('standalone', $content['display']);
    }

    public function test_service_worker_script_es_accesible(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);
        $this->assertStringContainsString('CACHE_NAME', file_get_contents($swPath));
    }

    public function test_sincronizacion_offline_procesa_lote_y_descuenta_inventario(): void
    {
        $clientTxId = (string) Str::uuid();

        $payload = [
            'ventas' => [
                [
                    'client_transaction_id' => $clientTxId,
                    'items' => [
                        [
                            'producto_id' => $this->productoA->id,
                            'cantidad' => 2,
                            'precio_unitario' => 4500,
                        ],
                    ],
                    'metodo_pago' => 'EFECTIVO',
                    'pago_con' => 10000,
                    'observaciones' => 'Venta registrada sin internet en mostrador 1',
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.sync-offline'), $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('data.procesadas', 1);
        $response->assertJsonPath('data.duplicadas', 0);
        $response->assertJsonPath('data.fallidas', 0);

        // Venta se registró en BD con marca offline
        $venta = Venta::where('client_transaction_id', $clientTxId)->first();
        $this->assertNotNull($venta);
        $this->assertTrue($venta->sincronizada_offline);
        $this->assertEquals(9000, (float) $venta->total);

        // Stock se descontó en inventario (50 - 2 = 48)
        $inv = Inventario::where('sucursal_id', $this->sucursalA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(48, (float) $inv->stock);
    }

    public function test_idempotencia_evita_duplicar_ventas_con_mismo_client_transaction_id(): void
    {
        $clientTxId = (string) Str::uuid();

        $payload = [
            'ventas' => [
                [
                    'client_transaction_id' => $clientTxId,
                    'items' => [
                        [
                            'producto_id' => $this->productoA->id,
                            'cantidad' => 3,
                            'precio_unitario' => 4500,
                        ],
                    ],
                ],
            ],
        ];

        // Primer envío: se procesa
        $resp1 = $this->actingAs($this->cajeroA)->postJson(route('pos.sync-offline'), $payload);
        $resp1->assertStatus(200);
        $resp1->assertJsonPath('data.procesadas', 1);

        $inv1 = Inventario::where('sucursal_id', $this->sucursalA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(47, (float) $inv1->stock);

        // Segundo envío con el mismo client_transaction_id (por ejemplo reconexión duplicada)
        $resp2 = $this->actingAs($this->cajeroA)->postJson(route('pos.sync-offline'), $payload);
        $resp2->assertStatus(200);
        $resp2->assertJsonPath('data.procesadas', 0);
        $resp2->assertJsonPath('data.duplicadas', 1);

        // El stock no se volvió a descontar
        $inv2 = Inventario::where('sucursal_id', $this->sucursalA->id)->where('producto_id', $this->productoA->id)->first();
        $this->assertEquals(47, (float) $inv2->stock);
    }

    public function test_aislamiento_multiempresa_en_sincronizacion_offline(): void
    {
        $clientTxId = (string) Str::uuid();

        // Cajero B (empresa B) intenta enviar producto de empresa A
        $payload = [
            'ventas' => [
                [
                    'client_transaction_id' => $clientTxId,
                    'items' => [
                        [
                            'producto_id' => $this->productoA->id,
                            'cantidad' => 1,
                            'precio_unitario' => 4500,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->cajeroB)->postJson(route('pos.sync-offline'), $payload);
        $response->assertStatus(200);
        // Debe fallar la sincronización para ese item porque el producto no pertenece a la empresa B
        $response->assertJsonPath('data.fallidas', 1);
        $response->assertJsonPath('data.procesadas', 0);
    }
}
