<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeApiCompanyContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->empresa_id) {
                CompanyContext::setCompanyId($user->empresa_id);
                setPermissionsTeamId($user->empresa_id);
            }

            $sucursalId = $request->header('X-Sucursal-ID') ?: $user->sucursal_id;
            if ($sucursalId) {
                BranchContext::setId((int) $sucursalId);
            }
        }

        return $next($request);
    }
}
