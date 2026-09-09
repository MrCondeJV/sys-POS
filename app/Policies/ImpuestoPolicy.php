<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Impuesto;
use App\Models\User;

class ImpuestoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::IMPUESTOS_VER->value);
    }

    public function view(User $user, Impuesto $impuesto): bool
    {
        return $user->hasPermissionTo(PermisoSistema::IMPUESTOS_VER->value) &&
               $user->empresa_id === $impuesto->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::IMPUESTOS_CREAR->value);
    }

    public function update(User $user, Impuesto $impuesto): bool
    {
        return $user->hasPermissionTo(PermisoSistema::IMPUESTOS_EDITAR->value) &&
               $user->empresa_id === $impuesto->empresa_id;
    }

    public function delete(User $user, Impuesto $impuesto): bool
    {
        return $user->hasPermissionTo(PermisoSistema::IMPUESTOS_ELIMINAR->value) &&
               $user->empresa_id === $impuesto->empresa_id;
    }
}
