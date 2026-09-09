<?php

namespace Tests\Feature\Fase21;

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\RolSistema;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $userA;
    protected User $userB;
    protected string $tokenA;
    protected string $tokenB;
    protected Producto $productoA;
    protected Producto $productoB;
    protected Caja $cajaA;
    protected CajaSesion $sesionA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa API',
            'nit' => '901888999-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sucursal API A',
            'codigo' => 'SUC-API-A',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->userA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'API Admin Alfa',
            'email' => 'api.admin@alfa.test',
            'password' => bcrypt('password123'),
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->userA->assignRole(RolSistema::ADMIN_EMPRESA->value);
        $this->tokenA = $this->userA->createToken('TestTokenA')->plainTextToken;

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta API',
            'nit' => '902999000-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sucursal API B',
            'codigo' => 'SUC-API-B',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->userB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'API Admin Beta',
            'email' => 'api.admin@beta.test',
            'password' => bcrypt('password123'),
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->userB->assignRole(RolSistema::ADMIN_EMPRESA->value);
        $this->tokenB = $this->userB->createToken('TestTokenB')->plainTextToken;

        // Caja y Sesión para A
        CompanyContext::setCompany($this->empresaA);

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja POS API',
            'codigo' => 'CAJA-API-1',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $this->sesionA = CajaSesion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'caja_id' => $this->cajaA->id,
            'user_id' => $this->userA->id,
            'fecha_apertura' => now(),
            'monto_apertura' => 50000.00,
            'estado' => EstadoSesionCaja::ABIERTA,
        ]);

        // Productos
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Laptop Gamer Pro',
            'codigo' => 'LAP-001',
            'precio_compra' => 2000000.00,
            'precio_venta' => 3000000.00,
            'stock' => 15,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 15, 'costo_promedio_ponderado' => 2000000.00]
        );

        CompanyContext::setCompany($this->empresaB);
        $this->productoB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Teclado Mecánico RGB',
            'codigo' => 'TEC-B01',
            'precio_compra' => 50000.00,
            'precio_venta' => 100000.00,
            'stock' => 20,
            'stock_minimo' => 5,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
    }

    public function test_login_con_credenciales_validas_retorna_token(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'api.admin@alfa.test',
            'password' => 'password123',
            'device_name' => 'MobileApp',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'empresa_id'],
            ],
        ]);
    }

    public function test_login_con_credenciales_invalidas_retorna_401(): void
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'api.admin@alfa.test',
            'password' => 'clave_incorrecta',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    public function test_me_retorna_informacion_del_usuario_autenticado(): void
    {
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'api.admin@alfa.test',
                    'empresa_id' => $this->empresaA->id,
                ],
            ],
        ]);
    }

    public function test_consultar_empresa_autenticada(): void
    {
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.empresa.show'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'nombre_comercial' => 'Empresa Alfa API',
                'nit' => '901888999-1',
            ],
        ]);
    }

    public function test_listar_productos_aplica_aislamiento_multiempresa(): void
    {
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.productos.index'));

        $response->assertOk();
        $response->assertSee('Laptop Gamer Pro');
        $response->assertDontSee('Teclado Mecánico RGB'); // Producto de Empresa B
    }

    public function test_crear_y_consultar_cliente_via_api(): void
    {
        $data = [
            'tipo_documento' => 'NIT',
            'numero_documento' => '901444555-8',
            'razon_social' => 'Distribuidora Tecnológica SAS',
            'email' => 'contacto@distritech.test',
            'telefono' => '3001234567',
        ];

        Sanctum::actingAs($this->userA);
        $response = $this->postJson(route('api.v1.clientes.store'), $data);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'numero_documento' => '901444555-8',
                'empresa_id' => $this->empresaA->id,
            ],
        ]);

        $this->assertDatabaseHas('clientes', [
            'empresa_id' => $this->empresaA->id,
            'numero_documento' => '901444555-8',
        ]);
    }

    public function test_registrar_venta_pos_via_api(): void
    {
        $data = [
            'sucursal_id' => $this->sucursalA->id,
            'tipo_pago' => TipoPago::CONTADO->value,
            'metodo_pago' => 'EFECTIVO',
            'caja_sesion_id' => $this->sesionA->id,
            'pago_con' => 3000000.00,
            'items' => [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 3000000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
        ];

        Sanctum::actingAs($this->userA);
        $response = $this->postJson(route('api.v1.ventas.store'), $data);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total' => '3570000.00',
                'empresa_id' => $this->empresaA->id,
            ],
        ]);

        // Verificar que se emitió el documento comercial formal automáticamente (Fase 18)
        $this->assertDatabaseHas('documentos_venta', [
            'empresa_id' => $this->empresaA->id,
            'total' => 3570000.00,
        ]);
    }

    public function test_consultar_estado_caja_via_api(): void
    {
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.caja.estado'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'caja_abierta' => true,
            ],
        ]);
    }

    public function test_resumen_reportes_financieros_via_api(): void
    {
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.reportes.resumen'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'ventas_hoy',
                'ventas_mes',
                'tickets_hoy',
                'ticket_promedio',
                'cartera_pendiente',
                'total_productos',
                'total_clientes',
            ],
        ]);
    }

    public function test_aislamiento_multitenant_usuario_a_no_puede_acceder_a_producto_empresa_b(): void
    {
        // Usuario A intenta ver detalle del producto de B
        Sanctum::actingAs($this->userA);
        $response = $this->getJson(route('api.v1.productos.show', $this->productoB));

        // Debe retornar 404 (gracias al global scope de BelongsToCompany activado por InitializeApiCompanyContext)
        $this->assertContains($response->status(), [403, 404]);
    }
}
