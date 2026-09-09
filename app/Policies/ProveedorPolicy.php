<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Proveedor;
use App\Models\User;

class ProveedorPolicy
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
        return $user->can(PermisoSistema::PROVEEDORES_VER->value);
    }

    public function view(User $user, Proveedor $proveedor): bool
    {
        return $user->empresa_id === $proveedor->empresa_id && $user->can(PermisoSistema::PROVEEDORES_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::PROVEEDORES_CREAR->value);
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $user->empresa_id === $proveedor->empresa_id && $user->can(PermisoSistema::PROVEEDORES_EDITAR->value);
    }

    public function delete(User $user, Proveedor $proveedor): bool
    {
        return $user->empresa_id === $proveedor->empresa_id && $user->can(PermisoSistema::PROVEEDORES_ELIMINAR->value);
    }
}
