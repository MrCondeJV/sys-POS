<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
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
        return $user->can(PermisoSistema::CLIENTES_VER->value);
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->empresa_id === $cliente->empresa_id && $user->can(PermisoSistema::CLIENTES_VER->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermisoSistema::CLIENTES_CREAR->value);
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->empresa_id === $cliente->empresa_id && $user->can(PermisoSistema::CLIENTES_EDITAR->value);
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        // El Consumidor Final predeterminado jamás puede ser eliminado por nadie
        if ($cliente->isConsumidorFinal()) {
            return false;
        }

        return $user->empresa_id === $cliente->empresa_id && $user->can(PermisoSistema::CLIENTES_ELIMINAR->value);
    }
}
