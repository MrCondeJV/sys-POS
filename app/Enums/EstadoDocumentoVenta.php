<?php

namespace App\Enums;

enum EstadoDocumentoVenta: string
{
    case BORRADOR = 'BORRADOR';
    case EMITIDO = 'EMITIDO';
    case ANULADO = 'ANULADO';

    public function label(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::EMITIDO => 'Emitido',
            self::ANULADO => 'Anulado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BORRADOR => 'bg-amber-50 text-amber-700 border-amber-200',
            self::EMITIDO => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::ANULADO => 'bg-red-50 text-red-700 border-red-200',
        };
    }
}
