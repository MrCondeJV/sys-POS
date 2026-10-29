<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\ResolucionFacturacion;
use App\Models\User;

/**
 * Las resoluciones DIAN son configuración fiscal de la empresa: cualquier rol con
 * acceso a documentos puede consultarlas, pero solo quien gestiona la empresa
 * puede crearlas, modificarlas o eliminarlas.
 */
class ResolucionFacturacionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::EMPRESA_GESTIONAR->value);
    }

    public function update(User $user, ResolucionFacturacion $resolucion): bool
    {
        return $user->hasPermissionTo(PermisoSistema::EMPRESA_GESTIONAR->value) &&
               $user->empresa_id === $resolucion->empresa_id;
    }

    public function delete(User $user, ResolucionFacturacion $resolucion): bool
    {
        return $user->hasPermissionTo(PermisoSistema::EMPRESA_GESTIONAR->value) &&
               $user->empresa_id === $resolucion->empresa_id;
    }
}
