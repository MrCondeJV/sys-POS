<?php

namespace App\Enums;

enum TipoPersona: string
{
    case NATURAL = 'NATURAL';
    case JURIDICA = 'JURIDICA';

    public function label(): string
    {
        return match ($this) {
            self::NATURAL => 'Persona Natural',
            self::JURIDICA => 'Persona Jurídica',
        };
    }
}
