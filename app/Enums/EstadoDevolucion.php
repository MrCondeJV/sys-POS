<?php

namespace App\Enums;

enum EstadoDevolucion: string
{
    case COMPLETADA = 'COMPLETADA';
    case ANULADA = 'ANULADA';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETADA => 'Completada',
            self::ANULADA => 'Anulada',
        };
    }
}
