<?php

namespace App\Enums;

enum EstadoGeneral: string
{
    case ACTIVO = 'ACTIVO';
    case INACTIVO = 'INACTIVO';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVO => 'Activo',
            self::INACTIVO => 'Inactivo',
        };
    }

    public function isActivo(): bool
    {
        return $this === self::ACTIVO;
    }
}
