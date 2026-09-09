<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCuentaCobrar;
use App\Enums\EstadoVenta;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal con KPIs, accesos y estado del negocio.
     */
    public function __invoke(Request $request): View
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now()->month;
        $anioActual = Carbon::now()->year;

        // Catálogos e infraestructura
        $totalProductos = Producto::count();
        $totalBajoStock = Producto::bajoStock()->count();
        $totalSucursales = Sucursal::count();
        $totalCategorias = Categoria::count();
        $totalClientes = Cliente::count();

        // Ventas de hoy
        $ventasHoyQuery = Venta::completadas()->whereDate('fecha', $hoy);
        $ventasHoy = (float) $ventasHoyQuery->sum('total');
        $cantidadVentasHoy = $ventasHoyQuery->count();

        // Ventas del mes
        $ventasMesQuery = Venta::completadas()->whereMonth('fecha', $mesActual)->whereYear('fecha', $anioActual);
        $ventasMes = (float) $ventasMesQuery->sum('total');
        $cantidadVentasMes = $ventasMesQuery->count();

        // Ticket promedio del mes
        $ticketPromedio = $cantidadVentasMes > 0 ? round($ventasMes / $cantidadVentasMes, 2) : 0.0;

        // Total productos/unidades vendidas en el mes
        $productosVendidosMes = (float) VentaDetalle::whereHas('venta', function ($q) use ($mesActual, $anioActual) {
            $q->completadas()->whereMonth('fecha', $mesActual)->whereYear('fecha', $anioActual);
        })->sum('cantidad');

        // Utilidad bruta estimada del mes (ingreso neto línea - costo total línea)
        $utilidadMes = (float) VentaDetalle::whereHas('venta', function ($q) use ($mesActual, $anioActual) {
            $q->completadas()->whereMonth('fecha', $mesActual)->whereYear('fecha', $anioActual);
        })->sum(DB::raw('(cantidad * precio_unitario) - (cantidad * costo_unitario) - descuento'));

        // Cartera pendiente de cobro
        $carteraPendiente = (float) CuentaPorCobrar::whereIn('estado', [
            EstadoCuentaCobrar::PENDIENTE->value,
            EstadoCuentaCobrar::PARCIAL->value,
        ])->sum('saldo_pendiente');

        // Productos críticos (bajo stock)
        $productosCriticos = Producto::with(['categoria', 'unidadMedida'])
            ->bajoStock()
            ->latest()
            ->take(6)
            ->get();

        // Últimos productos registrados
        $ultimosProductos = Producto::with(['categoria', 'unidadMedida'])
            ->latest()
            ->take(6)
            ->get();

        // Últimas 5 ventas completadas
        $ultimasVentas = Venta::with(['cliente', 'sucursal', 'usuario'])
            ->completadas()
            ->latest('fecha')
            ->take(5)
            ->get();

        // Ventas de los últimos 7 días para gráfico/tendencia
        $ventasUltimos7Dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $totalDia = (float) Venta::completadas()->whereDate('fecha', $dia)->sum('total');
            $ventasUltimos7Dias[] = [
                'dia' => $dia->isoFormat('ddd D'),
                'fecha' => $dia->toDateString(),
                'total' => $totalDia,
            ];
        }

        return view('dashboard', compact(
            'totalProductos',
            'totalBajoStock',
            'totalSucursales',
            'totalCategorias',
            'totalClientes',
            'ventasHoy',
            'cantidadVentasHoy',
            'ventasMes',
            'cantidadVentasMes',
            'ticketPromedio',
            'productosVendidosMes',
            'utilidadMes',
            'carteraPendiente',
            'productosCriticos',
            'ultimosProductos',
            'ultimasVentas',
            'ventasUltimos7Dias'
        ));
    }
}
