<?php

namespace App\Models;

use App\Enums\EstadoSesionCaja;
use App\Enums\TipoMovimientoCaja;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CajaSesion extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'cajas_sesiones';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'caja_id',
        'user_id',
        'user_cierre_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre_esperado',
        'monto_cierre_contado',
        'diferencia',
        'total_ingresos',
        'total_egresos',
        'total_ventas_efectivo',
        'total_ventas_electronico',
        'estado',
        'observaciones_apertura',
        'observaciones_cierre',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_apertura' => 'decimal:2',
        'monto_cierre_esperado' => 'decimal:2',
        'monto_cierre_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'total_ingresos' => 'decimal:2',
        'total_egresos' => 'decimal:2',
        'total_ventas_efectivo' => 'decimal:2',
        'total_ventas_electronico' => 'decimal:2',
        'estado' => EstadoSesionCaja::class,
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajeroCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_cierre_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_sesion_id');
    }

    public function estaAbierta(): bool
    {
        return $this->estado === EstadoSesionCaja::ABIERTA;
    }

    /**
     * Calcula en tiempo real el dinero físico esperado en caja.
     * Saldo esperado = monto inicial + ingresos en efectivo - egresos en efectivo
     */
    public function calcularSaldoEsperadoEfectivo(): float
    {
        $ingresosEfectivo = (float) $this->movimientos()
            ->where('tipo', TipoMovimientoCaja::INGRESO->value)
            ->where('metodo_pago', 'EFECTIVO')
            ->sum('monto');

        $egresosEfectivo = (float) $this->movimientos()
            ->where('tipo', TipoMovimientoCaja::EGRESO->value)
            ->where('metodo_pago', 'EFECTIVO')
            ->sum('monto');

        return (float) $this->monto_apertura + $ingresosEfectivo - $egresosEfectivo;
    }

    /**
     * Total acumulado de ingresos (todos los medios).
     */
    public function recalcularTotales(): void
    {
        $this->total_ingresos = (float) $this->movimientos()
            ->where('tipo', TipoMovimientoCaja::INGRESO->value)
            ->sum('monto');

        $this->total_egresos = (float) $this->movimientos()
            ->where('tipo', TipoMovimientoCaja::EGRESO->value)
            ->sum('monto');

        $this->save();
    }
}
