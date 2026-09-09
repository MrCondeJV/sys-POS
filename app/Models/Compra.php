<?php

namespace App\Models;

use App\Enums\EstadoCompra;
use App\Enums\TipoPago;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'proveedor_id',
        'user_id',
        'numero_factura',
        'fecha_emision',
        'subtotal',
        'impuestos',
        'descuento',
        'total',
        'tipo_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'subtotal' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'tipo_pago' => TipoPago::class,
        'estado' => EstadoCompra::class,
    ];

    /**
     * Empresa propietaria de la compra.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Sucursal que recibió los artículos comprados.
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Proveedor que emitió la factura.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Usuario que registró la compra en el sistema.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Líneas de productos de la compra.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }

    /**
     * Scope para compras activas / registradas.
     */
    public function scopeRegistrada(Builder $query): Builder
    {
        return $query->where('estado', EstadoCompra::REGISTRADA->value);
    }

    /**
     * Scope para compras de una sucursal específica.
     */
    public function scopePorSucursal(Builder $query, int $sucursalId): Builder
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope para compras de un proveedor específico.
     */
    public function scopePorProveedor(Builder $query, int $proveedorId): Builder
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    /**
     * Scope para compras dentro de un rango de fechas de emisión.
     */
    public function scopeEnRangoFechas(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        if ($desde) {
            $query->whereDate('fecha_emision', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha_emision', '<=', $hasta);
        }

        return $query;
    }

    public function isRegistrada(): bool
    {
        return $this->estado === EstadoCompra::REGISTRADA;
    }

    public function isAnulada(): bool
    {
        return $this->estado === EstadoCompra::ANULADA;
    }
}
