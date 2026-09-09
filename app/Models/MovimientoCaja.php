<?php

namespace App\Models;

use App\Enums\TipoMovimientoCaja;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoCaja extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'caja_sesion_id',
        'user_id',
        'tipo',
        'concepto',
        'monto',
        'metodo_pago',
        'comprobante',
        'origen_type',
        'origen_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'tipo' => TipoMovimientoCaja::class,
    ];

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function origen(): MorphTo
    {
        return $this->morphTo();
    }
}
