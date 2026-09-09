<?php

namespace Tests\Feature\Fase22;

use App\Enums\EstadoCaja;
use App\Enums\EstadoDian;
use App\Enums\EstadoDocumentoVenta;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoResolucionFacturacion;
use App\Enums\EstadoSesionCaja;
use App\Enums\RolSistema;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoDocumentoElectronico;
use App\Enums\TipoDocumentoVenta;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\DocumentoElectronico;
use App\Models\DocumentoVenta;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\ResolucionFacturacion;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Services\FacturacionElectronicaService;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacturacionElectronicaTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected Sucursal $sucursalB;
    protected User $adminA;
    protected User $adminB;
    protected ResolucionFacturacion $resolucionA;
    protected DocumentoVenta $documentoVentaA;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->seed(RolesAndPermissionsSeeder::class);

        // Empresa A
        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Alfa Fiscal',
            'nit' => '900111222-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Sede Principal Alfa',
            'codigo' => 'SUC-ALFA',
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

        // Empresa B
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Empresa Beta Fiscal',
            'nit' => '900333444-2',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombre' => 'Sede Principal Beta',
            'codigo' => 'SUC-BETA',
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

        // Contexto Empresa A
        CompanyContext::setCompany($this->empresaA);
        BranchContext::setId($this->sucursalA->id);

        $this->resolucionA = ResolucionFacturacion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'numero_resolucion' => '18764000001',
            'prefijo' => 'FE',
            'rango_desde' => 1,
            'rango_hasta' => 5000,
            'consecutivo_actual' => 0,
            'fecha_inicio' => now()->subMonth(),
            'fecha_vigencia' => now()->addYear(),
            'clave_tecnica' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e3811e8',
            'estado' => EstadoResolucionFacturacion::ACTIVA,
            'es_predeterminada' => true,
        ]);

        $caja = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja 1',
            'codigo' => 'CJ-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);

        $sesion = CajaSesion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'caja_id' => $caja->id,
            'user_id' => $this->adminA->id,
            'fecha_apertura' => now(),
            'monto_apertura' => 100000,
            'estado' => EstadoSesionCaja::ABIERTA,
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $this->empresaA->id,
            'tipo_documento' => 'NIT',
            'numero_documento' => '800123456-7',
            'razon_social' => 'Cliente Corporativo SAS',
        ]);

        $venta = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $cliente->id,
            'user_id' => $this->adminA->id,
            'caja_sesion_id' => $sesion->id,
            'numero_venta' => 'VTA-00001',
            'tipo_comprobante' => TipoComprobanteVenta::FACTURA,
            'fecha' => now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 1000000.00,
            'impuesto' => 190000.00,
            'total' => 1190000.00,
            'pago_con' => 1190000.00,
            'estado' => 'COMPLETADA',
        ]);

        $this->documentoVentaA = DocumentoVenta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'venta_id' => $venta->id,
            'cliente_id' => $cliente->id,
            'user_id' => $this->adminA->id,
            'tipo' => TipoDocumentoVenta::FACTURA,
            'estado' => EstadoDocumentoVenta::EMITIDO,
            'prefijo' => 'FAC',
            'numero' => 1,
            'numero_completo' => 'FAC-000001',
            'fecha_emision' => now(),
            'subtotal' => 1000000.00,
            'impuesto_total' => 190000.00,
            'total' => 1190000.00,
        ]);
    }

    public function test_admin_puede_crear_resolucion_facturacion(): void
    {
        $response = $this->actingAs($this->adminA)
            ->post(route('resoluciones.store'), [
                'numero_resolucion' => '18765000002',
                'prefijo' => 'SETT',
                'rango_desde' => 1,
                'rango_hasta' => 10000,
                'fecha_inicio' => now()->toDateString(),
                'fecha_vigencia' => now()->addYear()->toDateString(),
                'clave_tecnica' => '123456789abcdef',
                'estado' => EstadoResolucionFacturacion::ACTIVA->value,
                'es_predeterminada' => 0,
            ]);

        $response->assertRedirect(route('resoluciones.index'));
        $this->assertDatabaseHas('resoluciones_facturacion', [
            'empresa_id' => $this->empresaA->id,
            'numero_resolucion' => '18765000002',
            'prefijo' => 'SETT',
            'rango_hasta' => 10000,
        ]);
    }

    public function test_aislamiento_multiempresa_en_resoluciones_facturacion(): void
    {
        // Admin B no debe ver la resolución de A
        $response = $this->actingAs($this->adminB)
            ->get(route('resoluciones.index'));

        $response->assertOk();
        $response->assertDontSee('18764000001');
        $response->assertDontSee('FE');
    }

    public function test_resolucion_genera_consecutivos_y_detecta_agotamiento(): void
    {
        $res = ResolucionFacturacion::create([
            'empresa_id' => $this->empresaA->id,
            'numero_resolucion' => '18769999999',
            'prefijo' => 'TEST',
            'rango_desde' => 10,
            'rango_hasta' => 11,
            'consecutivo_actual' => 9,
            'fecha_inicio' => now()->subDay(),
            'fecha_vigencia' => now()->addMonth(),
            'estado' => EstadoResolucionFacturacion::ACTIVA,
        ]);

        $c1 = $res->obtenerSiguienteConsecutivo();
        $this->assertEquals(10, $c1);

        $c2 = $res->obtenerSiguienteConsecutivo();
        $this->assertEquals(11, $c2);

        // Agotamiento
        $this->expectException(\RuntimeException::class);
        $res->obtenerSiguienteConsecutivo();
    }

    public function test_emitir_factura_electronica_genera_cufe_xml_y_qr(): void
    {
        $service = app(FacturacionElectronicaService::class);

        $docElectronico = $service->emitirFacturaElectronica($this->documentoVentaA, $this->resolucionA);

        $this->assertInstanceOf(DocumentoElectronico::class, $docElectronico);
        $this->assertEquals(EstadoDian::ACEPTADO, $docElectronico->estado_dian);
        $this->assertEquals('FE-000001', $docElectronico->consecutivo_completo);
        $this->assertNotNull($docElectronico->cufe);
        $this->assertEquals(96, strlen($docElectronico->cufe)); // SHA-384 hex string is 96 chars
        $this->assertStringContainsString('CUFE=', $docElectronico->qr_data);
        $this->assertNotNull($docElectronico->xml_path);
        Storage::disk('local')->assertExists($docElectronico->xml_path);

        $this->assertDatabaseHas('documentos_electronicos', [
            'empresa_id' => $this->empresaA->id,
            'documento_venta_id' => $this->documentoVentaA->id,
            'consecutivo_completo' => 'FE-000001',
            'estado_dian' => EstadoDian::ACEPTADO->value,
        ]);
    }

    public function test_emision_via_controlador_web(): void
    {
        $response = $this->actingAs($this->adminA)
            ->post(route('facturacion-electronica.emitir', $this->documentoVentaA));

        $response->assertRedirect();
        $this->assertDatabaseHas('documentos_electronicos', [
            'empresa_id' => $this->empresaA->id,
            'documento_venta_id' => $this->documentoVentaA->id,
        ]);
    }

    public function test_emitir_nota_credito_electronica(): void
    {
        $service = app(FacturacionElectronicaService::class);
        $factura = $service->emitirFacturaElectronica($this->documentoVentaA, $this->resolucionA);

        $nc = $service->emitirNotaCredito($factura, 'Anulación de factura por acuerdo comercial');

        $this->assertEquals(TipoDocumentoElectronico::NOTA_CREDITO_ELECTRONICA, $nc->tipo);
        $this->assertEquals(EstadoDian::ACEPTADO, $nc->estado_dian);
        $this->assertEquals('NC-000001', $nc->consecutivo_completo);
        $this->assertNotNull($nc->cufe);

        $this->assertDatabaseHas('documentos_electronicos', [
            'empresa_id' => $this->empresaA->id,
            'tipo' => TipoDocumentoElectronico::NOTA_CREDITO_ELECTRONICA->value,
            'consecutivo_completo' => 'NC-000001',
        ]);
    }

    public function test_descargar_xml_ubl_factura_electronica(): void
    {
        $service = app(FacturacionElectronicaService::class);
        $factura = $service->emitirFacturaElectronica($this->documentoVentaA, $this->resolucionA);

        $response = $this->actingAs($this->adminA)
            ->get(route('facturacion-electronica.descargar-xml', $factura));

        $response->assertOk();
        $this->assertStringContainsString('xml', $response->headers->get('content-type'));
    }
}
