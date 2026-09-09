<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Categoria;
use App\Models\User;

class CategoriaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Categoria $categoria): bool
    {
        return $user->empresa_id === $categoria->empresa_id && $user->can(PermisoSistema::PRODUCTOS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::PRODUCTOS_CREAR->value);
    }

    public function update(User $user, Categoria $categoria): bool
    {
        return $user->empresa_id === $categoria->empresa_id && $user->can(PermisoSistema::PRODUCTOS_EDITAR->value);
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $user->empresa_id === $categoria->empresa_id && $user->can(PermisoSistema::PRODUCTOS_ELIMINAR->value);
    }
}
