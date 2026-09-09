<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionDetalle extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'devolucion_detalles';

    protected $fillable = [
        'empresa_id',
        'devolucion_id',
        'venta_detalle_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'impuesto_porcentaje',
        'impuesto_monto',
        'total',
        'reingresa_inventario',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'subtotal' => 'float',
        'impuesto_porcentaje' => 'float',
        'impuesto_monto' => 'float',
        'total' => 'float',
        'reingresa_inventario' => 'boolean',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    public function ventaDetalle(): BelongsTo
    {
        return $this->belongsTo(VentaDetalle::class, 'venta_detalle_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
