<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Support\Tenancy\BelongsToCompany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToCompany, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'empresa_id',
        'sucursal_id',
        'telefono',
        'cargo',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => EstadoGeneral::class,
        ];
    }

    /**
     * Sucursal a la cual está asignado el usuario.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Scope para filtrar usuarios activos.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Verifica si el usuario tiene el rol de Super Administrador.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RolSistema::SUPER_ADMIN->value);
    }

    /**
     * Verifica si el usuario es administrador de su empresa.
     */
    public function isAdminEmpresa(): bool
    {
        return $this->hasRole(RolSistema::ADMIN_EMPRESA->value);
    }

    /**
     * Verifica si el usuario se encuentra activo.
     */
    public function isActivo(): bool
    {
        return $this->estado === EstadoGeneral::ACTIVO;
    }
    public function sucursales(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user', 'user_id', 'sucursal_id')->withTimestamps();
    }

    /**
     * Verifica si el usuario tiene autorización para operar en una sucursal dada.
     */
    public function tieneAccesoASucursal(int $sucursalId): bool
    {
        if ($this->isSuperAdmin() || $this->isAdminEmpresa()) {
            return true;
        }

        if ($this->sucursal_id === $sucursalId) {
            return true;
        }

        return $this->sucursales()->where('sucursales.id', $sucursalId)->exists();
    }
}
