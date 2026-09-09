<?php

namespace App\Exceptions;

use Exception;

class StockInsuficienteException extends Exception
{
    public function __construct(
        public readonly string $productoNombre,
        public readonly float $stockActual,
        public readonly float $cantidadRequerida,
        string $message = ''
    ) {
        $msg = $message ?: "Stock insuficiente para '{$this->productoNombre}'. Disponible: {$this->stockActual}, Solicitado: {$this->cantidadRequerida}.";
        parent::__construct($msg);
    }
}
