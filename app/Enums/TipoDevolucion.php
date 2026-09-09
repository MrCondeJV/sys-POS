<?php

namespace App\Enums;

enum TipoDevolucion: string
{
    case TOTAL = 'TOTAL';
    case PARCIAL = 'PARCIAL';

    public function label(): string
    {
        return match ($this) {
            self::TOTAL => 'Devolución Total',
            self::PARCIAL => 'Devolución Parcial',
        };
    }
}
