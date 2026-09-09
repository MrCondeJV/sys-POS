<?php

namespace Tests\Feature\Fase18;

use App\Actions\Ventas\AnularVentaAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoDocumentoVenta;
use App\Enums\EstadoGeneral;
use App\Enums\PermisoSistema;
use App\Enums\RolSistema;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoDocumentoVenta;
use App\Enums\TipoPago;
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

class DocumentoVentaTest extends TestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Docs',
            'nit' => '901555666-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sucursal Principal Alfa',
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
            'nombre_comercial' => 'Empresa Beta Docs',
            'nit' => '902777888-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sucursal Principal Beta',
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

        // Producto y Cliente en Empresa A
        CompanyContext::setCompany($this->empresaA);

        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Taladro Percutor 750W',
            'codigo' => 'TAL-750',
            'precio_compra' => 100000.00,
            'precio_venta' => 150000.00,
            'stock' => 50,
            'stock_minimo' => 5,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id, 'producto_id' => $this->productoA->id],
            ['stock' => 50, 'costo_promedio_ponderado' => 100000.00]
        );

        $this->clienteA = Cliente::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'razon_social' => 'Industrias Metálicas SAS',
            'numero_documento' => '900123456-7',
        ]);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $response = $this->get(route('documentos.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_puede_listar_documentos(): void
    {
        $response = $this->actingAs($this->adminA)->get(route('documentos.index'));
        $response->assertOk();
        $response->assertSee('Documentos Comerciales');
    }

    public function test_venta_genera_automaticamente_documento_comercial_ticket(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $action = app(RegistrarVentaAction::class);
        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 2,
                    'precio_unitario' => 150000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        $this->assertDatabaseHas('documentos_venta', [
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'tipo' => TipoDocumentoVenta::TICKET->value,
            'estado' => EstadoDocumentoVenta::EMITIDO->value,
            'numero' => 1,
            'numero_completo' => 'TIK-000001',
        ]);

        $this->assertNotNull($venta->documentoVenta);
        $this->assertEquals('TIK-000001', $venta->documentoVenta->numero_completo);
        $this->assertTrue($venta->documentoVenta->esEmitido());
    }

    public function test_venta_factura_genera_documento_tipo_factura_con_consecutivo(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $action = app(RegistrarVentaAction::class);
        $venta = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [
                [
                    'producto_id' => $this->productoA->id,
                    'cantidad' => 1,
                    'precio_unitario' => 150000.00,
                    'impuesto_porcentaje' => 19,
                ],
            ],
            clienteId: $this->clienteA->id,
            tipoPago: TipoPago::CONTADO,
            tipoComprobante: TipoComprobanteVenta::FACTURA
        );

        $this->assertDatabaseHas('documentos_venta', [
            'empresa_id' => $this->empresaA->id,
            'venta_id' => $venta->id,
            'tipo' => TipoDocumentoVenta::FACTURA->value,
            'estado' => EstadoDocumentoVenta::EMITIDO->value,
            'numero' => 1,
            'numero_completo' => 'FAC-000001',
        ]);
    }

    public function test_consecutivos_incrementan_secuencialmente_por_tipo(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $action = app(RegistrarVentaAction::class);

        // Venta 1: Ticket
        $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 150000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        // Venta 2: Ticket
        $venta2 = $action->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 150000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        $this->assertEquals('TIK-000002', $venta2->documentoVenta->numero_completo);
        $this->assertEquals(2, $venta2->documentoVenta->numero);
    }

    public function test_anulacion_de_venta_anula_documento_comercial_vinculado(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 150000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        $docId = $venta->documentoVenta->id;

        // Anular la venta
        app(AnularVentaAction::class)->execute(
            venta: $venta,
            usuarioAnulacion: $this->adminA,
            motivo: 'Cliente desistió de la compra'
        );

        $docActualizado = DocumentoVenta::withoutGlobalScopes()->find($docId);
        $this->assertEquals(EstadoDocumentoVenta::ANULADO, $docActualizado->estado);
        $this->assertStringContainsString('Cliente desistió de la compra', $docActualizado->observaciones);
    }

    public function test_anulacion_manual_de_documento(): void
    {
        CompanyContext::setCompany($this->empresaA);

        $venta = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaA->id,
            sucursalId: $this->sucursalA->id,
            userId: $this->adminA->id,
            items: [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 150000.00]],
            clienteId: $this->clienteA->id,
            tipoComprobante: TipoComprobanteVenta::TICKET
        );

        $doc = $venta->documentoVenta;

        $response = $this->actingAs($this->adminA)->post(route('documentos.anular', $doc), [
            'motivo' => 'Error en datos fiscales del comprobante',
        ]);

        $response->assertRedirect(route('documentos.show', $doc));
        $this->assertDatabaseHas('documentos_venta', [
            'id' => $doc->id,
            'estado' => EstadoDocumentoVenta::ANULADO->value,
        ]);
    }

    public function test_aislamiento_multitenant_empresa_a_no_puede_ver_ni_anular_documento_empresa_b(): void
    {
        CompanyContext::setCompany($this->empresaB);

        $prodB = Producto::create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Producto Beta',
            'codigo' => 'PROD-B1',
            'precio_compra' => 5000,
            'precio_venta' => 10000,
            'stock' => 10,
            'stock_minimo' => 2,
            'impuesto_porcentaje' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::updateOrCreate(
            ['empresa_id' => $this->empresaB->id, 'sucursal_id' => $this->sucursalB->id, 'producto_id' => $prodB->id],
            ['stock' => 10, 'costo_promedio_ponderado' => 5000]
        );

        $ventaB = app(RegistrarVentaAction::class)->execute(
            empresaId: $this->empresaB->id,
            sucursalId: $this->sucursalB->id,
            userId: $this->adminB->id,
            items: [['producto_id' => $prodB->id, 'cantidad' => 1, 'precio_unitario' => 10000]],
            tipoComprobante: TipoComprobanteVenta::FACTURA
        );

        $docB = $ventaB->documentoVenta;

        // Admin A intenta ver Documento B -> 404 o 403
        $responseGet = $this->actingAs($this->adminA)->get(route('documentos.show', $docB));
        $this->assertContains($responseGet->status(), [403, 404]);

        // Admin A intenta anular Documento B -> 404 o 403
        $responsePost = $this->actingAs($this->adminA)->post(route('documentos.anular', $docB), [
            'motivo' => 'Intrusión no autorizada',
        ]);
        $this->assertContains($responsePost->status(), [403, 404]);
    }
}
