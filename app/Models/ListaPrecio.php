<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\TipoAjusteListaPrecio;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListaPrecio extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'listas_precios';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'descripcion',
        'es_predeterminada',
        'tipo_ajuste',
        'porcentaje_defecto',
        'estado',
    ];

    protected $casts = [
        'es_predeterminada' => 'boolean',
        'tipo_ajuste' => TipoAjusteListaPrecio::class,
        'porcentaje_defecto' => 'float',
        'estado' => EstadoGeneral::class,
    ];

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ListaPrecioDetalle::class, 'lista_precio_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'lista_precio_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'lista_precio_id');
    }

    /**
     * Calcula el precio final de venta de un producto bajo las reglas de esta lista.
     */
    public function calcularPrecio(Producto $producto): float
    {
        // 1. Revisar si tiene precio específico en los detalles de la lista
        $detalle = $this->relationLoaded('detalles')
            ? $this->detalles->firstWhere('producto_id', $producto->id)
            : $this->detalles()->where('producto_id', $producto->id)->first();

        if ($detalle !== null) {
            return (float) $detalle->precio;
        }

        $base = (float) $producto->precio_venta;

        // 2. Si el tipo de ajuste es porcentual sobre el precio base
        if ($this->tipo_ajuste === TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO && $this->porcentaje_defecto > 0) {
            return max(0, round($base * (1 - ($this->porcentaje_defecto / 100)), 2));
        }

        if ($this->tipo_ajuste === TipoAjusteListaPrecio::PORCENTAJE_AUMENTO && $this->porcentaje_defecto > 0) {
            return round($base * (1 + ($this->porcentaje_defecto / 100)), 2);
        }

        return $base;
    }
}
