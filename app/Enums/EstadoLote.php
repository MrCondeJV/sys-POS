<?php

namespace App\Enums;

enum EstadoLote: string
{
    case DISPONIBLE = 'DISPONIBLE';
    case PROXIMO_VENCER = 'PROXIMO_VENCER';
    case VENCIDO = 'VENCIDO';
    case AGOTADO = 'AGOTADO';

    public function label(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'Disponible',
            self::PROXIMO_VENCER => 'Próximo a Vencer',
            self::VENCIDO => 'Vencido',
            self::AGOTADO => 'Agotado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DISPONIBLE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::PROXIMO_VENCER => 'bg-amber-50 text-amber-700 border-amber-200',
            self::VENCIDO => 'bg-rose-50 text-rose-700 border-rose-200',
            self::AGOTADO => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
