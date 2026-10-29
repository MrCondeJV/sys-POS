<?php

namespace Tests\Feature\Modulos\Concerns;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Compras\RegistrarCompraAction;
use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Actions\Multisucursal\DespacharTrasladoAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\DTOs\CompraItemDTO;
use App\DTOs\RegistrarCompraDTO;
use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoResolucionFacturacion;
use App\Enums\MetodoPagoCartera;
use App\Enums\RolSistema;
use App\Enums\TipoAjusteListaPrecio;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoImpuesto;
use App\Enums\TipoPago;
use App\Enums\TipoPersona;
use App\Enums\TipoReintegroDevolucion;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Devolucion;
use App\Models\DocumentoElectronico;
use App\Models\Empresa;
use App\Models\Impuesto;
use App\Models\Inventario;
use App\Models\ListaPrecio;
use App\Models\Marca;
use App\Models\PagoSuscripcion;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ResolucionFacturacion;
use App\Models\Sucursal;
use App\Models\TrasladoSucursal;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Venta;
use App\Services\FacturacionElectronicaService;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\PlanesSaaSSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Escenario comercial completo de una empresa con datos en todos los módulos,
 * generado con las mismas Actions que usa la aplicación. Permite probar los
 * listados con filas reales (tabla de escritorio y tarjetas móviles).
 */
trait EscenarioComercial
{
    protected Empresa $empresa;
    protected Sucursal $sucursal;
    protected Sucursal $sucursalNorte;
    protected User $admin;
    protected User $cajero;
    protected Producto $producto;
    protected Proveedor $proveedor;
    protected Cliente $cliente;
    protected Compra $compra;
    protected CajaSesion $sesionCaja;
    protected Venta $ventaContado;
    protected Venta $ventaCredito;
    protected CuentaPorCobrar $cuenta;
    protected Devolucion $devolucion;
    protected TrasladoSucursal $traslado;
    protected ListaPrecio $lista;
    protected Impuesto $impuesto;
    protected ResolucionFacturacion $resolucion;
    protected DocumentoElectronico $documentoElectronico;
    protected PagoSuscripcion $pagoSuscripcion;

    protected function crearEscenarioComercial(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(PlanesSaaSSeeder::class);

        $this->empresa = Empresa::factory()->create([
            'nombre_comercial' => 'Ferretería Módulos SAS',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($this->empresa);

        $this->sucursal = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Principal Centro',
            'es_principal' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->sucursalNorte = Sucursal::factory()->create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Sede Norte',
            'es_principal' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        setPermissionsTeamId($this->empresa->id);
        $this->admin = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'name' => 'Admin Módulos',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

        $this->cajero = User::factory()->create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'name' => 'Cajera Turno Mañana',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->cajero->assignRole(RolSistema::CAJERO->value);

        CompanyContext::setCompany($this->empresa);
        BranchContext::setId($this->sucursal->id);

        // Catálogo e inventario en las dos sedes
        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Herramientas Manuales']);
        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Stanley', 'activo' => true]);
        $unidad = UnidadMedida::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Unidad', 'codigo' => 'UND', 'activo' => true]);

        $this->producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $categoria->id,
            'marca_id' => $marca->id,
            'unidad_medida_id' => $unidad->id,
            'codigo' => 'MART-16',
            'codigo_barras' => '7701234567890',
            'nombre' => 'Martillo Carpintero 16oz',
            'descripcion' => 'Martillo con mango de fibra de vidrio',
            'precio_compra' => 20000,
            'precio_venta' => 35000,
            'precio_mayorista' => 30000,
            'stock' => 0,
            'stock_minimo' => 5,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        foreach ([$this->sucursal, $this->sucursalNorte] as $sede) {
            Inventario::create([
                'empresa_id' => $this->empresa->id,
                'sucursal_id' => $sede->id,
                'producto_id' => $this->producto->id,
                'stock' => 0,
                'stock_minimo' => 5,
            ]);
        }

        // Compra que da entrada al inventario
        $this->proveedor = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'tipo_documento' => 'NIT',
            'numero_documento' => '800111222',
            'razon_social' => 'Distribuidora Herramientas del Caribe',
            'nombre_contacto' => 'Laura Gómez',
            'email' => 'ventas@herramientascaribe.test',
            'telefono' => '3001234567',
            'ciudad' => 'Barranquilla',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $this->compra = app(RegistrarCompraAction::class)->execute(new RegistrarCompraDTO(
            proveedorId: $this->proveedor->id,
            sucursalId: $this->sucursal->id,
            numeroFactura: 'FC-MOD-001',
            fechaEmision: now()->toDateString(),
            tipoPago: TipoPago::CONTADO,
            items: [new CompraItemDTO(productoId: $this->producto->id, cantidad: 50, costoUnitario: 20000, porcentajeIva: 0)],
            observaciones: 'Compra escenario de módulos',
            userId: $this->admin->id
        ));

        // Caja abierta y ventas (contado y crédito)
        $caja = Caja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'nombre' => 'Caja Mostrador',
            'codigo' => 'CAJA-MOD',
            'estado' => EstadoCaja::ACTIVA,
        ]);
        $this->sesionCaja = app(AbrirCajaAction::class)->execute($caja, $this->cajero, 100000.0, 'Apertura escenario');

        $this->cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'tipo_persona' => TipoPersona::JURIDICA,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => '901555444',
            'razon_social' => 'Constructora Los Andes SAS',
            'email' => 'compras@losandes.test',
            'telefono' => '6015550000',
            'ciudad' => 'Bogotá',
            'cupo_credito' => 1000000,
            'plazo_dias' => 30,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $ventas = app(RegistrarVentaAction::class);
        $this->ventaContado = $ventas->execute(
            empresaId: $this->empresa->id,
            sucursalId: $this->sucursal->id,
            userId: $this->cajero->id,
            items: [['producto_id' => $this->producto->id, 'cantidad' => 4, 'precio_unitario' => 35000]],
            tipoPago: TipoPago::CONTADO,
            metodoPago: 'EFECTIVO',
            cajaSesionId: $this->sesionCaja->id,
            pagoCon: 150000
        );
        $this->ventaCredito = $ventas->execute(
            empresaId: $this->empresa->id,
            sucursalId: $this->sucursal->id,
            userId: $this->cajero->id,
            items: [['producto_id' => $this->producto->id, 'cantidad' => 10, 'precio_unitario' => 35000]],
            clienteId: $this->cliente->id,
            tipoPago: TipoPago::CREDITO,
            metodoPago: 'CREDITO',
            tipoComprobante: TipoComprobanteVenta::FACTURA
        );

        $this->cuenta = CuentaPorCobrar::where('cliente_id', $this->cliente->id)->firstOrFail();
        app(RegistrarAbonoCarteraAction::class)->execute(
            cuenta: $this->cuenta,
            monto: 100000.0,
            metodoPago: MetodoPagoCartera::EFECTIVO,
            fechaPago: now()->toDateString(),
            referenciaPago: 'RC-MOD-1',
            notas: 'Abono parcial',
            userId: $this->cajero->id
        );
        $this->cuenta->refresh();

        $this->devolucion = app(RegistrarDevolucionAction::class)->execute(
            venta: $this->ventaContado,
            userId: $this->cajero->id,
            items: [[
                'venta_detalle_id' => $this->ventaContado->detalles->first()->id,
                'cantidad' => 1,
                'reingresa_inventario' => true,
            ]],
            tipoReintegro: TipoReintegroDevolucion::EFECTIVO,
            cajaSesionId: $this->sesionCaja->id,
            motivo: 'Producto con defecto de fábrica'
        );

        $this->traslado = app(DespacharTrasladoAction::class)->execute(
            sucursalOrigenId: $this->sucursal->id,
            sucursalDestinoId: $this->sucursalNorte->id,
            motivo: 'Reabastecer sede norte',
            items: [['producto_id' => $this->producto->id, 'cantidad' => 5]],
            userId: $this->admin->id
        );

        // Configuración: lista de precios, impuesto y facturación electrónica
        $this->lista = ListaPrecio::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Mayoristas Constructoras',
            'codigo' => 'MAY-01',
            'descripcion' => 'Descuento para constructoras con volumen',
            'tipo_ajuste' => TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO,
            'porcentaje_defecto' => 10,
            'es_predeterminada' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->impuesto = Impuesto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'IVA General 19%',
            'codigo' => 'IVA19',
            'porcentaje' => 19.00,
            'tipo' => TipoImpuesto::IVA,
            'por_defecto' => true,
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        $this->resolucion = ResolucionFacturacion::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'numero_resolucion' => '18764999001',
            'prefijo' => 'FEMO',
            'rango_desde' => 1,
            'rango_hasta' => 5000,
            'consecutivo_actual' => 0,
            'fecha_inicio' => now()->subMonth(),
            'fecha_vigencia' => now()->addYear(),
            'clave_tecnica' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e3811e8',
            'estado' => EstadoResolucionFacturacion::ACTIVA,
            'es_predeterminada' => true,
        ]);
        $this->documentoElectronico = app(FacturacionElectronicaService::class)
            ->emitirFacturaElectronica($this->ventaCredito->documentoVenta, $this->resolucion);

        $plan = Plan::where('slug', 'profesional')->firstOrFail();
        $this->pagoSuscripcion = PagoSuscripcion::create([
            'empresa_id' => $this->empresa->id,
            'plan_id' => $plan->id,
            'user_id' => $this->admin->id,
            'ciclo_facturacion' => 'mensual',
            'reference' => 'SUB-MOD-REF-0001',
            'amount_in_cents' => (int) ($plan->precio_mensual * 100),
            'currency' => 'COP',
            'integrity_signature' => str_repeat('a', 64),
            'status' => 'APPROVED',
            'payment_method_type' => 'CARD',
            'paid_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    /**
     * Super admin global (sin empresa). El contexto de empresa se limpia antes de
     * crearlo: si no, el modelo le asigna empresa_id y deja de ser super admin.
     */
    protected function crearSuperAdmin(): User
    {
        CompanyContext::clear();
        BranchContext::clear();

        $superAdmin = User::factory()->superAdmin()->create(['estado' => EstadoGeneral::ACTIVO]);
        setPermissionsTeamId(null);
        $superAdmin->assignRole(RolSistema::SUPER_ADMIN->value);

        setPermissionsTeamId($this->empresa->id);
        CompanyContext::setCompany($this->empresa);
        BranchContext::setId($this->sucursal->id);

        return $superAdmin;
    }

    /** Crea una segunda empresa con datos propios para validar el aislamiento. */
    protected function crearEmpresaAjena(): array
    {
        $ajena = Empresa::factory()->create(['nombre_comercial' => 'Empresa Ajena Ltda']);
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($ajena);
        $sede = Sucursal::factory()->create(['empresa_id' => $ajena->id, 'nombre' => 'Sede Ajena Única']);

        $cliente = Cliente::withoutGlobalScopes()->create([
            'empresa_id' => $ajena->id,
            'tipo_persona' => TipoPersona::NATURAL,
            'tipo_documento' => TipoDocumentoIdentidad::CC,
            'numero_documento' => '1099888777',
            'razon_social' => 'Cliente Secreto Ajeno',
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $producto = Producto::withoutGlobalScopes()->create([
            'empresa_id' => $ajena->id,
            'codigo' => 'AJENO-01',
            'nombre' => 'Producto Secreto Ajeno',
            'precio_compra' => 1000,
            'precio_venta' => 2000,
            'stock' => 3,
            'stock_minimo' => 1,
            'estado' => EstadoGeneral::ACTIVO,
        ]);
        $proveedor = Proveedor::withoutGlobalScopes()->create([
            'empresa_id' => $ajena->id,
            'tipo_documento' => 'NIT',
            'numero_documento' => '899000111',
            'razon_social' => 'Proveedor Secreto Ajeno',
            'estado' => EstadoGeneral::ACTIVO,
        ]);

        // Restaura el contexto y el team de permisos de la empresa principal
        setPermissionsTeamId($this->empresa->id);
        CompanyContext::setCompany($this->empresa);
        BranchContext::setId($this->sucursal->id);

        return compact('ajena', 'sede', 'cliente', 'producto', 'proveedor');
    }
}
