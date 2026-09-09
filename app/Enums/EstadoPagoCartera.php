<?php

namespace App\Enums;

enum EstadoPagoCartera: string
{
    case APLICADO = 'APLICADO';
    case ANULADO = 'ANULADO';

    public function label(): string
    {
        return match ($this) {
            self::APLICADO => 'Aplicado',
            self::ANULADO => 'Anulado',
        };
    }
}
