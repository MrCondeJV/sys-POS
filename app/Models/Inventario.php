<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'inventarios';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'producto_id',
        'stock',
        'stock_minimo',
        'ubicacion',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    /**
     * Empresa propietaria del registro de inventario.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Sucursal donde se almacenan las existencias físicas.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Producto catalogado al que corresponden las existencias.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Scope para filtrar existencias que están en nivel de alerta por bajo stock.
     */
    public function scopeBajoStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
    }

    /**
     * Scope para filtrar artículos con existencias agotadas (cero o menor).
     */
    public function scopeAgotado(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope para filtrar existencias por sucursal específica.
     */
    public function scopePorSucursal(Builder $query, int $sucursalId): Builder
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Determina si el stock en esta sucursal está en nivel de alerta.
     */
    public function tieneBajoStock(): bool
    {
        return (float) $this->stock <= (float) $this->stock_minimo;
    }

    /**
     * Determina si el artículo está agotado en esta sucursal.
     */
    public function estaAgotado(): bool
    {
        return (float) $this->stock <= 0;
    }
}
