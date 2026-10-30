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
        'cantidad_devuelta',
        'precio_unitario',
        'costo_unitario',
        'descuento',
        'impuesto_porcentaje',
        'impuesto_monto',
        'subtotal',
        'total',
        'lote_id',
        'presentacion_id',
        'factor_conversion',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'cantidad_devuelta' => 'decimal:4',
        'factor_conversion' => 'decimal:4',
        'precio_unitario' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(ProductoPresentacion::class, 'presentacion_id')->withTrashed();
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(ProductoLote::class, 'lote_id');
    }

    /**
     * Unidad en que se vendió la línea, ej. "Caja (1,44 M2)" o "M2".
     */
    public function unidadTexto(): string
    {
        $base = $this->producto?->unidadMedida?->codigo ?? 'UND';
        if ($this->presentacion_id && $this->presentacion) {
            $factor = rtrim(rtrim(number_format((float) $this->factor_conversion, 4, ',', '.'), '0'), ',');

            return "{$this->presentacion->nombre} ({$factor} {$base})";
        }

        return $base;
    }

    /**
     * Cantidad sin ceros sobrantes (2,35 · 2 · 0,5).
     */
    public function cantidadTexto(string $campo = 'cantidad'): string
    {
        return rtrim(rtrim(number_format((float) $this->{$campo}, 4, ',', '.'), '0'), ',');
    }

    public function cantidadPendienteDevolucion(): float
    {
        return max(0.0, (float) $this->cantidad - (float) $this->cantidad_devuelta);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
