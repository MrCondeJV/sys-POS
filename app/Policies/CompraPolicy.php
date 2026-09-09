<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Compra;
use App\Models\User;

class CompraPolicy
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
        return $user->can(PermisoSistema::COMPRAS_VER->value);
    }

    public function view(User $user, Compra $compra): bool
    {
        return $user->empresa_id === $compra->empresa_id && $user->can(PermisoSistema::COMPRAS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::COMPRAS_CREAR->value);
    }

    public function anular(User $user, Compra $compra): bool
    {
        return $user->empresa_id === $compra->empresa_id && $user->can(PermisoSistema::COMPRAS_ANULAR->value);
    }
}
