<?php

namespace App\Events;

use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockBajoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Producto $producto,
        public Sucursal $sucursal,
        public float $stockActual,
        public float $stockMinimo
    ) {}
}
