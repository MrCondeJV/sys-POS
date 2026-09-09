<?php

namespace App\Enums;

enum EstadoVenta: string
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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::COMPLETADA => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::ANULADA => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
