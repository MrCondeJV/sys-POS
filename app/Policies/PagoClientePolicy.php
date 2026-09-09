<?php

namespace App\Policies;

use App\Enums\PermisoSistema;
use App\Models\PagoCliente;
use App\Models\User;

class PagoClientePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, PagoCliente $pago): bool
    {
        return $user->empresa_id === $pago->empresa_id && $user->can(PermisoSistema::CARTERA_VER->value);
    }

    public function anular(User $user, PagoCliente $pago): bool
    {
        return $user->empresa_id === $pago->empresa_id && $user->can(PermisoSistema::CARTERA_ANULAR->value);
    }
}
