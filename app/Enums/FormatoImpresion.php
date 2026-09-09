<?php

namespace App\Enums;

enum FormatoImpresion: string
{
    case TICKET_58MM = '58mm';
    case TICKET_80MM = '80mm';
    case CARTA_PDF = 'carta';
    case MEDIA_CARTA = 'media_carta';

    public function label(): string
    {
        return match ($this) {
            self::TICKET_58MM => 'Ticket Térmico (58 mm)',
            self::TICKET_80MM => 'Ticket Térmico (80 mm)',
            self::CARTA_PDF => 'Formato Carta / PDF Estándar',
            self::MEDIA_CARTA => 'Media Carta Comercial',
        };
    }

    public function anchoMm(): int
    {
        return match ($this) {
            self::TICKET_58MM => 58,
            self::TICKET_80MM => 80,
            self::CARTA_PDF => 216,
            self::MEDIA_CARTA => 140,
        };
    }
}
