<?php

namespace App\Enums;

enum NivelNotificacion: string
{
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case DANGER = 'DANGER';
    case SUCCESS = 'SUCCESS';

    public function badgeClasses(): string
    {
        return match ($this) {
            self::INFO => 'bg-sky-50 text-sky-700 border-sky-200',
            self::WARNING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::DANGER => 'bg-red-50 text-red-700 border-red-200',
            self::SUCCESS => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    public function borderClasses(): string
    {
        return match ($this) {
            self::INFO => 'border-l-4 border-sky-500',
            self::WARNING => 'border-l-4 border-amber-500',
            self::DANGER => 'border-l-4 border-red-500',
            self::SUCCESS => 'border-l-4 border-emerald-500',
        };
    }
}
