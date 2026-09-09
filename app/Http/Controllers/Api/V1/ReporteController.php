<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoVenta;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends BaseApiController
{
    public function resumen(Request $request): JsonResponse
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        $ventasHoy = (float) Venta::where('estado', EstadoVenta::COMPLETADA)
            ->whereDate('created_at', $hoy)
            ->sum('total');

        $ventasMes = (float) Venta::where('estado', EstadoVenta::COMPLETADA)
            ->where('created_at', '>=', $inicioMes)
            ->sum('total');

        $conteoVentasHoy = Venta::where('estado', EstadoVenta::COMPLETADA)
            ->whereDate('created_at', $hoy)
            ->count();

        $ticketPromedio = $conteoVentasHoy > 0 ? round($ventasHoy / $conteoVentasHoy, 2) : 0.0;

        $totalCartera = (float) CuentaPorCobrar::where('estado', '!=', 'ANULADA')
            ->where('estado', '!=', 'PAGADA')
            ->sum('saldo_pendiente');

        $productosTotal = Producto::count();
        $clientesTotal = Cliente::count();

        return $this->successResponse([
            'ventas_hoy' => $ventasHoy,
            'ventas_mes' => $ventasMes,
            'tickets_hoy' => $conteoVentasHoy,
            'ticket_promedio' => $ticketPromedio,
            'cartera_pendiente' => $totalCartera,
            'total_productos' => $productosTotal,
            'total_clientes' => $clientesTotal,
        ]);
    }
}
