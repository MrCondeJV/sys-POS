<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\TrasladoSucursal;
use App\Models\Venta;
use App\Support\Tenancy\CompanyContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteMultisucursalController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = CompanyContext::getId();
        $sucursales = Sucursal::activa()->where('empresa_id', $empresaId)->get();

        $desde = $request->filled('desde') ? Carbon::parse($request->input('desde'))->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->input('hasta'))->endOfDay() : Carbon::now()->endOfDay();

        // 1. Matriz de Inventario por Sucursal
        $queryProductos = Producto::activo()
            ->with(['inventarios' => fn($q) => $q->where('empresa_id', $empresaId), 'categoria'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $queryProductos->where(fn($q) => $q->where('nombre', 'like', "%{$s}%")->orWhere('codigo_barras', 'like', "%{$s}%"));
        }

        $productos = $queryProductos->orderBy('nombre')->paginate(20)->withQueryString();

        // 2. Comparativo de Ventas por Sucursal
        $ventasPorSucursal = [];
        foreach ($sucursales as $suc) {
            $ventasQuery = Venta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('sucursal_id', $suc->id)
                ->where('estado', 'COMPLETADA')
                ->whereBetween('created_at', [$desde, $hasta]);

            $totalVendido = (float) $ventasQuery->sum('total');
            $cantidadVentas = $ventasQuery->count();
            $ticketPromedio = $cantidadVentas > 0 ? $totalVendido / $cantidadVentas : 0;

            $ventasPorSucursal[] = [
                'sucursal' => $suc,
                'total' => $totalVendido,
                'cantidad' => $cantidadVentas,
                'ticket_promedio' => $ticketPromedio,
            ];
        }

        // 3. Resumen de traslados en tránsito
        $trasladosEnTransito = TrasladoSucursal::where('empresa_id', $empresaId)
            ->where('estado', 'EN_TRANSITO')
            ->count();

        return view('reportes.multisucursal', compact(
            'sucursales',
            'productos',
            'ventasPorSucursal',
            'trasladosEnTransito',
            'desde',
            'hasta'
        ));
    }
}
