<?php

namespace App\Enums;

enum EstadoCotizacion: string
{
    case PENDIENTE = 'PENDIENTE';
    case CONVERTIDA = 'CONVERTIDA';
    case ANULADA = 'ANULADA';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::CONVERTIDA => 'Convertida en Venta',
            self::ANULADA => 'Anulada',
        };
    }
}
