<?php

namespace App\Enums;

enum TipoNotificacion: string
{
    case STOCK_BAJO = 'STOCK_BAJO';
    case PROXIMO_A_VENCER = 'PROXIMO_A_VENCER';
    case CAJA_CERRADA = 'CAJA_CERRADA';
    case VENTA_IMPORTANTE = 'VENTA_IMPORTANTE';
    case SISTEMA_ERROR = 'SISTEMA_ERROR';

    public function label(): string
    {
        return match ($this) {
            self::STOCK_BAJO => 'Stock Bajo en Inventario',
            self::PROXIMO_A_VENCER => 'Producto Próximo a Vencer',
            self::CAJA_CERRADA => 'Cierre de Turno de Caja',
            self::VENTA_IMPORTANTE => 'Venta Relevante Registrada',
            self::SISTEMA_ERROR => 'Alerta del Sistema',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::STOCK_BAJO => '📦',
            self::PROXIMO_A_VENCER => '⏳',
            self::CAJA_CERRADA => '🔒',
            self::VENTA_IMPORTANTE => '💰',
            self::SISTEMA_ERROR => '⚠️',
        };
    }
}
