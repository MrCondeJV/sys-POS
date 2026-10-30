<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionDetalle extends Model
{
    use BelongsToCompany;

    protected $table = 'cotizacion_detalles';

    protected $fillable = [
        'empresa_id',
        'cotizacion_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento',
        'impuesto_porcentaje',
        'impuesto_monto',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }
}
