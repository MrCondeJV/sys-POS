<?php

namespace App\Models;

use App\Enums\EstadoCotizacion;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'cliente_id',
        'lista_precio_id',
        'user_id',
        'numero_cotizacion',
        'fecha',
        'fecha_validez',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'estado',
        'observaciones',
        'venta_id',
        'fecha_conversion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_validez' => 'date',
        'fecha_conversion' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'estado' => EstadoCotizacion::class,
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id')->withTrashed();
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CotizacionDetalle::class, 'cotizacion_id');
    }

    public function isPendiente(): bool
    {
        return $this->estado === EstadoCotizacion::PENDIENTE;
    }

    /**
     * La validez es informativa: una cotización vencida se puede convertir igual, con aviso.
     */
    public function estaVencida(): bool
    {
        return $this->isPendiente()
            && $this->fecha_validez !== null
            && $this->fecha_validez->isPast()
            && ! $this->fecha_validez->isToday();
    }

    public function scopeBuscar(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('numero_cotizacion', 'like', "%{$term}%")
                ->orWhereHas('cliente', function (Builder $qc) use ($term) {
                    $qc->where('razon_social', 'like', "%{$term}%")
                        ->orWhere('numero_documento', 'like', "%{$term}%");
                });
        });
    }
}
