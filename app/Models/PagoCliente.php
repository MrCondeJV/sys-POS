<?php

namespace App\Models;

use App\Enums\EstadoPagoCartera;
use App\Enums\MetodoPagoCartera;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoCliente extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'pagos_clientes';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'cuenta_por_cobrar_id',
        'cliente_id',
        'numero_recibo',
        'monto',
        'metodo_pago',
        'referencia_pago',
        'fecha_pago',
        'saldo_anterior',
        'saldo_posterior',
        'notas',
        'user_id',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_posterior' => 'decimal:2',
        'fecha_pago' => 'date',
        'metodo_pago' => MetodoPagoCartera::class,
        'estado' => EstadoPagoCartera::class,
    ];

    /**
     * Cuenta por cobrar a la que se aplicó el abono.
     */
    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_por_cobrar_id');
    }

    /**
     * Cliente titular de la obligación y del pago.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Sucursal receptora del pago.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Usuario/Cajero que recibió y registró el abono.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para pagos válidos y aplicados (no anulados).
     */
    public function scopeAplicados(Builder $query): Builder
    {
        return $query->where('estado', EstadoPagoCartera::APLICADO->value);
    }

    /**
     * Determina si el pago ha sido anulado.
     */
    public function isAnulado(): bool
    {
        return $this->estado === EstadoPagoCartera::ANULADO;
    }
}
