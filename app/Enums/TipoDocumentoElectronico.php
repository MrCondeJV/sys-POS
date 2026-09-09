<?php

namespace App\Enums;

enum TipoDocumentoElectronico: string
{
    case FACTURA_ELECTRONICA = 'FACTURA_ELECTRONICA';
    case NOTA_CREDITO_ELECTRONICA = 'NOTA_CREDITO_ELECTRONICA';
    case NOTA_DEBITO_ELECTRONICA = 'NOTA_DEBITO_ELECTRONICA';
    case DOCUMENTO_EQUIVALENTE_ELECTRONICO = 'DOCUMENTO_EQUIVALENTE_ELECTRONICO';

    public function label(): string
    {
        return match ($this) {
            self::FACTURA_ELECTRONICA => 'Factura Electrónica de Venta',
            self::NOTA_CREDITO_ELECTRONICA => 'Nota Crédito Electrónica',
            self::NOTA_DEBITO_ELECTRONICA => 'Nota Débito Electrónica',
            self::DOCUMENTO_EQUIVALENTE_ELECTRONICO => 'Documento Equivalente Electrónico POS',
        };
    }
}
