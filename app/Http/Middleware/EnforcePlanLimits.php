<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    public function handle(Request $request, Closure $next, string $feature = ''): Response
    {
        $empresa = CompanyContext::getCompany();
        if (! $empresa) {
            return $next($request);
        }

        $plan = $empresa->obtenerPlan();
        if (! $plan) {
            return $next($request);
        }

        if ($feature === 'facturacion_electronica' && ! $plan->permite_facturacion_electronica) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tu plan no incluye facturación electrónica. Actualiza tu suscripción.',
                ], 403);
            }
            abort(403, 'Tu plan actual no incluye facturación electrónica. Actualiza tu plan para habilitar este módulo.');
        }

        if ($feature === 'api' && ! $plan->permite_api) {
            return response()->json([
                'error' => 'Acceso a la API no autorizado por tu plan de suscripción.',
            ], 403);
        }

        return $next($request);
    }
}
