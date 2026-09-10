<?php

namespace App\Policies;

use App\Enums\RolSistema;
use App\Models\User;

class UserPolicy
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
     * Determina si el usuario puede listar usuarios.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RolSistema::ADMIN_EMPRESA->value)
            || $user->hasPermissionTo('usuarios.ver')
            || $user->hasPermissionTo('usuarios.gestionar');
    }

    /**
     * Determina si el usuario puede ver un usuario específico.
     */
    public function view(User $user, User $model): bool
    {
        return $user->empresa_id === $model->empresa_id && (
            $user->hasRole(RolSistema::ADMIN_EMPRESA->value)
            || $user->hasPermissionTo('usuarios.ver')
            || $user->hasPermissionTo('usuarios.gestionar')
        );
    }

    /**
     * Determina si el usuario puede crear nuevos usuarios.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(RolSistema::ADMIN_EMPRESA->value)
            || $user->hasPermissionTo('usuarios.gestionar');
    }

    /**
     * Determina si el usuario puede actualizar un usuario.
     */
    public function update(User $user, User $model): bool
    {
        return $user->empresa_id === $model->empresa_id && (
            $user->hasRole(RolSistema::ADMIN_EMPRESA->value)
            || $user->hasPermissionTo('usuarios.gestionar')
        );
    }

    /**
     * Determina si el usuario puede eliminar un usuario (no puede eliminarse a sí mismo).
     */
    public function delete(User $user, User $model): bool
    {
        return $user->empresa_id === $model->empresa_id 
            && $user->id !== $model->id 
            && (
                $user->hasRole(RolSistema::ADMIN_EMPRESA->value)
                || $user->hasPermissionTo('usuarios.gestionar')
            );
    }
}
