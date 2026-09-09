<?php

namespace App\Http\Middleware;

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
            } else {
                CompanyContext::clear();
                setPermissionsTeamId(null);
            }
        } else {
            CompanyContext::clear();
            setPermissionsTeamId(null);
        }

        return $next($request);
    }
}
