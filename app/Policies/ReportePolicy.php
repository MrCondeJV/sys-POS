<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\User;

class ReportePolicy
{
    /**
     * Determina si el usuario puede acceder a cualquier reporte del sistema.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::REPORTES_VER->value);
    }
}
