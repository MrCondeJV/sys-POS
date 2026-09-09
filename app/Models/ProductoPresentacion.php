<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoPresentacion extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'producto_presentaciones';

    protected $fillable = [
        'empresa_id',
        'producto_id',
        'unidad_medida_id',
        'nombre',
        'factor_conversion',
        'codigo_barras',
        'precio_venta',
        'es_predeterminada',
        'estado',
    ];

    protected $casts = [
        'factor_conversion' => 'decimal:4',
        'precio_venta' => 'decimal:2',
        'es_predeterminada' => 'boolean',
        'estado' => EstadoGeneral::class,
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    /**
     * Calcula la cantidad base equivalente a partir de una cantidad de esta presentación.
     * Ejemplo: 2 cajas con factor 100 = 200 unidades base.
     */
    public function calcularCantidadBase(float $cantidad): float
    {
        return (float) ($cantidad * (float) $this->factor_conversion);
    }

    /**
     * Calcula cuántas unidades de esta presentación se pueden formar con el stock base disponible.
     * Ejemplo: 250 tornillos base / factor 100 = 2.5 cajas.
     */
    public function calcularStockPresentacion(float $stockBase): float
    {
        if ((float) $this->factor_conversion <= 0) {
            return 0.0;
        }

        return (float) ($stockBase / (float) $this->factor_conversion);
    }
}
