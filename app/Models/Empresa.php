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
     * Usuarios pertenecientes a la empresa.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
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

    /**
     * Historial de suscripciones SaaS de la empresa.
     */
    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'empresa_id');
    }

    /**
     * Suscripción vigente actual de la empresa.
     */
    public function suscripcionActual(): HasOne
    {
        return $this->hasOne(Suscripcion::class, 'empresa_id')
            ->whereIn('estado', [\App\Enums\EstadoSuscripcion::ACTIVA->value, \App\Enums\EstadoSuscripcion::PRUEBA->value])
            ->latestOfMany();
    }

    /**
     * Plan comercial contratado actualmente.
     */
    public function obtenerPlan(): ?Plan
    {
        return $this->suscripcionActual?->plan;
    }

    /**
     * Verifica si la empresa puede crear una nueva sucursal según los límites de su plan.
     */
    public function puedeCrearSucursal(): bool
    {
        $plan = $this->obtenerPlan();
        if (! $plan) {
            return true; // Si no hay plan asignado expresamente, permitir por defecto
        }

        if ($plan->esIlimitadoSucursales()) {
            return true;
        }

        $sucursalesActuales = $this->sucursales()->count();

        return $sucursalesActuales < $plan->limite_sucursales;
    }

    /**
     * Verifica si la empresa puede registrar un nuevo usuario según su plan.
     */
    public function puedeCrearUsuario(): bool
    {
        $plan = $this->obtenerPlan();
        if (! $plan) {
            return true;
        }

        if ($plan->esIlimitadoUsuarios()) {
            return true;
        }

        $usuariosActuales = $this->users()->count();

        return $usuariosActuales < $plan->limite_usuarios;
    }

    /**
     * Verifica si el plan de la empresa autoriza facturación electrónica.
     */
    public function puedeUsarFacturacionElectronica(): bool
    {
        $plan = $this->obtenerPlan();
        if (! $plan) {
            return true;
        }

        return (bool) $plan->permite_facturacion_electronica;
    }

    /**
     * Verifica si el plan de la empresa autoriza acceso a la API REST.
     */
    public function puedeUsarApi(): bool
    {
        $plan = $this->obtenerPlan();
        if (! $plan) {
            return true;
        }

        return (bool) $plan->permite_api;
    }
}
