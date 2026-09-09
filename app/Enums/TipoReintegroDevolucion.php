<?php

namespace App\Enums;

enum TipoReintegroDevolucion: string
{
    case EFECTIVO = 'EFECTIVO';
    case SALDO_FAVOR = 'SALDO_FAVOR';
    case AJUSTE_CARTERA = 'AJUSTE_CARTERA';

    public function label(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Reintegro en Efectivo',
            self::SALDO_FAVOR => 'Saldo a Favor del Cliente',
            self::AJUSTE_CARTERA => 'Rebaja / Ajuste de Cartera',
        };
    }
}
