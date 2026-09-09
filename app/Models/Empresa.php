<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'nombre_comercial',
        'razon_social',
        'tipo_documento',
        'nit',
        'dv',
        'email',
        'telefono',
        'direccion',
        'ciudad',
        'departamento',
        'codigo_postal',
        'moneda',
        'simbolo_moneda',
        'logo_path',
        'configuraciones',
        'estado',
    ];

    protected $casts = [
        'configuraciones' => 'array',
        'estado' => EstadoGeneral::class,
        'tipo_documento' => TipoDocumentoIdentidad::class,
    ];

    /**
     * Sucursales pertenecientes a la empresa.
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class, 'empresa_id');
    }

    /**
     * Sucursal designada como sede principal.
     */
    public function sucursalPrincipal(): HasOne
    {
        return $this->hasOne(Sucursal::class, 'empresa_id')->where('es_principal', true);
    }

    /**
     * Scope para filtrar empresas activas.
     */
    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Verificar si la empresa está en estado activo.
     */
    public function isActiva(): bool
    {
        return $this->estado === EstadoGeneral::ACTIVO;
    }
}
