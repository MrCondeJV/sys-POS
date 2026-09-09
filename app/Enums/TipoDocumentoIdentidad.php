<?php

namespace App\Enums;

enum TipoDocumentoIdentidad: string
{
    case NIT = 'NIT';
    case CC = 'CC';
    case CE = 'CE';
    case PASAPORTE = 'PASAPORTE';
    case TI = 'TI';
    case RUT = 'RUT';

    public function label(): string
    {
        return match ($this) {
            self::NIT => 'Número de Identificación Tributaria (NIT)',
            self::CC => 'Cédula de Ciudadanía',
            self::CE => 'Cédula de Extranjería',
            self::PASAPORTE => 'Pasaporte',
            self::TI => 'Tarjeta de Identidad',
            self::RUT => 'Registro Único Tributario',
        };
    }
}
