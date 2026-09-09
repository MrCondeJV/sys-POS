<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'sucursales';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'ciudad',
        'departamento',
        'es_principal',
        'estado',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'estado' => EstadoGeneral::class,
    ];

    /**
     * Scope para filtrar sucursales activas.
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Verificar si es la sede principal.
     */
    public function isPrincipal(): bool
    {
        return (bool) $this->es_principal;
    }
}
