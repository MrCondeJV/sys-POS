<?php

namespace App\Enums;

enum EstadoSuscripcion: string
{
    case ACTIVA = 'ACTIVA';
    case PRUEBA = 'PRUEBA';
    case EN_GRACIA = 'EN_GRACIA';
    case SUSPENDIDA = 'SUSPENDIDA';
    case CANCELADA = 'CANCELADA';
    case VENCIDA = 'VENCIDA';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA => 'Suscripción Activa',
            self::PRUEBA => 'Período de Prueba',
            self::EN_GRACIA => 'Período de Gracia',
            self::SUSPENDIDA => 'Suspendida por Pago',
            self::CANCELADA => 'Cancelada',
            self::VENCIDA => 'Vencida',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVA => 'bg-success-bg text-success-text border-success-border',
            self::PRUEBA => 'bg-brand-50 text-brand-700 border-brand-200',
            self::EN_GRACIA => 'bg-warning-bg text-warning-text border-warning-border',
            self::SUSPENDIDA => 'bg-warning-bg text-warning-text border-warning-border',
            self::CANCELADA => 'bg-surface-muted text-text-muted border-border-subtle',
            self::VENCIDA => 'bg-danger-bg text-danger-text border-danger-border',
        };
    }

    public function isOperativa(): bool
    {
        return in_array($this, [self::ACTIVA, self::PRUEBA, self::EN_GRACIA]);
    }
}
