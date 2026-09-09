<?php

namespace App\Enums;

enum EstadoResolucionFacturacion: string
{
    case ACTIVA = 'ACTIVA';
    case VENCIDA = 'VENCIDA';
    case AGOTADA = 'AGOTADA';
    case INACTIVA = 'INACTIVA';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA => 'Activa',
            self::VENCIDA => 'Vencida',
            self::AGOTADA => 'Agotada',
            self::INACTIVA => 'Inactiva',
        };
    }
}
