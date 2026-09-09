<?php

namespace App\Enums;

enum TipoComprobanteVenta: string
{
    case TICKET = 'TICKET';
    case FACTURA = 'FACTURA';
    case NOTA_VENTA = 'NOTA_VENTA';

    public function label(): string
    {
        return match ($this) {
            self::TICKET => 'Ticket POS',
            self::FACTURA => 'Factura de Venta',
            self::NOTA_VENTA => 'Nota de Entrega / Remisión',
        };
    }
}
