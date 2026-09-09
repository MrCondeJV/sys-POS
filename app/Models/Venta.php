<?php

namespace App\Models;

use App\Enums\EstadoVenta;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'cliente_id',
        'lista_precio_id',
        'user_id',
        'caja_sesion_id',
        'numero_venta',
        'client_transaction_id',
        'sincronizada_offline',
        'tipo_comprobante',
        'fecha',
        'tipo_pago',
        'metodo_pago',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'total_devuelto',
        'tiene_devolucion',
        'pago_con',
        'cambio',
        'estado',
        'observaciones',
        'anulado_por_id',
        'fecha_anulacion',
        'motivo_anulacion',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'total_devuelto' => 'decimal:2',
        'tiene_devolucion' => 'boolean',
        'sincronizada_offline' => 'boolean',
        'pago_con' => 'decimal:2',
        'cambio' => 'decimal:2',
        'estado' => EstadoVenta::class,
        'tipo_pago' => TipoPago::class,
        'tipo_comprobante' => TipoComprobanteVenta::class,
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajaSesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(VentaPago::class, 'venta_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class, 'venta_id');
    }

    public function isCompletada(): bool
    {
        return $this->estado === EstadoVenta::COMPLETADA;
    }

    public function isAnulada(): bool
    {
        return $this->estado === EstadoVenta::ANULADA;
    }

    public function isCredito(): bool
    {
        return $this->tipo_pago === TipoPago::CREDITO;
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', EstadoVenta::COMPLETADA);
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estado', EstadoVenta::ANULADA);
    }

    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }

    public function documentoVenta(): HasOne
    {
        return $this->hasOne(DocumentoVenta::class, 'venta_id')->latestOfMany();
    }

    public function documentosVenta(): HasMany
    {
        return $this->hasMany(DocumentoVenta::class, 'venta_id');
    }
}
