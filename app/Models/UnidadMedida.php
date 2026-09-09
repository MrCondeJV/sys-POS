<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadMedida extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Productos asociados a la unidad de medida.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id');
    }

    /**
     * Scope para unidades activas.
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
