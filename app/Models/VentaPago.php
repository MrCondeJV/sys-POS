<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentaPago extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'venta_pagos';

    protected $fillable = [
        'empresa_id',
        'venta_id',
        'metodo_pago',
        'monto',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
