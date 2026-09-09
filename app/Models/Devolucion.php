<?php

namespace App\Models;

use App\Enums\EstadoDevolucion;
use App\Enums\TipoDevolucion;
use App\Enums\TipoReintegroDevolucion;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucion extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'devoluciones';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'venta_id',
        'user_id',
        'caja_sesion_id',
        'numero_devolucion',
        'tipo_devolucion',
        'tipo_reintegro',
        'subtotal',
        'impuesto',
        'total',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'tipo_devolucion' => TipoDevolucion::class,
        'tipo_reintegro' => TipoReintegroDevolucion::class,
        'estado' => EstadoDevolucion::class,
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajaSesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DevolucionDetalle::class, 'devolucion_id');
    }
}
