<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\DocumentoVenta;
use App\Models\User;

class DocumentoVentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_VER->value);
    }

    public function view(User $user, DocumentoVenta $documento): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_VER->value) &&
               $user->empresa_id === $documento->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_EMITIR->value);
    }

    public function update(User $user, DocumentoVenta $documento): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_EMITIR->value) &&
               $user->empresa_id === $documento->empresa_id;
    }

    public function anular(User $user, DocumentoVenta $documento): bool
    {
        return $user->hasPermissionTo(PermisoSistema::DOCUMENTOS_ANULAR->value) &&
               $user->empresa_id === $documento->empresa_id &&
               $documento->esEmitido();
    }
}
