<?php

namespace App\Enums;

enum EstadoTraslado: string
{
    case PENDIENTE = 'PENDIENTE';
    case EN_TRANSITO = 'EN_TRANSITO';
    case RECIBIDO = 'RECIBIDO';
    case RECHAZADO = 'RECHAZADO';
    case CANCELADO = 'CANCELADO';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente por Despachar',
            self::EN_TRANSITO => 'En Tránsito',
            self::RECIBIDO => 'Recibido en Destino',
            self::RECHAZADO => 'Rechazado / Reversado',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDIENTE => 'bg-amber-100 text-amber-800 border-amber-300',
            self::EN_TRANSITO => 'bg-blue-100 text-blue-800 border-blue-300',
            self::RECIBIDO => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::RECHAZADO => 'bg-rose-100 text-rose-800 border-rose-300',
            self::CANCELADO => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }
}
