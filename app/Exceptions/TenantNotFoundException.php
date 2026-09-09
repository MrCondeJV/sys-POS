<?php

namespace App\Exceptions;

use Exception;

class TenantNotFoundException extends Exception
{
    protected $message = 'No se ha establecido o encontrado el contexto de la empresa.';
}
