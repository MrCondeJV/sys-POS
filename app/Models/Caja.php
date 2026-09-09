<?php

namespace App\Models;

use App\Enums\EstadoCaja;
use App\Enums\EstadoSesionCaja;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'cajas';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'nombre',
        'codigo',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoCaja::class,
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(CajaSesion::class, 'caja_id');
    }

    /**
     * Retorna la sesión actualmente abierta de la caja, si existe.
     */
    public function sesionActual(): HasOne
    {
        return $this->hasOne(CajaSesion::class, 'caja_id')
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->latestOfMany();
    }

    /**
     * Verifica si la caja tiene un turno o sesión abierta en este momento.
     */
    public function estaAbierta(): bool
    {
        return $this->sesiones()
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->exists();
    }
}
