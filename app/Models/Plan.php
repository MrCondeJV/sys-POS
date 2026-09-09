<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio_mensual',
        'precio_anual',
        'limite_sucursales',
        'limite_usuarios',
        'permite_facturacion_electronica',
        'permite_api',
        'permite_farmacia',
        'permite_ferreteria',
        'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'precio_anual' => 'decimal:2',
        'limite_sucursales' => 'integer',
        'limite_usuarios' => 'integer',
        'permite_facturacion_electronica' => 'boolean',
        'permite_api' => 'boolean',
        'permite_farmacia' => 'boolean',
        'permite_ferreteria' => 'boolean',
        'activo' => 'boolean',
    ];

    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'plan_id');
    }

    public function esIlimitadoSucursales(): bool
    {
        return $this->limite_sucursales <= 0 || $this->limite_sucursales >= 9999;
    }

    public function esIlimitadoUsuarios(): bool
    {
        return $this->limite_usuarios <= 0 || $this->limite_usuarios >= 9999;
    }
}
