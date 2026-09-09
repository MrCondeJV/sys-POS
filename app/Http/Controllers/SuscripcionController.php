<?php

namespace App\Http\Controllers;

use App\Enums\EstadoSuscripcion;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuscripcionController extends Controller
{
    public function index(): View
    {
        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        $suscripcion = $empresa->suscripcionActual()->with('plan')->first();
        $plan = $suscripcion?->plan;

        $sucursalesCount = $empresa->sucursales()->count();
        $usuariosCount = $empresa->users()->count();

        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();

        return view('saas.suscripcion', compact(
            'empresa',
            'suscripcion',
            'plan',
            'sucursalesCount',
            'usuariosCount',
            'planes'
        ));
    }

    public function planes(): View
    {
        $empresa = CompanyContext::getCompany();
        $suscripcionActual = $empresa?->suscripcionActual()->with('plan')->first();
        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();

        return view('saas.planes', compact('planes', 'suscripcionActual'));
    }

    public function cambiarPlan(Request $request): RedirectResponse
    {
        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:planes,id'],
            'ciclo' => ['required', 'in:MENSUAL,ANUAL'],
        ]);

        $nuevoPlan = Plan::findOrFail($validated['plan_id']);

        DB::transaction(function () use ($empresa, $nuevoPlan, $validated) {
            // Cancelar suscripción anterior si existe
            Suscripcion::where('empresa_id', $empresa->id)
                ->whereIn('estado', [EstadoSuscripcion::ACTIVA->value, EstadoSuscripcion::PRUEBA->value])
                ->update(['estado' => EstadoSuscripcion::CANCELADA->value]);

            $precio = $validated['ciclo'] === 'ANUAL' ? $nuevoPlan->precio_anual : $nuevoPlan->precio_mensual;
            $dias = $validated['ciclo'] === 'ANUAL' ? 365 : 30;

            Suscripcion::create([
                'empresa_id' => $empresa->id,
                'plan_id' => $nuevoPlan->id,
                'estado' => EstadoSuscripcion::ACTIVA,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addDays($dias),
                'ciclo_facturacion' => $validated['ciclo'],
                'precio_pago' => $precio,
                'metodo_pago' => 'TRANSFERENCIA_BANCARIA',
                'notas' => "Contratación de {$nuevoPlan->nombre} en ciclo {$validated['ciclo']}",
            ]);
        });

        return redirect()->route('saas.suscripcion')
            ->with('success', "¡Excelente! Has actualizado tu plan a: {$nuevoPlan->nombre}");
    }
}
