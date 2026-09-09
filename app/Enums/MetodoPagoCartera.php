<?php

namespace App\Enums;

enum MetodoPagoCartera: string
{
    case EFECTIVO = 'EFECTIVO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case TARJETA_DEBITO = 'TARJETA_DEBITO';
    case TARJETA_CREDITO = 'TARJETA_CREDITO';
    case CHEQUE = 'CHEQUE';
    case OTRO = 'OTRO';

    public function label(): string
    {
        return match ($this) {
            self::EFECTIVO => 'Efectivo',
            self::TRANSFERENCIA => 'Transferencia Bancaria',
            self::TARJETA_DEBITO => 'Tarjeta Débito',
            self::TARJETA_CREDITO => 'Tarjeta Crédito',
            self::CHEQUE => 'Cheque',
            self::OTRO => 'Otro Medio de Pago',
        };
    }
}
