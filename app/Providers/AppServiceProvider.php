<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Policies\EmpresaPolicy;
use App\Policies\SucursalPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registro explícito de Policies para integridad de acceso
        Gate::policy(Empresa::class, EmpresaPolicy::class);
        Gate::policy(Sucursal::class, SucursalPolicy::class);

        // Super Admin Bypass global auditado
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
