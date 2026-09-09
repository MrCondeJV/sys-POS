<?php

namespace App\Enums;

enum EstadoSuscripcion: string
{
    case ACTIVA = 'ACTIVA';
    case PRUEBA = 'PRUEBA';
    case SUSPENDIDA = 'SUSPENDIDA';
    case CANCELADA = 'CANCELADA';
    case VENCIDA = 'VENCIDA';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA => 'Suscripción Activa',
            self::PRUEBA => 'Período de Prueba',
            self::SUSPENDIDA => 'Suspendida por Pago',
            self::CANCELADA => 'Cancelada',
            self::VENCIDA => 'Vencida',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVA => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::PRUEBA => 'bg-sky-100 text-sky-800 border-sky-300',
            self::SUSPENDIDA => 'bg-amber-100 text-amber-800 border-amber-300',
            self::CANCELADA => 'bg-gray-100 text-gray-800 border-gray-300',
            self::VENCIDA => 'bg-rose-100 text-rose-800 border-rose-300',
        };
    }

    public function isOperativa(): bool
    {
        return in_array($this, [self::ACTIVA, self::PRUEBA]);
    }
}
