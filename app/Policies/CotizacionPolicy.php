<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Cotizacion;
use App\Models\User;

/**
 * Las cotizaciones reutilizan los permisos de ventas para no exigir re-sembrar los roles existentes.
 */
class CotizacionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can(PermisoSistema::VENTAS_VER->value);
    }

    public function view(User $user, Cotizacion $cotizacion): bool
    {
        return $user->empresa_id === $cotizacion->empresa_id && $user->can(PermisoSistema::VENTAS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::VENTAS_CREAR->value);
    }

    public function anular(User $user, Cotizacion $cotizacion): bool
    {
        return $user->empresa_id === $cotizacion->empresa_id && $user->can(PermisoSistema::VENTAS_CREAR->value);
    }
}
