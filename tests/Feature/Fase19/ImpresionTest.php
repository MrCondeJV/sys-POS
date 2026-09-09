<?php

namespace Tests\Feature\Fase19;

use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoSesionCaja;
use App\Enums\FormatoImpresion;
use App\Enums\RolSistema;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\DocumentoVenta;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpresionTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $cajeroA;
    protected User $adminB;
    protected Producto $productoA;
    protected Cliente $clienteA;
    protected Caja $cajaA;
    protected CajaSesion $sesionA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Print',
            'nit' => '901999888-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Alfa Print',
            'codigo' => 'SUC-A1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Admin Alfa',
            'email' => 'admin@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'name' => 'Cajero Alfa',
            'email' => 'cajero@alfa.test',
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta Print',
            'nit' => '902111222-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Beta Print',
            'codigo' => 'SUC-B1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'name' => 'Admin Beta',
            'email' => 'admin@beta.test',
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        // Caja y Sesión para A
        CompanyContext::setCompany($this->empresaA);

        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Principal A',
            'codigo' => 'CAJA-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $this->sesionA = CajaSesion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'caja_id' => $this->cajaA->id,
            'user_id' => $this->adminA->id,
            'fecha_apertura' => now(),
            'monto_inicial' => 100000.00,
            'estado' => EstadoSesionCaja::ABIERTA,
        ]);

        // Producto y Cliente en A
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sierra Circular 1400W',
            'codigo' => 'SIE-1400',
            'precio_compra' => 120000.00,
            'precio_venta' => 200000.00,
            'stock' => 30,
            'stock_minimo' => 3,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 30, 'costo_promedio_ponderado' => 120000.00]
        );

        $this->clienteA = Cliente::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Ferretería El Progreso SAS',
            'numero_documento' => '900888777-3',
        ]);
    }

    public function test_invitados_redirigen_al_login(): void
    {
        $response = $this->get(route('imprimir.documento', 1));
        $response->assertRedirect(route('login'));

        $responseVenta = $this->get(route('imprimir.venta', 1));
        $responseVenta->assertRedirect(route('login'));
    }

    public function test_imprimir_documento_en_formatos_58mm_80mm_y_carta(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 200000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::FACTURA
        );

        $doc = $venta->documentoVenta;
        $this->assertNotNull($doc);

        // 1. Formato 58mm
        $resp58 = $this->actingAs($this->adminA)->get(route('imprimir.documento', ['documento' => $doc, 'formato' => '58mm']));
        $resp58->assertOk();
        $resp58->assertSee('Ticket 58mm');
        $resp58->assertSee('Sierra Circular 1400W');
        $resp58->assertSee('FAC-000001');

        // 2. Formato 80mm
        $resp80 = $this->actingAs($this->adminA)->get(route('imprimir.documento', ['documento' => $doc, 'formato' => '80mm']));
        $resp80->assertOk();
        $resp80->assertSee('Ticket 80mm');
        $resp80->assertSee('FAC-000001');
        $resp80->assertSee('Ferretería El Progreso SAS');

        // 3. Formato Carta / PDF
        $respCarta = $this->actingAs($this->adminA)->get(route('imprimir.documento', ['documento' => $doc, 'formato' => 'carta']));
        $respCarta->assertOk();
        $respCarta->assertSee('Factura Carta');
        $respCarta->assertSee('TOTAL A PAGAR');
        $respCarta->assertSee('FAC-000001');
    }

    public function test_imprimir_venta_directa(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->cajeroA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 2, 'precio_unitario' => 200000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        $response = $this->actingAs($this->cajeroA)->get(route('imprimir.venta', ['venta' => $venta, 'formato' => '80mm']));
        $response->assertOk();
        $response->assertSee($venta->numero_venta);
        $response->assertSee('Ticket 80mm');
    }

    public function test_imprimir_cierre_caja(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $response = $this->actingAs($this->adminA)->get(route('imprimir.caja', $this->sesionA));
        $response->assertOk();
        $response->assertSee('COMPROBANTE DE CIERRE DE CAJA');
        $response->assertSee('CAJA-01');
        $response->assertSee('Fondo Inicial');
    }

    public function test_imprimir_recibo_abono_cartera(): void
    {
        CompanyContext::setCompany($this->empresaA);

        // Crear una cuenta por cobrar
        $cxc = app(RegistrarCuentaPorCobrarAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            clienteId: $this->clienteA->id,
            montoTotal: 500000.00,
            fechaEmision: now()->toDateString(),
            fechaVencimiento: now()->addDays(30)->toDateString(),
            concepto: 'Factura a Crédito de Prueba',
            userId: $this->adminA->id
        );

        // Registrar abono
        $abono = app(RegistrarAbonoCarteraAction::class)->execute(
            cuenta: $cxc,
            monto: 200000.00,
            metodoPago: \App\Enums\MetodoPagoCartera::TRANSFERENCIA,
            fechaPago: now()->toDateString(),
            referenciaPago: 'TR-12345',
            notas: 'Abono parcial transferencia Bancolombia',
            userId: $this->adminA->id
        );

        $response = $this->actingAs($this->adminA)->get(route('imprimir.abono', $abono));
        $response->assertOk();
        $response->assertSee('RECIBO DE CAJA / ABONO');
        $response->assertSee('MONTO ABONADO');
        $response->assertSee('$200,000.00');
    }

    public function test_aislamiento_multitenant_empresa_a_no_puede_imprimir_recursos_empresa_b(): void
    {
        CompanyContext::setCompany($this->empresaB);

        $prodB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Producto Beta Print',
            'codigo' => 'BETA-PR',
            'precio_compra' => 10000,
            'precio_venta' => 25000,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaB->id, 'sucursal_id' => $this->sucursalB->id, 'producto_id' => $prodB->id],
            ['stock' => 10, 'costo_promedio_ponderado' => 10000]
        );

        $ventaB = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaB->id,
            sucursalId: $this->sucursalB->id,
            userId: $this->adminB->id,
            items: [['producto_id' => $prodB->id, 'cantidad' => 1, 'precio_unitario' => 25000]],
            tipoComprobante: TipoComprobanteVenta::FACTURA
        );

        $docB = $ventaB->documentoVenta;

        // Admin A intenta imprimir Documento B -> 404 o 403
        $respDoc = $this->actingAs($this->adminA)->get(route('imprimir.documento', $docB));
        $this->assertContains($respDoc->status(), [403, 404]);

        // Admin A intenta imprimir Venta B -> 404 o 403
        $respVenta = $this->actingAs($this->adminA)->get(route('imprimir.venta', $ventaB));
        $this->assertContains($respVenta->status(), [403, 404]);
    }
}
