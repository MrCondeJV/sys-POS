<?php

namespace App\Enums;

enum TipoMovimientoCaja: string
{
    case INGRESO = 'INGRESO';
    case EGRESO = 'EGRESO';

    public function label(): string
    {
        return match ($this) {
            self::INGRESO => 'Ingreso de Dinero',
            self::EGRESO => 'Retiro / Egreso de Dinero',
        };
    }
}
