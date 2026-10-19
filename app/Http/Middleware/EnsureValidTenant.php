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

        // Si es superadministrador, tiene autorización transversal pero requiere contexto para rutas de negocio
        if ($user && $user->isSuperAdmin()) {
            if (! CompanyContext::check() && $this->isBusinessRoute($request)) {
                return redirect()->route('empresas.index')
                    ->with('warning', 'Debes entrar a un negocio primero desde el panel de empresas para acceder a los módulos operativos.');
            }

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

    /**
     * Determina si la ruta actual es una ruta operativa dependiente de un contexto de empresa.
     */
    protected function isBusinessRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return false;
        }

        $platformPrefixes = [
            'empresas.',
            'usuarios.',
            'auditoria.',
            'login',
            'logout',
            'dashboard',
        ];

        foreach ($platformPrefixes as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                return false;
            }
        }

        return true;
    }
}
