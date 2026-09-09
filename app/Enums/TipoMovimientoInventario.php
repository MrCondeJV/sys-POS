<?php

namespace App\Enums;

enum TipoMovimientoInventario: string
{
    case ENTRADA_COMPRA = 'ENTRADA_COMPRA';
    case SALIDA_VENTA = 'SALIDA_VENTA';
    case AJUSTE_POSITIVO = 'AJUSTE_POSITIVO';
    case AJUSTE_NEGATIVO = 'AJUSTE_NEGATIVO';
    case DEVOLUCION_CLIENTE = 'DEVOLUCION_CLIENTE';
    case DEVOLUCION_PROVEEDOR = 'DEVOLUCION_PROVEEDOR';
    case TRASLADO_SALIDA = 'TRASLADO_SALIDA';
    case TRASLADO_ENTRADA = 'TRASLADO_ENTRADA';

    public function label(): string
    {
        return match ($this) {
            self::ENTRADA_COMPRA => 'Entrada por Compra',
            self::SALIDA_VENTA => 'Salida por Venta',
            self::AJUSTE_POSITIVO => 'Ajuste (+) Sobrante',
            self::AJUSTE_NEGATIVO => 'Ajuste (-) Merma / Daño',
            self::DEVOLUCION_CLIENTE => 'Devolución de Cliente',
            self::DEVOLUCION_PROVEEDOR => 'Devolución a Proveedor',
            self::TRASLADO_SALIDA => 'Traslado (Salida)',
            self::TRASLADO_ENTRADA => 'Traslado (Entrada)',
        };
    }

    /**
     * Determina si el tipo de movimiento incrementa las existencias físicas.
     */
    public function esEntrada(): bool
    {
        return match ($this) {
            self::ENTRADA_COMPRA,
            self::AJUSTE_POSITIVO,
            self::DEVOLUCION_CLIENTE,
            self::TRASLADO_ENTRADA => true,
            default => false,
        };
    }

    /**
     * Determina si el tipo de movimiento decrementa las existencias físicas.
     */
    public function esSalida(): bool
    {
        return ! $this->esEntrada();
    }

    /**
     * Clases Tailwind para badges en interfaces responsivas.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ENTRADA_COMPRA, self::DEVOLUCION_CLIENTE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::AJUSTE_POSITIVO => 'bg-teal-50 text-teal-700 border-teal-200',
            self::SALIDA_VENTA => 'bg-rose-50 text-rose-700 border-rose-200',
            self::AJUSTE_NEGATIVO, self::DEVOLUCION_PROVEEDOR => 'bg-amber-50 text-amber-700 border-amber-200',
            self::TRASLADO_SALIDA, self::TRASLADO_ENTRADA => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }
}
