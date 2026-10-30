<?php

namespace Tests\Feature\Cotizaciones;

use App\Actions\Caja\AbrirCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCotizacion;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Http\Controllers\EnvioDocumentoController;
use App\Mail\DocumentoComercialMail;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Empresa;
use App\Models\EnlaceCompartido;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use App\Support\WhatsApp;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CotizacionTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;
    protected Empresa $empresaB;
    protected Sucursal $sucursalA;
    protected User $adminA;
    protected User $cajeroA;
    protected User $adminB;
    protected Cliente $clienteA;
    protected Producto $productoA;
    protected Caja $cajaA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create(['nombre_comercial' => 'Ferretería Alfa']);
        $this->empresaB = Empresa::factory()->create(['nombre_comercial' => 'Ferretería Beta']);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalA = Sucursal::factory()->create(['empresa_id' => $this->empresaA->id, 'es_principal' => true]);
        $sucursalB = Sucursal::factory()->create(['empresa_id' => $this->empresaB->id, 'es_principal' => true]);

        $this->adminA = User::factory()->create(['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajeroA = User::factory()->create(['empresa_id' => $this->empresaA->id, 'sucursal_id' => $this->sucursalA->id]);
        $this->cajeroA->assignRole(RolSistema::CAJERO->value);

        $this->adminB = User::factory()->create(['empresa_id' => $this->empresaB->id, 'sucursal_id' => $sucursalB->id]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->clienteA = Cliente::factory()->for($this->empresaA)->create([
            'razon_social' => 'Constructora Delta',
            'email' => 'compras@delta.test',
            'telefono' => '300 123 4567',
        ]);

        CompanyContext::setCompany($this->empresaA);
        $this->productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Cemento Gris 50kg',
            'codigo' => 'CEM-050',
            'precio_compra' => 20000.00,
            'precio_venta' => 30000.00,
            'iva' => 19,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        Inventario::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'producto_id' => $this->productoA->id,
            'stock' => 50,
            'stock_minimo' => 5,
        ]);
        $this->cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja 1',
            'codigo' => 'POS-01',
            'estado' => EstadoCaja::ACTIVA,
        ]);
        CompanyContext::clear();
    }

    protected function crearCotizacion(array $extra = []): Cotizacion
    {
        $this->actingAs($this->adminA)->post(route('cotizaciones.store'), array_merge([
            'cliente_id' => $this->clienteA->id,
            'fecha_validez' => now()->addDays(10)->toDateString(),
            'observaciones' => 'Entrega en obra',
            'items' => [
                ['producto_id' => $this->productoA->id, 'cantidad' => 10, 'descuento' => 20000],
            ],
        ], $extra))->assertRedirect();

        return Cotizacion::withoutGlobalScopes()->latest('id')->firstOrFail();
    }

    public function test_crear_cotizacion_calcula_con_precios_del_catalogo_y_no_toca_inventario(): void
    {
        $cotizacion = $this->crearCotizacion();

        // 10 x 30.000 = 300.000 - 20.000 desc = 280.000 + 19% IVA (53.200) = 333.200
        $this->assertEquals('COT-00001', $cotizacion->numero_cotizacion);
        $this->assertEquals(EstadoCotizacion::PENDIENTE, $cotizacion->estado);
        $this->assertEquals(300000.00, (float) $cotizacion->subtotal);
        $this->assertEquals(20000.00, (float) $cotizacion->descuento);
        $this->assertEquals(53200.00, (float) $cotizacion->impuesto);
        $this->assertEquals(333200.00, (float) $cotizacion->total);
        $this->assertDatabaseHas('cotizacion_detalles', [
            'cotizacion_id' => $cotizacion->id,
            'descripcion' => 'Cemento Gris 50kg',
            'precio_unitario' => 30000.00,
        ]);

        $this->assertDatabaseHas('inventarios', ['producto_id' => $this->productoA->id, 'stock' => 50]);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_listado_detalle_y_pdf_de_cotizacion(): void
    {
        $cotizacion = $this->crearCotizacion();

        $this->actingAs($this->adminA)->get(route('cotizaciones.index'))
            ->assertOk()->assertSee('COT-00001')->assertSee('Constructora Delta');

        $this->actingAs($this->adminA)->get(route('cotizaciones.show', $cotizacion))
            ->assertOk()->assertSee('Convertir en Venta')->assertSee('Abrir WhatsApp Web');

        $pdf = $this->actingAs($this->adminA)->get(route('cotizaciones.pdf', $cotizacion));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_formulario_de_cotizacion_carga(): void
    {
        $this->actingAs($this->cajeroA)->get(route('cotizaciones.create'))
            ->assertOk()->assertSee('Cemento Gris 50kg');
    }

    public function test_otra_empresa_no_puede_ver_ni_descargar_la_cotizacion(): void
    {
        $cotizacion = $this->crearCotizacion();

        $this->assertContains($this->actingAs($this->adminB)->get(route('cotizaciones.show', $cotizacion))->status(), [403, 404]);
        $this->assertContains($this->actingAs($this->adminB)->get(route('cotizaciones.pdf', $cotizacion))->status(), [403, 404]);
        $this->assertContains($this->actingAs($this->adminB)->post(route('cotizaciones.enviar-correo', $cotizacion), [
            'email' => 'x@y.test', 'mensaje' => 'x',
        ])->status(), [403, 404]);
    }

    public function test_anular_cotizacion_pendiente(): void
    {
        $cotizacion = $this->crearCotizacion();

        $this->actingAs($this->adminA)->post(route('cotizaciones.anular', $cotizacion))->assertRedirect();

        $this->assertEquals(EstadoCotizacion::ANULADA, $cotizacion->fresh()->estado);
    }

    public function test_pos_precarga_la_cotizacion_para_convertirla(): void
    {
        $cotizacion = $this->crearCotizacion();
        app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $this->actingAs($this->cajeroA)->get(route('pos.index', ['cotizacion' => $cotizacion->id]))
            ->assertOk()
            ->assertSee('COT-00001')
            ->assertSee('Convirtiendo la cotización', false);
    }

    public function test_convertir_cotizacion_en_venta_la_marca_convertida_y_descuenta_inventario(): void
    {
        $cotizacion = $this->crearCotizacion();
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);

        $response = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $sesion->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 400000,
            'cotizacion_id' => $cotizacion->id,
            'items' => [
                ['producto_id' => $this->productoA->id, 'cantidad' => 10, 'precio_unitario' => 30000, 'descuento' => 20000],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true, 'total' => 333200.00]);

        $cotizacion->refresh();
        $this->assertEquals(EstadoCotizacion::CONVERTIDA, $cotizacion->estado);
        $this->assertEquals($response->json('venta_id'), $cotizacion->venta_id);
        $this->assertNotNull($cotizacion->fecha_conversion);
        $this->assertDatabaseHas('inventarios', ['producto_id' => $this->productoA->id, 'stock' => 40]);
    }

    public function test_no_se_puede_convertir_dos_veces_la_misma_cotizacion(): void
    {
        $cotizacion = $this->crearCotizacion();
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);
        $payload = [
            'caja_sesion_id' => $sesion->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 400000,
            'cotizacion_id' => $cotizacion->id,
            'items' => [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 30000]],
        ];

        $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload)->assertOk();
        $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), $payload)->assertStatus(422);

        $this->assertEquals(1, Venta::withoutGlobalScopes()->count());
        $this->assertDatabaseHas('inventarios', ['producto_id' => $this->productoA->id, 'stock' => 49]);
    }

    public function test_enviar_cotizacion_por_correo_adjunta_el_pdf(): void
    {
        Mail::fake();
        $cotizacion = $this->crearCotizacion();

        $this->actingAs($this->adminA)->post(route('cotizaciones.enviar-correo', $cotizacion), [
            'email' => 'compras@delta.test',
            'mensaje' => 'Hola, adjuntamos la cotización.',
        ])->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(DocumentoComercialMail::class, function (DocumentoComercialMail $mail) {
            return $mail->hasTo('compras@delta.test')
                && $mail->pdfNombre === 'COT-00001.pdf'
                && str_starts_with($mail->pdfContenido, '%PDF');
        });
    }

    public function test_enviar_venta_por_correo_y_descargar_su_pdf(): void
    {
        Mail::fake();
        $sesion = app(AbrirCajaAction::class)->execute($this->cajaA, $this->cajeroA, 50000.00);
        $ventaId = $this->actingAs($this->cajeroA)->postJson(route('pos.procesar'), [
            'caja_sesion_id' => $sesion->id,
            'cliente_id' => $this->clienteA->id,
            'tipo_pago' => 'CONTADO',
            'metodo_pago' => 'EFECTIVO',
            'pago_con' => 40000,
            'items' => [['producto_id' => $this->productoA->id, 'cantidad' => 1, 'precio_unitario' => 30000]],
        ])->json('venta_id');
        $venta = Venta::withoutGlobalScopes()->findOrFail($ventaId);

        $this->actingAs($this->adminA)->get(route('ventas.show', $venta))->assertOk()->assertSee('Abrir WhatsApp Web');

        $pdf = $this->actingAs($this->adminA)->get(route('ventas.pdf', $venta));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->actingAs($this->adminA)->post(route('ventas.enviar-correo', $venta), [
            'email' => 'compras@delta.test',
            'mensaje' => 'Adjuntamos su factura.',
        ])->assertSessionHas('success');

        Mail::assertSent(DocumentoComercialMail::class, fn ($mail) => $mail->pdfNombre === $venta->numero_venta.'.pdf');
    }

    public function test_correo_invalido_es_rechazado(): void
    {
        Mail::fake();
        $cotizacion = $this->crearCotizacion();

        $this->actingAs($this->adminA)->post(route('cotizaciones.enviar-correo', $cotizacion), [
            'email' => 'no-es-un-correo',
            'mensaje' => 'Hola',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_enlace_corto_del_pdf_es_legible_vence_y_se_reutiliza(): void
    {
        $cotizacion = $this->crearCotizacion();
        auth()->logout();

        $enlace = EnvioDocumentoController::enlaceCompartido($cotizacion);

        // Formato corto y legible: /d/{token de 12}/COT-00001.pdf
        $this->assertMatchesRegularExpression('#/d/[A-Za-z0-9]{12}/COT-00001\.pdf$#', $enlace);
        $this->assertSame($enlace, EnvioDocumentoController::enlaceCompartido($cotizacion), 'Debe reutilizar el enlace vigente');
        $this->assertDatabaseCount('enlaces_compartidos', 1);

        $respuesta = $this->get($enlace);
        $respuesta->assertOk();
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
        $this->assertSame(1, EnlaceCompartido::first()->visitas);

        // Token inexistente: no se entrega nada
        $this->get(route('documentos.compartido', ['token' => 'NoExiste1234']))->assertStatus(410);

        // Enlace vencido
        $this->travel(EnlaceCompartido::DIAS_VIGENCIA + 1)->days();
        $this->get($enlace)->assertStatus(410);

        // Al compartir de nuevo se emite un enlace nuevo
        $this->assertNotSame($enlace, EnvioDocumentoController::enlaceCompartido($cotizacion));
    }

    public function test_mensaje_de_whatsapp_tiene_formato_y_enlace_corto(): void
    {
        $cotizacion = $this->crearCotizacion();

        $mensaje = EnvioDocumentoController::mensajes($cotizacion)['whatsapp'];
        $lineas = explode("\n", $mensaje);

        $this->assertSame('Hola *Constructora Delta*,', $lineas[0]);
        $this->assertContains('Te compartimos la *Cotización COT-00001* de *Ferretería Alfa*.', $lineas);
        $this->assertContains('Total: *$333.200*', $lineas);
        $this->assertContains('Válida hasta: '.now()->addDays(10)->format('d/m/Y'), $lineas);
        // El enlace va solo en su línea, corto y con el número del documento
        $this->assertCount(1, preg_grep('#^https?://\S+/d/[A-Za-z0-9]{12}/COT-00001\.pdf$#', $lineas));
        $this->assertStringNotContainsString('signature=', $mensaje);
        $this->assertDoesNotMatchRegularExpression('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $mensaje, 'El mensaje no debe llevar emojis');

        // La vista de la cotización ofrece el envío
        $this->actingAs($this->adminA)->get(route('cotizaciones.show', $cotizacion))->assertOk()->assertSee('Abrir WhatsApp Web');
    }

    public function test_normaliza_telefono_para_whatsapp(): void
    {
        $this->assertSame('573001234567', WhatsApp::normalizarTelefono('300 123 4567'));
        $this->assertSame('573001234567', WhatsApp::normalizarTelefono('+57 300-123-4567'));
        $this->assertSame('6041234567', WhatsApp::normalizarTelefono('604 123 4567'));
        $this->assertNull(WhatsApp::normalizarTelefono(''));
    }
}
