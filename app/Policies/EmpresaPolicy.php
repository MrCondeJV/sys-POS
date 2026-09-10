<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
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
     * Determina si el usuario puede listar todas las empresas (Super Admin).
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determina si el usuario puede registrar nuevas empresas (Super Admin).
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determina si el usuario puede ver la empresa.
     */
    public function view(User $user, Empresa $empresa): bool
    {
        return $user->empresa_id === $empresa->id;
    }

    /**
     * Determina si el usuario puede actualizar la empresa.
     */
    public function update(User $user, Empresa $empresa): bool
    {
        return $user->empresa_id === $empresa->id && ($user->isAdminEmpresa() || $user->can('empresa.gestionar'));
    }

    /**
     * Determina si el usuario puede eliminar la empresa.
     */
    public function delete(User $user, Empresa $empresa): bool
    {
        return false; // Solo Super Admin vía before()
    }
}
