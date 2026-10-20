<?php

namespace App\Http\Middleware;

use App\Enums\EstadoSuscripcion;
use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionIsActive
{
    /**
     * Rutas exentas de validación de suscripción operativa.
     */
    protected array $exceptRoutePrefixes = [
        'login',
        'logout',
        'suscripcion.',
        'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Usuarios no autenticados o SuperAdmin tienen paso libre
        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            return $next($request);
        }

        // 2. Comprobar si la ruta actual está explícitamente exenta
        $routeName = $request->route()?->getName() ?? '';
        foreach ($this->exceptRoutePrefixes as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        $suscripcion = $empresa->suscripcionActual;
        $esOperativa = $empresa->suscripcionEstaOperativa();

        // 3. Manejo de Período de Gracia: Permite operar y expone aviso informativo
        if ($suscripcion && $suscripcion->estado === EstadoSuscripcion::EN_GRACIA && $esOperativa) {
            view()->share('suscripcionEnGracia', true);
            view()->share('suscripcionFechaGraciaFin', $suscripcion->fecha_gracia_fin);

            return $next($request);
        }

        // 4. Si la suscripción está vigente y activa, permitir flujo normal
        if ($esOperativa) {
            return $next($request);
        }

        // 5. Suscripción VENCIDA o SUSPENDIDA:
        // Las peticiones GET de lectura (reportes, historial, facturas, clientes, POS offline, dashboard) están 100% permitidas
        if ($request->isMethod('GET')) {
            view()->share('suscripcionVencida', true);

            return $next($request);
        }

        // Las peticiones de escritura/mutación (POST, PUT, PATCH, DELETE) se bloquean con HTTP 402
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Tu suscripción se encuentra vencida o suspendida. Actualiza tu plan para registrar nuevas operaciones.',
                'code' => 'SUBSCRIPTION_EXPIRED',
                'renovar_url' => route('suscripcion.index'),
            ], 402);
        }

        return redirect()->route('suscripcion.index')->with(
            'warning',
            'Tu suscripción se encuentra vencida. Renueva tu plan para continuar registrando operaciones.'
        );
    }
}
