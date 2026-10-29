<?php

namespace Tests\Feature\Modulos;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\ProductoPresentacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modulos\Concerns\EscenarioComercial;
use Tests\TestCase;

/**
 * Acciones sensibles exigen el permiso correspondiente del catálogo PermisoSistema.
 * Se usa el rol con menos permisos que NO debería poder hacerlo y se verifica
 * que el rol que sí tiene el permiso conserva el acceso.
 */
class AutorizacionPorPermisoTest extends TestCase
{
    use EscenarioComercial;
    use RefreshDatabase;

    private User $vendedor;
    private User $contador;
    private User $adminSucursal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crearEscenarioComercial();

        $this->vendedor = $this->usuarioConRol(RolSistema::VENDEDOR);
        $this->contador = $this->usuarioConRol(RolSistema::CONTADOR);
        $this->adminSucursal = $this->usuarioConRol(RolSistema::ADMIN_SUCURSAL);
    }

    private function usuarioConRol(RolSistema $rol): User
    {
        $usuario = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresa->id);
        $usuario->assignRole($rol->value);

        return $usuario;
    }

    // --- Traslados entre sedes: mueven inventario (inventario.ajustar) ---

    public function test_traslados_requieren_permiso_de_inventario(): void
    {
        $this->actingAs($this->vendedor)->get(route('traslados.index'))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('traslados.create'))->assertForbidden();

        $this->actingAs($this->cajero)->post(route('traslados.store'), [
            'sucursal_origen_id' => $this->sucursal->id,
            'sucursal_destino_id' => $this->sucursalNorte->id,
            'motivo' => 'Intento sin permiso',
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1]],
        ])->assertForbidden();

        $this->actingAs($this->cajero)->post(route('traslados.recibir', $this->traslado))->assertForbidden();
        $this->actingAs($this->cajero)->post(route('traslados.rechazar', $this->traslado), ['motivo' => 'x'])->assertForbidden();

        $this->assertDatabaseCount('traslados_sucursal', 1);
        $this->actingAs($this->adminSucursal)->get(route('traslados.index'))->assertOk();
    }

    // --- Presentaciones de producto: definen precio de venta (productos.editar) ---

    public function test_presentaciones_requieren_permiso_de_edicion_de_productos(): void
    {
        $this->actingAs($this->cajero)->post(route('productos.presentaciones.store', $this->producto), [
            'nombre' => 'Caja x12',
            'factor_conversion' => 12,
            'precio_venta' => 1,
        ])->assertForbidden();

        $this->assertSame(0, ProductoPresentacion::count());
        $this->actingAs($this->adminSucursal)->get(route('productos.presentaciones.index', $this->producto))->assertOk();
    }

    // --- Reporte multisucursal: cifras financieras (reportes.ver) ---

    public function test_reporte_multisucursal_requiere_permiso_de_reportes(): void
    {
        $this->actingAs($this->cajero)->get(route('reportes.multisucursal'))->assertForbidden();
        $this->actingAs($this->vendedor)->get(route('reportes.multisucursal'))->assertForbidden();
        $this->actingAs($this->contador)->get(route('reportes.multisucursal'))->assertOk();
    }

    // --- Facturación electrónica ---

    public function test_facturacion_electronica_requiere_permisos_de_documentos(): void
    {
        $documentoVenta = $this->ventaContado->documentoVenta;

        // Emitir ante la DIAN: documentos.emitir (el vendedor y el contador no lo tienen)
        $this->actingAs($this->vendedor)->post(route('facturacion-electronica.emitir', $documentoVenta))->assertForbidden();
        $this->actingAs($this->contador)->post(route('facturacion-electronica.emitir', $documentoVenta))->assertForbidden();

        // Nota crédito: revierte ingresos ante la DIAN, documentos.anular (el cajero no lo tiene)
        $this->actingAs($this->cajero)
            ->post(route('facturacion-electronica.nota-credito', $this->documentoElectronico), ['motivo' => 'Intento sin permiso'])
            ->assertForbidden();

        $this->assertDatabaseCount('documentos_electronicos', 1);

        // Consultar: documentos.ver
        $this->actingAs($this->contador)->get(route('facturacion-electronica.index'))->assertOk();
        $this->actingAs($this->contador)->get(route('facturacion-electronica.show', $this->documentoElectronico))->assertOk();
    }

    // --- Impresiones de caja y abonos ---

    public function test_impresion_de_cierre_de_caja_requiere_permiso_de_caja(): void
    {
        $this->actingAs($this->vendedor)->get(route('imprimir.caja', $this->sesionCaja))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('imprimir.caja', $this->sesionCaja))->assertOk();
    }

    // --- Productos y catálogos: productos.ver (el contador no lo tiene) ---

    public function test_listado_de_productos_y_catalogos_requieren_permiso_de_productos(): void
    {
        $this->actingAs($this->contador)->get(route('productos.index'))->assertForbidden();
        $this->actingAs($this->contador)->get(route('catalogos.index'))->assertForbidden();

        $this->actingAs($this->vendedor)->get(route('productos.index'))->assertOk();
        $this->actingAs($this->vendedor)->get(route('catalogos.index'))->assertOk();
    }

    // --- Suscripción y pagos del SaaS: solo quien gestiona la empresa ---

    public function test_suscripcion_requiere_permiso_de_gestion_de_empresa(): void
    {
        $plan = \App\Models\Plan::where('slug', 'empresarial')->firstOrFail();

        $this->actingAs($this->cajero)->get(route('suscripcion.index'))->assertForbidden();
        $this->actingAs($this->cajero)->get(route('suscripcion.checkout', $plan))->assertForbidden();
        $this->actingAs($this->admin)->get(route('suscripcion.index'))->assertOk();
    }

    // --- Sincronización de ventas offline del POS: ventas.crear ---

    public function test_sincronizacion_offline_requiere_permiso_de_ventas(): void
    {
        $this->actingAs($this->contador)
            ->postJson(route('pos.sync-offline'), ['ventas' => []])
            ->assertForbidden();
    }
}
