<?php

namespace App\Events;

use App\Models\CajaSesion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CajaCerradaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CajaSesion $sesion
    ) {}
}
