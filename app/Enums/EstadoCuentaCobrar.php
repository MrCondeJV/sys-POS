<?php

namespace App\Enums;

enum EstadoCuentaCobrar: string
{
    case PENDIENTE = 'PENDIENTE';
    case PARCIAL = 'PARCIAL';
    case PAGADA = 'PAGADA';
    case ANULADA = 'ANULADA';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::PARCIAL => 'Abono Parcial',
            self::PAGADA => 'Pagada / Cancelada',
            self::ANULADA => 'Anulada',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDIENTE => 'bg-amber-100 text-amber-800 border-amber-200',
            self::PARCIAL => 'bg-blue-100 text-blue-800 border-blue-200',
            self::PAGADA => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::ANULADA => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
