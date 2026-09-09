<?php

namespace App\Http\Middleware;

use App\Models\Sucursal;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if (! $user->isActivo()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Tu cuenta se encuentra inactiva. Comunícate con el administrador.',
                ]);
            }

            if ($user->empresa_id) {
                CompanyContext::setCompanyId($user->empresa_id);
                setPermissionsTeamId($user->empresa_id);

                // Resolver y validar sucursal activa
                $this->resolveActiveBranch($user);
            } else {
                CompanyContext::clear();
                BranchContext::clear();
                setPermissionsTeamId(null);
            }
        } else {
            CompanyContext::clear();
            BranchContext::clear();
            setPermissionsTeamId(null);
        }

        return $next($request);
    }

    /**
     * Resuelve la sucursal activa asegurando que pertenezca a la empresa del usuario.
     */
    protected function resolveActiveBranch($user): void
    {
        $sessionBranchId = session('sucursal_activa_id');

        // Validar que la sucursal en sesión pertenezca realmente a la empresa
        if ($sessionBranchId) {
            $validBranch = Sucursal::withoutGlobalScopes()
                ->where('id', $sessionBranchId)
                ->where('empresa_id', $user->empresa_id)
                ->first();

            if ($validBranch) {
                BranchContext::setId($validBranch->id);

                return;
            }
        }

        // Si no hay sucursal en sesión o no es válida, usar la del usuario o la principal
        $defaultBranchId = $user->sucursal_id
            ?? $user->empresa?->sucursalPrincipal?->id
            ?? $user->empresa?->sucursales()->first()?->id;

        BranchContext::setId($defaultBranchId);
    }
}
