<?php

namespace App\Enums;

enum MetodoPagoVenta: string
{
    case EFECTIVO = 'EFECTIVO';
    case TARJETA_DEBITO = 'TARJETA_DEBITO';
    case TARJETA_CREDITO = 'TARJETA_CREDITO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case CREDITO = 'CREDITO';
    case MIXTO = 'MIXTO';

    public function label(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::TARJETA_DEBITO => 'Tarjeta Débito',
            self::TARJETA_CREDITO => 'Tarjeta Crédito',
            self::TRANSFERENCIA => 'Transferencia Bancaria',
            self::CREDITO => 'Crédito Comercial',
            self::MIXTO => 'Pago Mixto / Combinado',
        };
    }
}
