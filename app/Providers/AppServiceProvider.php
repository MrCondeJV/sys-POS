<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\PagoCliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use App\Policies\CategoriaPolicy;
use App\Policies\ClientePolicy;
use App\Policies\CompraPolicy;
use App\Policies\CuentaPorCobrarPolicy;
use App\Policies\EmpresaPolicy;
use App\Policies\InventarioPolicy;
use App\Policies\MarcaPolicy;
use App\Policies\PagoClientePolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ProveedorPolicy;
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
        Gate::policy(Inventario::class, InventarioPolicy::class);
        Gate::policy(Proveedor::class, ProveedorPolicy::class);
        Gate::policy(Compra::class, CompraPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(CuentaPorCobrar::class, CuentaPorCobrarPolicy::class);
        Gate::policy(PagoCliente::class, PagoClientePolicy::class);

        // Super Admin Bypass global auditado
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // Fase 20: Eventos & Notificaciones del Sistema
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\StockBajoEvent::class,
            \App\Listeners\NotificarStockBajoListener::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\CajaCerradaEvent::class,
            \App\Listeners\NotificarCajaCerradaListener::class
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\VentaImportanteEvent::class,
            \App\Listeners\NotificarVentaImportanteListener::class
        );
    }
}
