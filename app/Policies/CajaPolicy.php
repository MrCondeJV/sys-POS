<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Caja;
use App\Models\User;

class CajaPolicy
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
        return $user->can(PermisoSistema::CAJA_VER->value);
    }

    public function view(User $user, Caja $caja): bool
    {
        return $user->empresa_id === $caja->empresa_id && $user->can(PermisoSistema::CAJA_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::CAJA_ADMINISTRAR->value);
    }

    public function abrir(User $user, Caja $caja): bool
    {
        return $user->empresa_id === $caja->empresa_id && $user->can(PermisoSistema::CAJA_ABRIR->value);
    }

    public function cerrar(User $user, Caja $caja): bool
    {
        return $user->empresa_id === $caja->empresa_id && $user->can(PermisoSistema::CAJA_CERRAR->value);
    }

    public function movimiento(User $user, Caja $caja): bool
    {
        return $user->empresa_id === $caja->empresa_id && $user->can(PermisoSistema::CAJA_MOVIMIENTO->value);
    }
}
