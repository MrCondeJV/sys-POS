<?php

namespace App\Enums;

enum TipoImpuesto: string
{
    case IVA = 'IVA';
    case EXENTO = 'EXENTO';
    case EXCLUIDO = 'EXCLUIDO';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match ($this) {
            self::IVA => 'Impuesto al Valor Agregado (IVA)',
            self::EXENTO => 'Exento de Impuesto (Tarifa 0%)',
            self::EXCLUIDO => 'Excluido de Impuesto (No Gravable)',
            self::OTRO => 'Otro Gravamen / Tasa',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::IVA => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::EXENTO => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::EXCLUIDO => 'bg-slate-100 text-slate-700 border-slate-200',
            self::OTRO => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    }
}
