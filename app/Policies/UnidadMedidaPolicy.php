<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\UnidadMedida;
use App\Models\User;

class UnidadMedidaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, UnidadMedida $unidad): bool
    {
        return $user->empresa_id === $unidad->empresa_id && $user->can(PermisoSistema::PRODUCTOS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::PRODUCTOS_CREAR->value);
    }

    public function update(User $user, UnidadMedida $unidad): bool
    {
        return $user->empresa_id === $unidad->empresa_id && $user->can(PermisoSistema::PRODUCTOS_EDITAR->value);
    }

    public function delete(User $user, UnidadMedida $unidad): bool
    {
        return $user->empresa_id === $unidad->empresa_id && $user->can(PermisoSistema::PRODUCTOS_ELIMINAR->value);
    }
}
