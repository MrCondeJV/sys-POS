<?php

namespace App\Enums;

enum EstadoDian: string
{
    case PENDIENTE = 'PENDIENTE';
    case ENVIADO = 'ENVIADO';
    case ACEPTADO = 'ACEPTADO';
    case RECHAZADO = 'RECHAZADO';
    case ERROR = 'ERROR';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente de Envío',
            self::ENVIADO => 'Enviado a DIAN',
            self::ACEPTADO => 'Aceptado por DIAN',
            self::RECHAZADO => 'Rechazado por DIAN',
            self::ERROR => 'Error Técnico',
        };
    }
}
