<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Producto $producto): bool
    {
        return $user->empresa_id === $producto->empresa_id && $user->can(PermisoSistema::PRODUCTOS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::PRODUCTOS_CREAR->value);
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->empresa_id === $producto->empresa_id && $user->can(PermisoSistema::PRODUCTOS_EDITAR->value);
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->empresa_id === $producto->empresa_id && $user->can(PermisoSistema::PRODUCTOS_ELIMINAR->value);
    }
}
