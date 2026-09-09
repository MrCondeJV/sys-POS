<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Auditoria extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'auditorias';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'modulo',
        'accion',
        'auditable_type',
        'auditable_id',
        'datos_anteriores',
        'datos_nuevos',
        'descripcion',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePorModulo(Builder $query, ?string $modulo): Builder
    {
        if ($modulo) {
            $query->where('modulo', $modulo);
        }
        return $query;
    }

    public function scopePorAccion(Builder $query, ?string $accion): Builder
    {
        if ($accion) {
            $query->where('accion', $accion);
        }
        return $query;
    }

    public function scopePorUsuario(Builder $query, ?int $userId): Builder
    {
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopeEnRangoFechas(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }
        return $query;
    }

    public function badgeClasses(): string
    {
        return match (strtoupper($this->accion)) {
            'ANULAR_VENTA', 'ELIMINAR' => 'bg-rose-50 text-rose-700 border-rose-200',
            'DEVOLUCION' => 'bg-amber-50 text-amber-700 border-amber-200',
            'APERTURA_CAJA', 'CREAR' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'CIERRE_CAJA' => 'bg-slate-100 text-slate-700 border-slate-200',
            'AJUSTE_INVENTARIO', 'CAMBIO_PRECIO' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }
}
