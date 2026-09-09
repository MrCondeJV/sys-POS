<?php

namespace Tests\Feature\Fase28;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Compras\RegistrarCompraAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\DTOs\CompraItemDTO;
use App\DTOs\RegistrarCompraDTO;
use App\Enums\EstadoSesionCaja;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoGeneral;
use App\Enums\MetodoPagoCartera;
use App\Enums\RolSistema;
use App\Enums\TipoPago;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoIntegralComercialTest extends TestCase
{
    use RefreshDatabase;

    public function test_ciclo_de_vida_comercial_completo(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        // 1. Configuración de Empresa, Sede y Usuarios
        $empresa = Empresa::factory()->create([
            'nombre_comercial' => 'Macroferretería Integral SAS',
            'nit' => '900123999-1',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $sucursal = Sucursal::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Sede Principal',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);
        setPermissionsTeamId($empresa->id);

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $cajero = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $cajero->assignRole(RolSistema::CAJERO->value);

        CompanyContext::setCompanyId($empresa->id);
        BranchContext::setId($sucursal->id);

        // 2. Creación de Catálogo (Categoría, Unidad, Producto)
        $cat = Categoria::create(['empresa_id' => $empresa->id, 'nombre' => 'Cintas y Adhesivos']);
        $um = UnidadMedida::create(['empresa_id' => $empresa->id, 'nombre' => 'Unidad', 'codigo' => 'UND', 'activo' => true]);

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'categoria_id' => $cat->id,
            'unidad_medida_id' => $um->id,
            'codigo_barras' => 'CIN-001',
            'nombre' => 'Cinta Aislante Negra 20m',
            'precio_compra' => 3000,
            'precio_venta' => 6000,
            'stock' => 0,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        Inventario::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'producto_id' => $producto->id,
            'stock' => 0,
            'stock_minimo' => 5,
        ]);

        // 3. Proveedor y Compra Comercial (Entrada de Inventario)
        $proveedor = Proveedor::create([
            'empresa_id' => $empresa->id,
            'tipo_documento' => 'NIT',
            'numero_documento' => '800555111',
            'razon_social' => 'Distribuciones Eléctricas del Valle',
            'nombre_contacto' => 'Carlos Valle',
            'email' => 'ventas@elvalle.test',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $compraAction = app(RegistrarCompraAction::class);
        $dtoCompra = new RegistrarCompraDTO(
            proveedorId: $proveedor->id,
            sucursalId: $sucursal->id,
            numeroFactura: 'FC-99881',
            fechaEmision: now()->toDateString(),
            tipoPago: TipoPago::CONTADO,
            items: [
                new CompraItemDTO(
                    productoId: $producto->id,
                    cantidad: 100,
                    costoUnitario: 3000,
                    porcentajeIva: 0
                ),
            ],
            observaciones: 'Abastecimiento inicial',
            userId: $admin->id
        );

        $compra = $compraAction->execute($dtoCompra);
        $this->assertNotNull($compra);

        // Verificar que el inventario ingresó (100 unidades)
        $inv = Inventario::where('sucursal_id', $sucursal->id)->where('producto_id', $producto->id)->first();
        $this->assertEquals(100, (float) $inv->stock);

        // 4. Apertura de Caja con fondo inicial de $50,000
        $caja = Caja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Caja Principal Mostrador',
            'codigo' => 'CAJA-01',
            'estado' => \App\Enums\EstadoCaja::ACTIVA,
        ]);

        $abrirCajaAction = app(AbrirCajaAction::class);
        $sesionCaja = $abrirCajaAction->execute($caja, $cajero, 50000.0, 'Apertura de turno mañana');
        $this->assertEquals(EstadoSesionCaja::ABIERTA, $sesionCaja->estado);

        // 5. Venta de Contado en POS (10 unidades x $6,000 = $60,000)
        $ventaAction = app(RegistrarVentaAction::class);
        $venta1 = $ventaAction->execute(
            empresaId: $empresa->id,
            sucursalId: $sucursal->id,
            userId: $cajero->id,
            items: [
                ['producto_id' => $producto->id, 'cantidad' => 10, 'precio_unitario' => 6000],
            ],
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $sesionCaja->id,
            pagoCon: 100000
        );

        $this->assertEquals(60000, (float) $venta1->total);
        $this->assertEquals(40000, (float) $venta1->cambio);

        // Stock ahora es 90
        $inv->refresh();
        $this->assertEquals(90, (float) $inv->stock);

        // 6. Venta a Crédito con Cartera a Cliente con cupo
        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_persona' => \App\Enums\TipoPersona::JURIDICA,
            'tipo_documento' => \App\Enums\TipoDocumentoIdentidad::NIT,
            'numero_documento' => '900888999-3',
            'razon_social' => 'Constructora Bolívar SAS',
            'cupo_credito' => 500000,
            'plazo_dias' => 30,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Vender 20 unidades a crédito = $120,000
        $ventaCredito = $ventaAction->execute(
            empresaId: $empresa->id,
            sucursalId: $sucursal->id,
            userId: $cajero->id,
            items: [
                ['producto_id' => $producto->id, 'cantidad' => 20, 'precio_unitario' => 6000],
            ],
            clienteId: $cliente->id,
            tipoPago: TipoPago::CREDITO,
            metodoPago: 'CREDITO'
        );

        // Stock ahora es 70
        $inv->refresh();
        $this->assertEquals(70, (float) $inv->stock);

        // Se creó cuenta por cobrar en cartera por $120,000
        $cpc = CuentaPorCobrar::where('cliente_id', $cliente->id)->first();
        $this->assertNotNull($cpc);
        $this->assertEquals(120000, (float) $cpc->monto_total);
        $this->assertEquals(120000, (float) $cpc->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PENDIENTE, $cpc->estado);

        // 7. Abono a Cartera de $70,000
        $abonoAction = app(RegistrarAbonoCarteraAction::class);
        $abono = $abonoAction->execute(
            cuenta: $cpc,
            monto: 70000.0,
            metodoPago: MetodoPagoCartera::EFECTIVO,
            fechaPago: now()->toDateString(),
            referenciaPago: 'RC-001',
            notas: 'Abono parcial en efectivo',
            userId: $cajero->id
        );
        $this->assertNotNull($abono);

        $cpc->refresh();
        $this->assertEquals(50000, (float) $cpc->saldo_pendiente);
        $this->assertEquals(EstadoCuentaCobrar::PARCIAL, $cpc->estado);

        // 8. Devolución de 2 unidades de la Venta 1
        $devolucionAction = app(RegistrarDevolucionAction::class);
        $devolucion = $devolucionAction->execute(
            venta: $venta1,
            userId: $cajero->id,
            items: [
                [
                    'venta_detalle_id' => $venta1->detalles->first()->id,
                    'cantidad' => 2,
                    'reingresa_inventario' => true,
                ],
            ],
            tipoReintegro: TipoReintegroDevolucion::EFECTIVO,
            cajaSesionId: $sesionCaja->id,
            motivo: 'Cliente compró más unidades de las necesarias'
        );

        $this->assertNotNull($devolucion);
        $this->assertEquals(12000, (float) $devolucion->total);

        // Stock reintegrado: 70 + 2 = 72
        $inv->refresh();
        $this->assertEquals(72, (float) $inv->stock);

        // 9. Cierre de Caja
        $cerrarCajaAction = app(CerrarCajaAction::class);
        $sesionCaja->recalcularTotales();
        $saldoEsperado = $sesionCaja->calcularSaldoEsperadoEfectivo();

        $sesionCerrada = $cerrarCajaAction->execute(
            sesion: $sesionCaja,
            auditor: $admin,
            montoContado: $saldoEsperado,
            observaciones: 'Arqueo exacto sin descuadre al cierre de jornada'
        );

        $this->assertEquals(EstadoSesionCaja::CERRADA, $sesionCerrada->estado);
        $this->assertEquals($saldoEsperado, (float) $sesionCerrada->monto_cierre_contado);
        $this->assertEquals(0, (float) $sesionCerrada->diferencia);
    }
}
