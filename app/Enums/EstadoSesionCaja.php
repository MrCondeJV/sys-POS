<?php

namespace App\Enums;

enum EstadoSesionCaja: string
{
    case ABIERTA = 'ABIERTA';
    case CERRADA = 'CERRADA';

    public function label(): string
    {
        return match ($this) {
            self::ABIERTA => 'Abierta',
            self::CERRADA => 'Cerrada',
        };
    }
}
