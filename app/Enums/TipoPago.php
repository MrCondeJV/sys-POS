<?php

namespace App\Enums;

enum TipoPago: string
{
    case CONTADO = 'CONTADO';
    case CREDITO = 'CREDITO';

    public function label(): string
    {
        return match ($this) {
            self::CONTADO => 'Contado / Efectivo',
            self::CREDITO => 'Crédito Comercial',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::CONTADO => 'bg-blue-50 text-blue-700 border-blue-200',
            self::CREDITO => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }
}
