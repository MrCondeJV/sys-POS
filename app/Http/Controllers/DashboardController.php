<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal con KPIs, accesos y estado del inventario.
     */
    public function __invoke(Request $request): View
    {
        $totalProductos = Producto::count();
        $totalBajoStock = Producto::bajoStock()->count();
        $totalSucursales = Sucursal::count();
        $totalCategorias = Categoria::count();

        // Obtener productos críticos (bajo stock) o últimos registrados
        $productosCriticos = Producto::with(['categoria', 'unidadMedida'])
            ->bajoStock()
            ->latest()
            ->take(6)
            ->get();

        $ultimosProductos = Producto::with(['categoria', 'unidadMedida'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'totalProductos',
            'totalBajoStock',
            'totalSucursales',
            'totalCategorias',
            'productosCriticos',
            'ultimosProductos'
        ));
    }
}
