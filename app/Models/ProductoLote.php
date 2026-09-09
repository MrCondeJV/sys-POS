<?php

namespace App\Models;

use App\Enums\EstadoLote;
use App\Support\Tenancy\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoLote extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'producto_lotes';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'producto_id',
        'numero_lote',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'stock_inicial',
        'stock_actual',
        'costo_unitario',
        'estado',
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
        'stock_inicial' => 'decimal:4',
        'stock_actual' => 'decimal:4',
        'costo_unitario' => 'decimal:2',
        'estado' => EstadoLote::class,
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('stock_actual', '>', 0)
            ->where('fecha_vencimiento', '>=', now()->toDateString());
    }

    public function scopeProximosAVencer(Builder $query, int $dias = 30): Builder
    {
        $hoy = now()->toDateString();
        $limite = now()->addDays($dias)->toDateString();

        return $query->where('stock_actual', '>', 0)
            ->whereBetween('fecha_vencimiento', [$hoy, $limite]);
    }

    public function scopeVencidos(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString())
            ->where('stock_actual', '>', 0);
    }

    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento->isPast();
    }

    public function diasParaVencer(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
    }

    public function actualizarEstadoAutomatico(): void
    {
        if ($this->stock_actual <= 0) {
            $this->estado = EstadoLote::AGOTADO;
        } elseif ($this->estaVencido()) {
            $this->estado = EstadoLote::VENCIDO;
        } elseif ($this->diasParaVencer() <= 30) {
            $this->estado = EstadoLote::PROXIMO_VENCER;
        } else {
            $this->estado = EstadoLote::DISPONIBLE;
        }

        $this->save();
    }

    public function descontarStock(float $cantidad): void
    {
        if ($cantidad > (float) $this->stock_actual) {
            throw new \InvalidArgumentException("Stock insuficiente en el lote {$this->numero_lote}. Disponible: {$this->stock_actual}");
        }

        $this->stock_actual -= $cantidad;
        $this->actualizarEstadoAutomatico();
    }
}
