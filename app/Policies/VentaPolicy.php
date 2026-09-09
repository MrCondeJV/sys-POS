<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\User;
use App\Models\Venta;

class VentaPolicy
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

    public function view(User $user, Venta $venta): bool
    {
        return $user->empresa_id === $venta->empresa_id && $user->can(PermisoSistema::VENTAS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::VENTAS_CREAR->value);
    }

    public function anular(User $user, Venta $venta): bool
    {
        return $user->empresa_id === $venta->empresa_id && $user->can(PermisoSistema::VENTAS_ANULAR->value);
    }
}
