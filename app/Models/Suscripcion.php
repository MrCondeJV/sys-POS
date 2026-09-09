<?php

namespace App\Models;

use App\Enums\EstadoSuscripcion;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suscripcion extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'suscripciones';

    protected $fillable = [
        'empresa_id',
        'plan_id',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'ciclo_facturacion',
        'precio_pago',
        'metodo_pago',
        'notas',
    ];

    protected $casts = [
        'estado' => EstadoSuscripcion::class,
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'precio_pago' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function isVigente(): bool
    {
        if (! $this->estado->isOperativa()) {
            return false;
        }

        if ($this->fecha_fin === null) {
            return true;
        }

        return $this->fecha_fin->isFuture();
    }
}
