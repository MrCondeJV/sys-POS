<?php

namespace App\Exceptions;

use Exception;

class TenancyViolationException extends Exception
{
    protected $message = 'Violación de seguridad multiempresa: intento de acceso o modificación a recursos fuera del tenant autorizado.';
}
