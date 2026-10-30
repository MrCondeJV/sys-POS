<?php

namespace App\Support;

/**
 * Enlaces de WhatsApp Web (wa.me) sin API: abren el chat con un mensaje prellenado.
 * WhatsApp Web no permite adjuntar archivos por enlace, por eso el mensaje lleva la URL del PDF.
 */
class WhatsApp
{
    /**
     * Deja solo dígitos y agrega el indicativo de Colombia (57) a celulares locales de 10 dígitos.
     */
    public static function normalizarTelefono(?string $telefono): ?string
    {
        $digitos = preg_replace('/\D+/', '', (string) $telefono);

        if ($digitos === '') {
            return null;
        }

        if (strlen($digitos) === 10 && str_starts_with($digitos, '3')) {
            return '57'.$digitos;
        }

        return $digitos;
    }
}
