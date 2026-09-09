<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\CuentaPorCobrar;
use App\Models\User;

class CuentaPorCobrarPolicy
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
        return $user->can(PermisoSistema::CARTERA_VER->value);
    }

    public function view(User $user, CuentaPorCobrar $cuenta): bool
    {
        return $user->empresa_id === $cuenta->empresa_id && $user->can(PermisoSistema::CARTERA_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::CARTERA_CREAR->value);
    }

    public function abonar(User $user, CuentaPorCobrar $cuenta): bool
    {
        return $user->empresa_id === $cuenta->empresa_id && $user->can(PermisoSistema::CARTERA_ABONAR->value);
    }

    public function anular(User $user, CuentaPorCobrar $cuenta): bool
    {
        return $user->empresa_id === $cuenta->empresa_id && $user->can(PermisoSistema::CARTERA_ANULAR->value);
    }
}
