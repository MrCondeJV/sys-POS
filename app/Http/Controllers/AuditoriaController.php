<?php

namespace App\Http\Controllers;

use App\Enums\PermisoSistema;
use App\Models\Auditoria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /**
     * Muestra la bitácora de auditoría con filtros avanzados.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermissionTo(PermisoSistema::AUDITORIA_VER->value), 403, 'No tiene permiso para consultar la bitácora de auditoría.');

        $fechaDesde = $request->input('fecha_desde', Carbon::now()->subDays(30)->toDateString());
        $fechaHasta = $request->input('fecha_hasta', Carbon::now()->toDateString());
        $userId = $request->input('user_id');
        $modulo = $request->input('modulo');
        $accion = $request->input('accion');
        $term = $request->input('term');

        $query = Auditoria::with(['usuario', 'empresa'])
            ->whereDate('created_at', '>=', $fechaDesde)
            ->whereDate('created_at', '<=', $fechaHasta);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($modulo) {
            $query->where('modulo', strtoupper($modulo));
        }
        if ($accion) {
            $query->where('accion', strtoupper($accion));
        }
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('descripcion', 'like', "%{$term}%")
                  ->orWhere('ip', 'like', "%{$term}%")
                  ->orWhere('auditable_type', 'like', "%{$term}%");
            });
        }

        $auditorias = $query->latest('created_at')->paginate(25)->withQueryString();

        $usuarios = User::orderBy('name')->get();
        $modulos = Auditoria::select('modulo')->distinct()->pluck('modulo');
        $acciones = Auditoria::select('accion')->distinct()->pluck('accion');

        return view('auditoria.index', compact(
            'auditorias',
            'fechaDesde',
            'fechaHasta',
            'userId',
            'modulo',
            'accion',
            'term',
            'usuarios',
            'modulos',
            'acciones'
        ));
    }

    /**
     * Muestra el detalle completo de un registro de auditoría con comparativa de datos.
     */
    public function show(Request $request, Auditoria $auditoria): View
    {
        abort_unless(
            $request->user()->hasPermissionTo(PermisoSistema::AUDITORIA_VER->value) &&
            ($auditoria->empresa_id === null || $auditoria->empresa_id === $request->user()->empresa_id),
            403,
            'No tiene permiso para ver este registro de auditoría.'
        );

        $auditoria->load(['usuario', 'empresa']);

        return view('auditoria.show', compact('auditoria'));
    }
}
