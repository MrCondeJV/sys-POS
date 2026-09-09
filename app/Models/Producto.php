<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'marca_id',
        'unidad_medida_id',
        'codigo',
        'codigo_barras',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'precio_mayorista',
        'precio_distribuidor',
        'stock',
        'stock_minimo',
        'iva',
        'impuesto_id',
        'laboratorio_id',
        'principio_activo_id',
        'registro_sanitario',
        'requiere_receta',
        'maneja_lotes',
        'imagen_path',
        'estado',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'maneja_lotes' => 'boolean',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'precio_mayorista' => 'decimal:2',
        'precio_distribuidor' => 'decimal:2',
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'iva' => 'decimal:2',
        'estado' => EstadoGeneral::class,
    ];

    protected $appends = [
        'sku',
        'imagen_url',
    ];

    public function getSkuAttribute(): ?string
    {
        return $this->codigo;
    }

    public function getPrecioCostoAttribute(): float
    {
        return (float) ($this->attributes['precio_compra'] ?? 0);
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class, 'impuesto_id');
    }

    public function getPorcentajeIvaAttribute(): float
    {
        if ($this->impuesto) {
            return (float) $this->impuesto->porcentaje;
        }

        return (float) ($this->attributes['iva'] ?? 0);
    }

    /**
     * Categoría a la que pertenece el producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Marca del producto.
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    /**
     * Unidad de medida comercial.
     */
    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    /**
     * Laboratorio farmacéutico fabricante.
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    /**
     * Principio activo o fármaco base.
     */
    public function principioActivo(): BelongsTo
    {
        return $this->belongsTo(PrincipioActivo::class, 'principio_activo_id');
    }

    /**
     * Lotes registrados del producto.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(ProductoLote::class, 'producto_id');
    }

    /**
     * Presentaciones comerciales y factores de conversión (cajas, metros, rollos, bultos).
     */
    public function presentaciones(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'producto_id');
    }

    /**
     * Registros de existencias por sucursal.
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'producto_id');
    }

    /**
     * Historial de movimientos de inventario (Kardex).
     */
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    /**
     * Líneas de facturas de compra donde ha sido adquirido este producto.
     */
    public function compraDetalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class, 'producto_id');
    }

    /**
     * Retorna las existencias del producto en una sucursal específica.
     */
    public function stockEnSucursal(int $sucursalId): float
    {
        $inv = $this->inventarios->firstWhere('sucursal_id', $sucursalId);

        return $inv ? (float) $inv->stock : 0.0;
    }

    /**
     * Scope para productos activos.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Scope para productos con existencias por debajo o igual al stock mínimo.
     */
    public function scopeBajoStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'stock_minimo');
    }

    /**
     * Scope para búsqueda rápida en el POS (nombre, código interno o código de barras).
     */
    public function scopeBuscar(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nombre', 'like', "%{$term}%")
                ->orWhere('codigo', 'like', "%{$term}%")
                ->orWhere('codigo_barras', 'like', "%{$term}%");
        });
    }

    /**
     * Verifica si el producto está en alerta de stock bajo.
     */
    public function tieneBajoStock(): bool
    {
        return (float) $this->stock <= (float) $this->stock_minimo;
    }

    /**
     * Calcula el precio de venta final con IVA incluido.
     */
    public function calcularPrecioConIva(): float
    {
        $factorIva = 1 + ((float) $this->iva / 100);

        return round((float) $this->precio_venta * $factorIva, 2);
    }

    /**
     * Retorna la URL pública de la imagen o null si no tiene.
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (! $this->imagen_path) {
            return null;
        }

        return asset('storage/'.ltrim($this->imagen_path, '/'));
    }
}
