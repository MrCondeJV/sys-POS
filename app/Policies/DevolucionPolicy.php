<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Devolucion;
use App\Models\User;

class DevolucionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::VENTAS_VER->value);
    }

    public function view(User $user, Devolucion $devolucion): bool
    {
        return $user->hasPermissionTo(PermisoSistema::VENTAS_VER->value) &&
               $user->empresa_id === $devolucion->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::VENTAS_DEVOLVER->value);
    }
}
