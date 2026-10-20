<?php

namespace Tests\Feature\AuditoriaNocturna;

use App\Actions\Caja\AbrirCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCompra;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoResolucionFacturacion;
use App\Enums\EstadoTraslado;
use App\Enums\EstadoVenta;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoImpuesto;
use App\Enums\TipoPago;
use App\Enums\TipoPersona;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\ListaPrecio;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ResolucionFacturacion;
use App\Models\Sucursal;
use App\Models\TrasladoSucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarridoMultiempresaRutasTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresaA;

    protected Empresa $empresaB;

    protected Sucursal $sucursalA;

    protected Sucursal $sucursalA2;

    protected Sucursal $sucursalB;

    protected User $adminA;

    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->empresaA = Empresa::factory()->create([
            'nombre_comercial' => 'Tenant A Protegido S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->empresaB = Empresa::factory()->create([
            'nombre_comercial' => 'Tenant B Atacante S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaA);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresaB);

        $this->sucursalA = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalA2 = Sucursal::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'es_principal' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalB = Sucursal::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->adminA = User::factory()->create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresaA->id);
        $this->adminA->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->adminB = User::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'sucursal_id' => $this->sucursalB->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        setPermissionsTeamId($this->empresaB->id);
        $this->adminB->assignRole(RolSistema::ADMIN_EMPRESA->value);

        CompanyContext::clear();
    }

    protected function tearDown(): void
    {
        CompanyContext::clear();
        parent::tearDown();
    }

    protected function assertCrossTenantBlocked(int $statusCode, string $context): void
    {
        $this->assertTrue(
            in_array($statusCode, [403, 404], true),
            "Fuga multiempresa detectada en [{$context}]: se esperaba HTTP 403 o 404 pero respondió {$statusCode}."
        );
    }

    public function test_barrido_idor_catalogo_productos_categorias_marcas_unidades_e_inventario(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $categoriaA = Categoria::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Herramientas A',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $marcaA = Marca::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Stanley A',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $unidadA = UnidadMedida::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Unidad A',
            'codigo' => 'UND-A',
            'abreviatura' => 'UND',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $productoA = Producto::create([
            'empresa_id' => $this->empresaA->id,
            'categoria_id' => $categoriaA->id,
            'marca_id' => $marcaA->id,
            'unidad_medida_id' => $unidadA->id,
            'nombre' => 'Taladro Industrial A',
            'codigo' => 'TAL-A1',
            'precio_venta' => 180000,
            'stock' => 15,
            'stock_minimo' => 3,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        CompanyContext::clear();

        // Admin B intenta editar, actualizar, eliminar y ver kardex del producto de Empresa A
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('productos.edit', $productoA))->getStatusCode(),
            'GET productos.edit'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->put(route('productos.update', $productoA), ['nombre' => 'Sabotaje'])->getStatusCode(),
            'PUT productos.update'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('productos.destroy', $productoA))->getStatusCode(),
            'DELETE productos.destroy'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('inventario.kardex', $productoA))->getStatusCode(),
            'GET inventario.kardex'
        );

        // Catálogos auxiliares de Empresa A
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('catalogos.categorias.destroy', $categoriaA))->getStatusCode(),
            'DELETE catalogos.categorias.destroy'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('catalogos.marcas.destroy', $marcaA))->getStatusCode(),
            'DELETE catalogos.marcas.destroy'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('catalogos.unidades.destroy', $unidadA))->getStatusCode(),
            'DELETE catalogos.unidades.destroy'
        );
    }

    public function test_barrido_idor_terceros_clientes_proveedores_y_sucursales(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $clienteA = Cliente::create([
            'empresa_id' => $this->empresaA->id,
            'tipo_persona' => TipoPersona::JURIDICA,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900111222-3',
            'razon_social' => 'Constructora A S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $proveedorA = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '800333444-5',
            'razon_social' => 'Importadora A S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        CompanyContext::clear();

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('clientes.edit', $clienteA))->getStatusCode(),
            'GET clientes.edit'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->put(route('clientes.update', $clienteA), ['razon_social' => 'Hack'])->getStatusCode(),
            'PUT clientes.update'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('clientes.destroy', $clienteA))->getStatusCode(),
            'DELETE clientes.destroy'
        );

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->put(route('proveedores.update', $proveedorA), ['razon_social' => 'Hack'])->getStatusCode(),
            'PUT proveedores.update'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('proveedores.destroy', $proveedorA))->getStatusCode(),
            'DELETE proveedores.destroy'
        );

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->put(route('sucursales.update', $this->sucursalA), ['nombre' => 'Hack'])->getStatusCode(),
            'PUT sucursales.update'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('sucursales.destroy', $this->sucursalA2))->getStatusCode(),
            'DELETE sucursales.destroy'
        );
    }

    public function test_barrido_idor_cajas_sesiones_y_comprobantes(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $cajaA = Caja::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'nombre' => 'Caja Privada A',
            'codigo' => 'CAJ-A1',
            'estado' => EstadoCaja::ACTIVA,
        ]);
        $sesionA = app(AbrirCajaAction::class)->execute($cajaA, $this->adminA, 100000);
        CompanyContext::clear();

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('cajas.show', $cajaA))->getStatusCode(),
            'GET cajas.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('cajas.cierre', $cajaA))->getStatusCode(),
            'GET cajas.cierre'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('cajas.cierre.store', $cajaA), ['monto_cierre_contado' => 0])->getStatusCode(),
            'POST cajas.cierre.store'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('cajas.comprobante', $sesionA))->getStatusCode(),
            'GET cajas.comprobante'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('imprimir.caja', $sesionA))->getStatusCode(),
            'GET imprimir.caja'
        );
    }

    public function test_barrido_idor_ventas_compras_cartera_y_devoluciones(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $clienteA = Cliente::create([
            'empresa_id' => $this->empresaA->id,
            'tipo_persona' => TipoPersona::JURIDICA,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900555666-7',
            'razon_social' => 'Cliente Cartera A S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $proveedorA = Proveedor::create([
            'empresa_id' => $this->empresaA->id,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '800777888-9',
            'razon_social' => 'Proveedor Compras A S.A.S.',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $ventaA = Venta::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'user_id' => $this->adminA->id,
            'cliente_id' => $clienteA->id,
            'numero_venta' => 'VEN-A-0001',
            'fecha' => now(),
            'tipo_pago' => TipoPago::CONTADO,
            'metodo_pago' => 'EFECTIVO',
            'subtotal' => 50000,
            'descuento' => 0,
            'impuesto' => 0,
            'total' => 50000,
            'pago_con' => 50000,
            'cambio' => 0,
            'estado' => EstadoVenta::COMPLETADA,
        ]);

        $compraA = Compra::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'user_id' => $this->adminA->id,
            'proveedor_id' => $proveedorA->id,
            'numero_factura' => 'FAC-991',
            'fecha_emision' => now(),
            'tipo_pago' => TipoPago::CONTADO,
            'subtotal' => 40000,
            'impuestos' => 0,
            'descuento' => 0,
            'total' => 40000,
            'estado' => EstadoCompra::REGISTRADA,
        ]);

        $cuentaA = CuentaPorCobrar::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'cliente_id' => $clienteA->id,
            'numero_documento' => 'CXC-A-0001',
            'concepto' => 'Factura a crédito',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(30),
            'monto_total' => 50000,
            'monto_pagado' => 0,
            'saldo_pendiente' => 50000,
            'estado' => EstadoCuentaCobrar::PENDIENTE,
        ]);
        CompanyContext::clear();

        // Ventas
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('ventas.show', $ventaA))->getStatusCode(),
            'GET ventas.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('ventas.ticket', $ventaA))->getStatusCode(),
            'GET ventas.ticket'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('ventas.anular', $ventaA), ['motivo_anulacion' => 'Hack'])->getStatusCode(),
            'POST ventas.anular'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('devoluciones.create', $ventaA))->getStatusCode(),
            'GET devoluciones.create'
        );

        // Compras
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('compras.show', $compraA))->getStatusCode(),
            'GET compras.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('compras.anular', $compraA), ['motivo' => 'Hack'])->getStatusCode(),
            'POST compras.anular'
        );

        // Cartera
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('cartera.show', $cuentaA))->getStatusCode(),
            'GET cartera.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('cartera.abono.store', $cuentaA), ['monto' => 1000, 'metodo_pago' => 'EFECTIVO'])->getStatusCode(),
            'POST cartera.abono.store'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('cartera.estado-cuenta', $clienteA))->getStatusCode(),
            'GET cartera.estado-cuenta'
        );
    }

    public function test_barrido_idor_configuracion_listas_precios_impuestos_resoluciones_traslados_y_usuarios(): void
    {
        CompanyContext::setCompany($this->empresaA);
        $listaA = ListaPrecio::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'Mayorista A',
            'codigo' => 'MAY-A',
            'tipo' => 'PORCENTAJE_DESCUENTO',
            'porcentaje' => 10,
            'activa' => true,
        ]);
        $impuestoA = Impuesto::create([
            'empresa_id' => $this->empresaA->id,
            'nombre' => 'IVA 19 A',
            'codigo' => 'IVA19A',
            'porcentaje' => 19,
            'tipo' => TipoImpuesto::IVA,
            'activo' => true,
        ]);
        $resolucionA = ResolucionFacturacion::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_id' => $this->sucursalA->id,
            'numero_resolucion' => '18760000001',
            'prefijo' => 'SETT',
            'rango_desde' => 1,
            'rango_hasta' => 5000,
            'consecutivo_actual' => 1,
            'fecha_inicio' => now()->subMonth(),
            'fecha_vigencia' => now()->addYear(),
            'clave_tecnica' => 'clave-test',
            'estado' => EstadoResolucionFacturacion::ACTIVA,
            'es_predeterminada' => true,
        ]);
        $trasladoA = TrasladoSucursal::create([
            'empresa_id' => $this->empresaA->id,
            'sucursal_origen_id' => $this->sucursalA->id,
            'sucursal_destino_id' => $this->sucursalA2->id,
            'user_id' => $this->adminA->id,
            'consecutivo' => 'TR-A-001',
            'motivo' => 'Abastecimiento sede norte',
            'fecha_envio' => now(),
            'estado' => EstadoTraslado::EN_TRANSITO,
        ]);
        CompanyContext::clear();

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('listas-precios.show', $listaA))->getStatusCode(),
            'GET listas-precios.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('listas-precios.destroy', $listaA))->getStatusCode(),
            'DELETE listas-precios.destroy'
        );

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('impuestos.edit', $impuestoA))->getStatusCode(),
            'GET impuestos.edit'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('impuestos.destroy', $impuestoA))->getStatusCode(),
            'DELETE impuestos.destroy'
        );

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('resoluciones.edit', $resolucionA))->getStatusCode(),
            'GET resoluciones.edit'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('resoluciones.destroy', $resolucionA))->getStatusCode(),
            'DELETE resoluciones.destroy'
        );

        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('traslados.show', $trasladoA))->getStatusCode(),
            'GET traslados.show'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('traslados.recibir', $trasladoA))->getStatusCode(),
            'POST traslados.recibir'
        );

        // Usuario de Empresa A atacado por Admin de Empresa B
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->get(route('usuarios.edit', $this->adminA))->getStatusCode(),
            'GET usuarios.edit'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->post(route('usuarios.toggle-estado', $this->adminA))->getStatusCode(),
            'POST usuarios.toggle-estado'
        );
        $this->assertCrossTenantBlocked(
            $this->actingAs($this->adminB)->delete(route('usuarios.destroy', $this->adminA))->getStatusCode(),
            'DELETE usuarios.destroy'
        );
    }
}
