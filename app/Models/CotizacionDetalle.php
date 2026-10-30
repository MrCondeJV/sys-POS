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
        'presentacion_id',
        'descripcion',
        'cantidad',
        'factor_conversion',
        'precio_unitario',
        'descuento',
        'impuesto_porcentaje',
        'impuesto_monto',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'factor_conversion' => 'decimal:4',
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

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(ProductoPresentacion::class, 'presentacion_id')->withTrashed();
    }

    /**
     * Unidad en que se cotizó la línea, ej. "Caja (1,44 M2)" o "M2".
     */
    public function unidadTexto(): string
    {
        $base = $this->producto?->unidadMedida?->codigo ?? '';
        if ($this->presentacion) {
            $factor = rtrim(rtrim(number_format((float) $this->factor_conversion, 4, ',', '.'), '0'), ',');

            return "{$this->presentacion->nombre} ({$factor} {$base})";
        }

        return $base;
    }

    public function cantidadTexto(): string
    {
        return rtrim(rtrim(number_format((float) $this->cantidad, 4, ',', '.'), '0'), ',');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }
}
