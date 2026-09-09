<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'marcas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Productos asociados a la marca.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }

    /**
     * Scope para marcas activas.
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
