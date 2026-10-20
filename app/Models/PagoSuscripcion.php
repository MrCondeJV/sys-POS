<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoSuscripcion extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'pagos_suscripcion';

    protected $fillable = [
        'empresa_id',
        'suscripcion_id',
        'plan_id',
        'user_id',
        'ciclo_facturacion',
        'reference',
        'amount_in_cents',
        'currency',
        'integrity_signature',
        'wompi_transaction_id',
        'status',
        'payment_method_type',
        'status_message',
        'paid_at',
        'expires_at',
        'raw_response',
    ];

    protected $casts = [
        'amount_in_cents' => 'integer',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function suscripcion(): BelongsTo
    {
        return $this->belongsTo(Suscripcion::class, 'suscripcion_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
