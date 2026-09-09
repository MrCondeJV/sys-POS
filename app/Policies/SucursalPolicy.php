<?php

namespace App\Policies;

use App\Models\Sucursal;
use App\Models\User;

class SucursalPolicy
{
    /**
     * Super administradores pueden realizar cualquier acción.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determina si el usuario puede ver la sucursal.
     */
    public function view(User $user, Sucursal $sucursal): bool
    {
        return $user->empresa_id === $sucursal->empresa_id;
    }

    /**
     * Determina si el usuario puede crear sucursales.
     */
    public function create(User $user): bool
    {
        return $user->isAdminEmpresa() || $user->can('sucursales.gestionar');
    }

    /**
     * Determina si el usuario puede actualizar la sucursal.
     */
    public function update(User $user, Sucursal $sucursal): bool
    {
        return $user->empresa_id === $sucursal->empresa_id && ($user->isAdminEmpresa() || $user->can('sucursales.gestionar'));
    }

    /**
     * Determina si el usuario puede eliminar la sucursal.
     */
    public function delete(User $user, Sucursal $sucursal): bool
    {
        // No se permite eliminar la sucursal principal
        if ($sucursal->isPrincipal()) {
            return false;
        }

        return $user->empresa_id === $sucursal->empresa_id && $user->isAdminEmpresa();
    }
}
