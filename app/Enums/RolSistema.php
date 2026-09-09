<?php

namespace App\Enums;

enum RolSistema: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN_EMPRESA = 'ADMIN_EMPRESA';
    case ADMIN_SUCURSAL = 'ADMIN_SUCURSAL';
    case CAJERO = 'CAJERO';
    case VENDEDOR = 'VENDEDOR';
    case CONTADOR = 'CONTADOR';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrador del Sistema',
            self::ADMIN_EMPRESA => 'Administrador de Empresa',
            self::ADMIN_SUCURSAL => 'Administrador de Sucursal',
            self::CAJERO => 'Cajero de Punto de Venta',
            self::VENDEDOR => 'Asesor / Vendedor',
            self::CONTADOR => 'Contador / Auditor',
        };
    }
}
