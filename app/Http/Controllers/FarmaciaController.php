<?php

namespace App\Http\Controllers;

use App\Models\Laboratorio;
use App\Models\PrincipioActivo;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Services\FarmaciaAlertasService;
use App\Support\Tenancy\CompanyContext;
use Illuminate\View\View;

class FarmaciaController extends Controller
{
    public function dashboard(FarmaciaAlertasService $alertasService): View
    {
        $empresaId = CompanyContext::getId();

        $alertasService->sincronizarNotificacionesFarmacia($empresaId);

        $totalLaboratorios = Laboratorio::count();
        $totalPrincipios = PrincipioActivo::count();
        $totalMedicamentos = Producto::whereNotNull('registro_sanitario')->orWhere('maneja_lotes', true)->count();

        $lotesDisponibles = ProductoLote::disponibles()->count();
        $lotesProximos = ProductoLote::proximosAVencer(30)->count();
        $lotesVencidos = ProductoLote::vencidos()->count();

        $lotesCriticos = ProductoLote::with(['producto', 'sucursal'])
            ->where(function ($q) {
                $q->vencidos()->orWhere(fn ($b) => $b->proximosAVencer(30));
            })
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        return view('farmacia.dashboard', compact(
            'totalLaboratorios',
            'totalPrincipios',
            'totalMedicamentos',
            'lotesDisponibles',
            'lotesProximos',
            'lotesVencidos',
            'lotesCriticos'
        ));
    }
}
