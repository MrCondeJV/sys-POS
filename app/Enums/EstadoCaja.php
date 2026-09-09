<?php

namespace App\Enums;

enum EstadoCaja: string
{
    case ACTIVA = 'ACTIVA';
    case INACTIVA = 'INACTIVA';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA => 'Activa',
            self::INACTIVA => 'Inactiva',
        };
    }
}
