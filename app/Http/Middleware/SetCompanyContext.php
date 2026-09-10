<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
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

            if ($user->isSuperAdmin()) {
                // Super Admin: resolver tenant activo desde sesión o primera empresa disponible
                $sessionEmpresaId = session('superadmin_empresa_id');
                $empresaActiva = null;

                if ($sessionEmpresaId) {
                    $empresaActiva = Empresa::withoutGlobalScopes()->find($sessionEmpresaId);
                }

                if (! $empresaActiva) {
                    $empresaActiva = $user->empresa_id
                        ? Empresa::withoutGlobalScopes()->find($user->empresa_id)
                        : Empresa::withoutGlobalScopes()->first();

                    if ($empresaActiva) {
                        session(['superadmin_empresa_id' => $empresaActiva->id]);
                    }
                }

                if ($empresaActiva) {
                    CompanyContext::setCompany($empresaActiva);
                    setPermissionsTeamId($empresaActiva->id);
                    $user->unsetRelation('roles');
                    $user->unsetRelation('permissions');
                    $this->resolveActiveBranch($user);
                } else {
                    CompanyContext::clear();
                    BranchContext::clear();
                    setPermissionsTeamId(null);
                }
            } elseif ($user->empresa_id) {
                CompanyContext::setCompanyId($user->empresa_id);
                setPermissionsTeamId($user->empresa_id);
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');

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
        $empresaId = CompanyContext::getId() ?? $user->empresa_id;

        // Validar que la sucursal en sesión pertenezca realmente a la empresa
        if ($sessionBranchId && $empresaId) {
            $validBranch = Sucursal::withoutGlobalScopes()
                ->where('id', $sessionBranchId)
                ->where('empresa_id', $empresaId)
                ->first();

            if ($validBranch) {
                BranchContext::setId($validBranch->id);

                return;
            }
        }

        $empresa = CompanyContext::getCompany() ?? $user->empresa;

        // Si no hay sucursal en sesión o no es válida, usar la del usuario o la principal
        $defaultBranchId = ($user->empresa_id === $empresaId ? $user->sucursal_id : null)
            ?? $empresa?->sucursalPrincipal?->id
            ?? $empresa?->sucursales()->first()?->id;

        BranchContext::setId($defaultBranchId);
    }
}
