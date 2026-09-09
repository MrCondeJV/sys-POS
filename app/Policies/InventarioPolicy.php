<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\User;

class InventarioPolicy
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
        return $user->can(PermisoSistema::INVENTARIO_VER->value);
    }

    public function view(User $user, Inventario $inventario): bool
    {
        return $user->empresa_id === $inventario->empresa_id && $user->can(PermisoSistema::INVENTARIO_VER->value);
    }

    public function viewKardex(User $user, Producto $producto): bool
    {
        return $user->empresa_id === $producto->empresa_id && $user->can(PermisoSistema::INVENTARIO_VER->value);
    }

    public function ajustar(User $user): bool
    {
        return $user->can(PermisoSistema::INVENTARIO_AJUSTAR->value);
    }

    public function trasladar(User $user): bool
    {
        return $user->can(PermisoSistema::INVENTARIO_AJUSTAR->value);
    }
}
