<?php

namespace App\Enums;

enum EstadoCompra: string
{
    case REGISTRADA = 'REGISTRADA';
    case ANULADA = 'ANULADA';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRADA => 'Registrada',
            self::ANULADA => 'Anulada',
        };
    }

    public function isRegistrada(): bool
    {
        return $this === self::REGISTRADA;
    }

    public function isAnulada(): bool
    {
        return $this === self::ANULADA;
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::REGISTRADA => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::ANULADA => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
