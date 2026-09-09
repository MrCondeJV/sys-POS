<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCaja;
use App\Enums\EstadoCompra;
use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoSesionCaja;
use App\Enums\EstadoVenta;
use App\Enums\PermisoSistema;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReporteController extends Controller
{
    /**
     * Verifica que el usuario tenga permiso para consultar reportes.
     */
    protected function checkPermission(Request $request): void
    {
        abort_unless($request->user()->hasPermissionTo(PermisoSistema::REPORTES_VER->value), 403, 'No tiene permiso para ver reportes.');
    }

    /**
     * Centro de Control de Reportes.
     */
    public function index(Request $request): View
    {
        $this->checkPermission($request);

        return view('reportes.index');
    }

    /**
     * Reporte de Ventas.
     */
    public function ventas(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');
        $usuarioId = $request->input('usuario_id');
        $clienteId = $request->input('cliente_id');
        $tipoPago = $request->input('tipo_pago');
        $estado = $request->input('estado');

        $query = Venta::with(['cliente', 'sucursal', 'usuario', 'detalles'])
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }
        if ($usuarioId) {
            $query->where('user_id', $usuarioId);
        }
        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }
        if ($tipoPago) {
            $query->where('tipo_pago', $tipoPago);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        // Totales agregados
        $totalesQuery = clone $query;
        $totalNeto = (float) (clone $totalesQuery)->where('estado', EstadoVenta::COMPLETADA->value)->sum('total');
        $totalImpuestos = (float) (clone $totalesQuery)->where('estado', EstadoVenta::COMPLETADA->value)->sum('impuesto');
        $totalDescuentos = (float) (clone $totalesQuery)->where('estado', EstadoVenta::COMPLETADA->value)->sum('descuento');
        $cantidadVentas = (int) (clone $totalesQuery)->where('estado', EstadoVenta::COMPLETADA->value)->count();
        $ticketPromedio = $cantidadVentas > 0 ? round($totalNeto / $cantidadVentas, 2) : 0.0;

        $ventas = $query->latest('fecha')->paginate(25)->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $clientes = Cliente::orderBy('razon_social')->take(50)->get();

        return view('reportes.ventas', compact(
            'ventas',
            'fechaDesde',
            'fechaHasta',
            'sucursalId',
            'usuarioId',
            'clienteId',
            'tipoPago',
            'estado',
            'totalNeto',
            'totalImpuestos',
            'totalDescuentos',
            'cantidadVentas',
            'ticketPromedio',
            'sucursales',
            'usuarios',
            'clientes'
        ));
    }

    /**
     * Versión Imprimible de Reporte de Ventas.
     */
    public function ventasImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $query = Venta::with(['cliente', 'sucursal', 'usuario'])
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $totalNeto = (float) (clone $query)->where('estado', EstadoVenta::COMPLETADA->value)->sum('total');
        $totalImpuestos = (float) (clone $query)->where('estado', EstadoVenta::COMPLETADA->value)->sum('impuesto');
        $cantidadVentas = (int) (clone $query)->where('estado', EstadoVenta::COMPLETADA->value)->count();

        $ventas = $query->latest('fecha')->take(500)->get();

        return view('reportes.ventas_imprimir', compact(
            'ventas',
            'fechaDesde',
            'fechaHasta',
            'totalNeto',
            'totalImpuestos',
            'cantidadVentas'
        ));
    }

    /**
     * Reporte de Compras.
     */
    public function compras(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');
        $proveedorId = $request->input('proveedor_id');
        $estado = $request->input('estado');

        $query = Compra::with(['proveedor', 'sucursal', 'user'])
            ->whereDate('fecha_emision', '>=', $fechaDesde)
            ->whereDate('fecha_emision', '<=', $fechaHasta);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }
        if ($proveedorId) {
            $query->where('proveedor_id', $proveedorId);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        $totalesQuery = clone $query;
        $totalComprado = (float) (clone $totalesQuery)->where('estado', EstadoCompra::REGISTRADA->value)->sum('total');
        $totalImpuestos = (float) (clone $totalesQuery)->where('estado', EstadoCompra::REGISTRADA->value)->sum('impuestos');
        $totalComprasCount = (int) (clone $totalesQuery)->where('estado', EstadoCompra::REGISTRADA->value)->count();

        $compras = $query->latest('fecha_emision')->paginate(25)->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('razon_social')->get();

        return view('reportes.compras', compact(
            'compras',
            'fechaDesde',
            'fechaHasta',
            'sucursalId',
            'proveedorId',
            'estado',
            'totalComprado',
            'totalImpuestos',
            'totalComprasCount',
            'sucursales',
            'proveedores'
        ));
    }

    /**
     * Versión Imprimible de Compras.
     */
    public function comprasImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());

        $compras = Compra::with(['proveedor', 'sucursal'])
            ->whereDate('fecha_emision', '>=', $fechaDesde)
            ->whereDate('fecha_emision', '<=', $fechaHasta)
            ->latest('fecha_emision')
            ->take(500)
            ->get();

        $totalComprado = (float) $compras->where('estado', EstadoCompra::REGISTRADA)->sum('total');

        return view('reportes.compras_imprimir', compact('compras', 'fechaDesde', 'fechaHasta', 'totalComprado'));
    }

    /**
     * Reporte de Utilidad y Rentabilidad.
     */
    public function utilidad(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');
        $categoriaId = $request->input('categoria_id');

        $query = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->where('ventas.estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('ventas.fecha', '>=', $fechaDesde)
            ->whereDate('ventas.fecha', '<=', $fechaHasta);

        if ($sucursalId) {
            $query->where('ventas.sucursal_id', $sucursalId);
        }
        if ($categoriaId) {
            $query->where('productos.categoria_id', $categoriaId);
        }

        // Totales consolidados
        $totales = (clone $query)->selectRaw('
            COALESCE(SUM(venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento), 0) as total_ingreso,
            COALESCE(SUM(venta_detalles.cantidad * venta_detalles.costo_unitario), 0) as total_costo,
            COALESCE(SUM(venta_detalles.cantidad), 0) as total_unidades
        ')->first();

        $totalIngreso = (float) ($totales->total_ingreso ?? 0);
        $totalCosto = (float) ($totales->total_costo ?? 0);
        $totalUtilidad = $totalIngreso - $totalCosto;
        $porcentajeMargen = $totalIngreso > 0 ? round(($totalUtilidad / $totalIngreso) * 100, 2) : 0.0;
        $totalUnidades = (float) ($totales->total_unidades ?? 0);

        // Agrupación por producto
        $rentabilidadProductos = (clone $query)
            ->selectRaw('
                productos.id as producto_id,
                productos.nombre as producto_nombre,
                productos.codigo as producto_codigo,
                SUM(venta_detalles.cantidad) as unidades_vendidas,
                SUM(venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento) as ingreso_neto,
                SUM(venta_detalles.cantidad * venta_detalles.costo_unitario) as costo_total,
                SUM((venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento) - (venta_detalles.cantidad * venta_detalles.costo_unitario)) as utilidad_bruta
            ')
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('utilidad_bruta')
            ->paginate(25)
            ->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('reportes.utilidad', compact(
            'rentabilidadProductos',
            'fechaDesde',
            'fechaHasta',
            'sucursalId',
            'categoriaId',
            'totalIngreso',
            'totalCosto',
            'totalUtilidad',
            'porcentajeMargen',
            'totalUnidades',
            'sucursales',
            'categorias'
        ));
    }

    /**
     * Versión Imprimible de Utilidad.
     */
    public function utilidadImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());

        $totales = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->where('ventas.estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('ventas.fecha', '>=', $fechaDesde)
            ->whereDate('ventas.fecha', '<=', $fechaHasta)
            ->selectRaw('
                COALESCE(SUM(venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento), 0) as total_ingreso,
                COALESCE(SUM(venta_detalles.cantidad * venta_detalles.costo_unitario), 0) as total_costo
            ')->first();

        $totalIngreso = (float) ($totales->total_ingreso ?? 0);
        $totalCosto = (float) ($totales->total_costo ?? 0);
        $totalUtilidad = $totalIngreso - $totalCosto;
        $porcentajeMargen = $totalIngreso > 0 ? round(($totalUtilidad / $totalIngreso) * 100, 2) : 0.0;

        $items = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->where('ventas.estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('ventas.fecha', '>=', $fechaDesde)
            ->whereDate('ventas.fecha', '<=', $fechaHasta)
            ->selectRaw('
                productos.nombre as producto_nombre,
                productos.codigo as producto_codigo,
                SUM(venta_detalles.cantidad) as unidades_vendidas,
                SUM(venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento) as ingreso_neto,
                SUM(venta_detalles.cantidad * venta_detalles.costo_unitario) as costo_total,
                SUM((venta_detalles.cantidad * venta_detalles.precio_unitario - venta_detalles.descuento) - (venta_detalles.cantidad * venta_detalles.costo_unitario)) as utilidad_bruta
            ')
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('utilidad_bruta')
            ->take(500)
            ->get();

        return view('reportes.utilidad_imprimir', compact(
            'items',
            'fechaDesde',
            'fechaHasta',
            'totalIngreso',
            'totalCosto',
            'totalUtilidad',
            'porcentajeMargen'
        ));
    }

    /**
     * Reporte de Inventario y Valoración de Stock.
     */
    public function inventario(Request $request): View
    {
        $this->checkPermission($request);

        $sucursalId = $request->input('sucursal_id');
        $categoriaId = $request->input('categoria_id');
        $filtroStock = $request->input('filtro_stock', 'todos');

        $query = Inventario::with(['producto.categoria', 'producto.unidadMedida', 'sucursal']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($categoriaId) {
            $query->whereHas('producto', fn($q) => $q->where('categoria_id', $categoriaId));
        }

        if ($filtroStock === 'bajo_stock') {
            $query->whereHas('producto', function ($q) {
                $q->whereColumn('inventarios.stock', '<=', 'productos.stock_minimo');
            });
        } elseif ($filtroStock === 'agotados') {
            $query->where('stock', '<=', 0);
        } elseif ($filtroStock === 'disponibles') {
            $query->where('stock', '>', 0);
        }

        // Valoración global de inventario
        $inventariosTodos = (clone $query)->get();
        $totalUnidades = (float) $inventariosTodos->sum('stock');
        $totalCostoValorizado = (float) $inventariosTodos->sum(fn($i) => (float) $i->stock * (float) $i->producto->precio_costo);
        $totalValorVenta = (float) $inventariosTodos->sum(fn($i) => (float) $i->stock * (float) $i->producto->precio_venta);
        $utilidadPotencial = $totalValorVenta - $totalCostoValorizado;

        $inventarios = $query->paginate(25)->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('reportes.inventario', compact(
            'inventarios',
            'sucursalId',
            'categoriaId',
            'filtroStock',
            'totalUnidades',
            'totalCostoValorizado',
            'totalValorVenta',
            'utilidadPotencial',
            'sucursales',
            'categorias'
        ));
    }

    /**
     * Versión Imprimible de Inventario.
     */
    public function inventarioImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $inventarios = Inventario::with(['producto.categoria', 'sucursal'])
            ->orderBy('sucursal_id')
            ->take(500)
            ->get();

        $totalCostoValorizado = (float) $inventarios->sum(fn($i) => (float) $i->stock * (float) $i->producto->precio_costo);
        $totalValorVenta = (float) $inventarios->sum(fn($i) => (float) $i->stock * (float) $i->producto->precio_venta);

        return view('reportes.inventario_imprimir', compact('inventarios', 'totalCostoValorizado', 'totalValorVenta'));
    }

    /**
     * Reporte de Cajas y Turnos.
     */
    public function cajas(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $cajaId = $request->input('caja_id');
        $sucursalId = $request->input('sucursal_id');
        $estado = $request->input('estado');

        $query = CajaSesion::with(['caja', 'sucursal', 'cajero', 'cajeroCierre'])
            ->whereDate('fecha_apertura', '>=', $fechaDesde)
            ->whereDate('fecha_apertura', '<=', $fechaHasta);

        if ($cajaId) {
            $query->where('caja_id', $cajaId);
        }
        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        $sesiones = $query->latest('fecha_apertura')->paginate(25)->withQueryString();

        $totalesQuery = clone $query;
        $totalIngresos = (float) (clone $totalesQuery)->sum('total_ingresos');
        $totalEgresos = (float) (clone $totalesQuery)->sum('total_egresos');
        $totalVentasEfectivo = (float) (clone $totalesQuery)->sum('total_ventas_efectivo');
        $totalDiferencias = (float) (clone $totalesQuery)->sum('diferencia');

        $cajas = Caja::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('reportes.cajas', compact(
            'sesiones',
            'fechaDesde',
            'fechaHasta',
            'cajaId',
            'sucursalId',
            'estado',
            'totalIngresos',
            'totalEgresos',
            'totalVentasEfectivo',
            'totalDiferencias',
            'cajas',
            'sucursales'
        ));
    }

    /**
     * Versión Imprimible de Cajas.
     */
    public function cajasImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());

        $sesiones = CajaSesion::with(['caja', 'sucursal', 'cajero'])
            ->whereDate('fecha_apertura', '>=', $fechaDesde)
            ->whereDate('fecha_apertura', '<=', $fechaHasta)
            ->latest('fecha_apertura')
            ->take(500)
            ->get();

        return view('reportes.cajas_imprimir', compact('sesiones', 'fechaDesde', 'fechaHasta'));
    }

    /**
     * Reporte de Cartera y Envejecimiento.
     */
    public function cartera(Request $request): View
    {
        $this->checkPermission($request);

        $clienteId = $request->input('cliente_id');
        $sucursalId = $request->input('sucursal_id');
        $estado = $request->input('estado');

        $query = CuentaPorCobrar::with(['cliente', 'sucursal']);

        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
        }
        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }

        $cuentas = (clone $query)->get();

        $saldoTotalPendiente = (float) $cuentas->whereIn('estado', [EstadoCuentaCobrar::PENDIENTE, EstadoCuentaCobrar::PARCIAL])->sum('saldo_pendiente');
        $totalOriginal = (float) $cuentas->sum('monto_total');
        $totalCobrado = (float) $cuentas->sum('monto_pagado');

        // Baldes de envejecimiento
        $hoy = Carbon::today();
        $corriente = 0.0;
        $mora1a30 = 0.0;
        $mora31a60 = 0.0;
        $moraMas60 = 0.0;

        foreach ($cuentas->whereIn('estado', [EstadoCuentaCobrar::PENDIENTE, EstadoCuentaCobrar::PARCIAL]) as $c) {
            $saldo = (float) $c->saldo_pendiente;
            if ($c->fecha_vencimiento >= $hoy) {
                $corriente += $saldo;
            } else {
                $diasMora = $c->fecha_vencimiento->diffInDays($hoy);
                if ($diasMora <= 30) {
                    $mora1a30 += $saldo;
                } elseif ($diasMora <= 60) {
                    $mora31a60 += $saldo;
                } else {
                    $moraMas60 += $saldo;
                }
            }
        }

        $cuentasPaginadas = $query->latest('fecha_vencimiento')->paginate(25)->withQueryString();

        $clientes = Cliente::orderBy('razon_social')->take(50)->get();
        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('reportes.cartera', compact(
            'cuentasPaginadas',
            'saldoTotalPendiente',
            'totalOriginal',
            'totalCobrado',
            'corriente',
            'mora1a30',
            'mora31a60',
            'moraMas60',
            'clientes',
            'sucursales',
            'clienteId',
            'sucursalId',
            'estado'
        ));
    }

    /**
     * Versión Imprimible de Cartera.
     */
    public function carteraImprimir(Request $request): View
    {
        $this->checkPermission($request);

        $cuentas = CuentaPorCobrar::with(['cliente', 'sucursal'])
            ->pendientes()
            ->orderBy('fecha_vencimiento')
            ->take(500)
            ->get();

        $saldoTotalPendiente = (float) $cuentas->sum('saldo_pendiente');

        return view('reportes.cartera_imprimir', compact('cuentas', 'saldoTotalPendiente'));
    }

    /**
     * Reporte de Métodos de Pago.
     */
    public function metodosPago(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        $query = Venta::completadas()
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $totalGeneral = (float) (clone $query)->sum('total');

        $desglose = (clone $query)
            ->selectRaw('tipo_pago, metodo_pago, COUNT(*) as cantidad_transacciones, SUM(total) as monto_total')
            ->groupBy('tipo_pago', 'metodo_pago')
            ->orderByDesc('monto_total')
            ->get();

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('reportes.metodos_pago', compact(
            'desglose',
            'totalGeneral',
            'fechaDesde',
            'fechaHasta',
            'sucursalId',
            'sucursales'
        ));
    }

    /**
     * Reporte de Impuestos (IVA Consolidado).
     */
    public function impuestos(Request $request): View
    {
        $this->checkPermission($request);

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $sucursalId = $request->input('sucursal_id');

        // Ventas: Base gravable e IVA generado
        $ventasQuery = Venta::completadas()
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        if ($sucursalId) {
            $ventasQuery->where('sucursal_id', $sucursalId);
        }

        $totalVentasNeto = (float) (clone $ventasQuery)->sum('subtotal');
        $totalIvaGenerado = (float) (clone $ventasQuery)->sum('impuesto');
        $totalVentas = (float) (clone $ventasQuery)->sum('total');

        // Compras: Base gravable e IVA descontable
        $comprasQuery = Compra::registrada()
            ->whereDate('fecha_emision', '>=', $fechaDesde)
            ->whereDate('fecha_emision', '<=', $fechaHasta);

        if ($sucursalId) {
            $comprasQuery->where('sucursal_id', $sucursalId);
        }

        $totalComprasNeto = (float) (clone $comprasQuery)->sum('subtotal');
        $totalIvaDescontable = (float) (clone $comprasQuery)->sum('impuestos');
        $totalCompras = (float) (clone $comprasQuery)->sum('total');

        // Saldo tributario neto (IVA a pagar o saldo a favor)
        $saldoIvaNeto = $totalIvaGenerado - $totalIvaDescontable;

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('reportes.impuestos', compact(
            'totalVentasNeto',
            'totalIvaGenerado',
            'totalVentas',
            'totalComprasNeto',
            'totalIvaDescontable',
            'totalCompras',
            'saldoIvaNeto',
            'fechaDesde',
            'fechaHasta',
            'sucursalId',
            'sucursales'
        ));
    }
}
