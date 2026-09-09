<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\ListaPrecio;
use App\Models\User;

class ListaPrecioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::LISTAS_PRECIOS_VER->value);
    }

    public function view(User $user, ListaPrecio $listaPrecio): bool
    {
        return $user->hasPermissionTo(PermisoSistema::LISTAS_PRECIOS_VER->value) &&
               $user->empresa_id === $listaPrecio->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermisoSistema::LISTAS_PRECIOS_CREAR->value);
    }

    public function update(User $user, ListaPrecio $listaPrecio): bool
    {
        return $user->hasPermissionTo(PermisoSistema::LISTAS_PRECIOS_EDITAR->value) &&
               $user->empresa_id === $listaPrecio->empresa_id;
    }

    public function delete(User $user, ListaPrecio $listaPrecio): bool
    {
        return $user->hasPermissionTo(PermisoSistema::LISTAS_PRECIOS_ELIMINAR->value) &&
               $user->empresa_id === $listaPrecio->empresa_id;
    }
}
