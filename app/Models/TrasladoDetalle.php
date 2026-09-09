<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrasladoDetalle extends Model
{
    use HasFactory;

    protected $table = 'traslado_detalles';

    protected $fillable = [
        'traslado_sucursal_id',
        'producto_id',
        'lote_id',
        'cantidad_enviada',
        'cantidad_recibida',
        'observaciones',
    ];

    protected $casts = [
        'cantidad_enviada' => 'decimal:3',
        'cantidad_recibida' => 'decimal:3',
    ];

    public function traslado(): BelongsTo
    {
        return $this->belongsTo(TrasladoSucursal::class, 'traslado_sucursal_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(ProductoLote::class, 'lote_id');
    }
}
