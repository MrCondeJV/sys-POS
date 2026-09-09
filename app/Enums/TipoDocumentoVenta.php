<?php

namespace App\Enums;

enum TipoDocumentoVenta: string
{
    case TICKET = 'TICKET';
    case DOCUMENTO_EQUIVALENTE = 'DOCUMENTO_EQUIVALENTE';
    case FACTURA = 'FACTURA';

    public function label(): string
    {
        return match ($this) {
            self::TICKET => 'Ticket POS',
            self::DOCUMENTO_EQUIVALENTE => 'Documento Equivalente POS',
            self::FACTURA => 'Factura de Venta',
        };
    }

    public function prefijoPorDefecto(): string
    {
        return match ($this) {
            self::TICKET => 'TIK',
            self::DOCUMENTO_EQUIVALENTE => 'EQV',
            self::FACTURA => 'FAC',
        };
    }
}
