<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaDetalle extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'empresa_id',
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'costo_unitario',
        'descuento',
        'impuesto_porcentaje',
        'impuesto_monto',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
