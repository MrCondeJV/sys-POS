<?php

namespace App\Models;

use App\Enums\EstadoCuentaCobrar;
use App\Support\Tenancy\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorCobrar extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'cliente_id',
        'numero_documento',
        'concepto',
        'monto_total',
        'monto_pagado',
        'saldo_pendiente',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'observaciones',
        'created_by',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'estado' => EstadoCuentaCobrar::class,
    ];

    /**
     * Cliente deudor de la cuenta por cobrar.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Sucursal emisora.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Usuario que registró la cuenta por cobrar.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Historial de abonos y pagos aplicados a la cuenta.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCliente::class, 'cuenta_por_cobrar_id')->latest();
    }

    /**
     * Scope para cuentas con saldo pendiente (PENDIENTE o PARCIAL).
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', [
            EstadoCuentaCobrar::PENDIENTE->value,
            EstadoCuentaCobrar::PARCIAL->value,
        ]);
    }

    /**
     * Scope para cuentas con fecha de pago acordada que ya pasó.
     * Las cuentas sin acuerdo de pago (sin fecha) nunca se consideran vencidas.
     */
    public function scopeVencidas(Builder $query): Builder
    {
        return $query->pendientes()
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now()->toDateString());
    }

    /**
     * Scope para cuentas totalmente canceladas.
     */
    public function scopePagadas(Builder $query): Builder
    {
        return $query->where('estado', EstadoCuentaCobrar::PAGADA->value);
    }

    /**
     * Scope de búsqueda por número de documento, concepto o datos del cliente.
     */
    public function scopeBuscar(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('numero_documento', 'like', "%{$term}%")
                ->orWhere('concepto', 'like', "%{$term}%")
                ->orWhereHas('cliente', function (Builder $qc) use ($term) {
                    $qc->where('razon_social', 'like', "%{$term}%")
                        ->orWhere('numero_documento', 'like', "%{$term}%")
                        ->orWhere('nombre_comercial', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Días transcurridos desde que se fió la venta (antigüedad de la deuda).
     * El crédito no maneja plazos de pago: es solo un dato informativo.
     */
    public function diasAntiguedad(): int
    {
        return (int) $this->fecha_emision->copy()->startOfDay()->diffInDays(Carbon::today());
    }

    /**
     * Indica si tiene una fecha de pago acordada y ya pasó. Es solo un aviso: no bloquea ventas ni abonos.
     */
    public function estaVencida(): bool
    {
        if (! $this->fecha_vencimiento) {
            return false;
        }

        if ($this->estado === EstadoCuentaCobrar::PAGADA || $this->estado === EstadoCuentaCobrar::ANULADA) {
            return false;
        }

        return $this->fecha_vencimiento->isPast() && ! $this->fecha_vencimiento->isToday();
    }

    /**
     * Porcentaje pagado de la deuda (0 a 100).
     */
    public function porcentajePagado(): float
    {
        if ((float) $this->monto_total <= 0) {
            return 100.0;
        }

        $pct = ((float) $this->monto_pagado / (float) $this->monto_total) * 100;

        return min(100.0, round($pct, 1));
    }
}
