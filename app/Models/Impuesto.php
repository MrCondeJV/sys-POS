<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\TipoImpuesto;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Impuesto extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'impuestos';

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'tipo',
        'porcentaje',
        'es_retencion',
        'por_defecto',
        'estado',
        'descripcion',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'es_retencion' => 'boolean',
        'por_defecto' => 'boolean',
        'estado' => EstadoGeneral::class,
        'tipo' => TipoImpuesto::class,
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'impuesto_id');
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO);
    }

    public function scopePorDefecto(Builder $query): Builder
    {
        return $query->where('por_defecto', true);
    }

    /**
     * Calcula el monto de impuesto aplicado a un subtotal base.
     */
    public function calcularMonto(float $base): float
    {
        if ($this->tipo === TipoImpuesto::EXCLUIDO || $this->tipo === TipoImpuesto::EXENTO) {
            return 0.0;
        }

        return round($base * ((float) $this->porcentaje / 100), 2);
    }
}
