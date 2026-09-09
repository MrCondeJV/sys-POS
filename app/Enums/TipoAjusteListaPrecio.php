<?php

namespace App\Enums;

enum TipoAjusteListaPrecio: string
{
    case FIJO = 'FIJO';
    case PORCENTAJE_DESCUENTO = 'PORCENTAJE_DESCUENTO';
    case PORCENTAJE_AUMENTO = 'PORCENTAJE_AUMENTO';

    public function label(): string
    {
        return match ($this) {
            self::FIJO => 'Precios Fijos por Producto',
            self::PORCENTAJE_DESCUENTO => 'Descuento General (%)',
            self::PORCENTAJE_AUMENTO => 'Margen / Aumento General (%)',
        };
    }
}
