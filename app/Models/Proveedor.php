<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'empresa_id',
        'razon_social',
        'nombre_contacto',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'departamento',
        'estado',
    ];

    protected $casts = [
        'tipo_documento' => TipoDocumentoIdentidad::class,
        'estado' => EstadoGeneral::class,
    ];

    /**
     * Empresa a la que pertenece el proveedor.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Facturas de compra emitidas por este proveedor.
     */
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }

    /**
     * Scope para filtrar proveedores activos.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Scope para búsqueda por razón social, documento, contacto o email.
     */
    public function scopeBuscar(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('razon_social', 'like', "%{$term}%")
                ->orWhere('numero_documento', 'like', "%{$term}%")
                ->orWhere('nombre_contacto', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function isActivo(): bool
    {
        return $this->estado === EstadoGeneral::ACTIVO;
    }
}
