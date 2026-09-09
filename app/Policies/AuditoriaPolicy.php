<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Auditoria;
use App\Models\User;

class AuditoriaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::AUDITORIA_VER->value);
    }

    public function view(User $user, Auditoria $auditoria): bool
    {
        return $user->hasPermissionTo(PermisoSistema::AUDITORIA_VER->value) &&
               ($auditoria->empresa_id === null || $user->empresa_id === $auditoria->empresa_id);
    }
}
