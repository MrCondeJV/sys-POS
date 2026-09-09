<?php

namespace App\Models;

use App\Enums\TipoMovimientoInventario;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'sucursal_destino_id',
        'producto_id',
        'user_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'stock_anterior',
        'stock_posterior',
        'referencia',
        'notas',
    ];

    protected $casts = [
        'tipo' => TipoMovimientoInventario::class,
        'cantidad' => 'decimal:2',
        'costo_unitario' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_posterior' => 'decimal:2',
    ];

    /**
     * Empresa propietaria del movimiento de inventario.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Sucursal donde se produjo el movimiento.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Sucursal de destino (en caso de traslados entre sucursales).
     */
    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    /**
     * Producto afectado por el movimiento.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Usuario que registró o autorizó el movimiento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para filtrar movimientos de un producto específico.
     */
    public function scopePorProducto(Builder $query, int $productoId): Builder
    {
        return $query->where('producto_id', $productoId);
    }

    /**
     * Scope para filtrar movimientos de una sucursal específica.
     */
    public function scopePorSucursal(Builder $query, int $sucursalId): Builder
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope para filtrar movimientos por tipo.
     */
    public function scopePorTipo(Builder $query, TipoMovimientoInventario|string $tipo): Builder
    {
        $val = $tipo instanceof TipoMovimientoInventario ? $tipo->value : $tipo;

        return $query->where('tipo', $val);
    }

    /**
     * Scope para filtrar movimientos dentro de un rango de fechas.
     */
    public function scopeEnRango(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        return $query;
    }
}
