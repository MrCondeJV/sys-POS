<?php

namespace App\Models;

use App\Enums\EstadoTraslado;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrasladoSucursal extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'traslados_sucursal';

    protected $fillable = [
        'empresa_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'consecutivo',
        'user_id',
        'user_receptor_id',
        'estado',
        'motivo',
        'observaciones',
        'fecha_envio',
        'fecha_recepcion',
    ];

    protected $casts = [
        'estado' => EstadoTraslado::class,
        'fecha_envio' => 'datetime',
        'fecha_recepcion' => 'datetime',
    ];

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function usuarioDespacho(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioReceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_receptor_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class, 'traslado_sucursal_id');
    }

    public function isEnTransito(): bool
    {
        return $this->estado === EstadoTraslado::EN_TRANSITO;
    }

    public function isRecibido(): bool
    {
        return $this->estado === EstadoTraslado::RECIBIDO;
    }

    public function isRechazado(): bool
    {
        return $this->estado === EstadoTraslado::RECHAZADO;
    }
}
