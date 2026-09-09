<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Policies\CategoriaPolicy;
use App\Policies\EmpresaPolicy;
use App\Policies\MarcaPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\SucursalPolicy;
use App\Policies\UnidadMedidaPolicy;
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
        Gate::policy(Producto::class, ProductoPolicy::class);
        Gate::policy(Categoria::class, CategoriaPolicy::class);
        Gate::policy(Marca::class, MarcaPolicy::class);
        Gate::policy(UnidadMedida::class, UnidadMedidaPolicy::class);

        // Super Admin Bypass global auditado
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
