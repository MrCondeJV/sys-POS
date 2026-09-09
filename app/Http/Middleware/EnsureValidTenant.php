<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si es superadministrador, tiene autorización transversal
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        $activeTenantId = CompanyContext::getId();

        // Inspeccionar todos los parámetros enlazados en la ruta
        foreach ($request->route()->parameters() as $param) {
            if ($param instanceof Empresa) {
                if ($param->id !== $activeTenantId) {
                    abort(403, 'Acceso no autorizado a recursos de otra empresa.');
                }
            } elseif ($param instanceof Model && isset($param->empresa_id)) {
                if ($param->empresa_id !== $activeTenantId) {
                    abort(403, 'Acceso no autorizado a registros de otra empresa.');
                }
            }
        }

        return $next($request);
    }
}
