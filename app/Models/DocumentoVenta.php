<?php

namespace App\Models;

use App\Enums\EstadoDocumentoVenta;
use App\Enums\TipoDocumentoVenta;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoVenta extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'documentos_venta';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'venta_id',
        'cliente_id',
        'user_id',
        'tipo',
        'estado',
        'prefijo',
        'numero',
        'numero_completo',
        'fecha_emision',
        'subtotal',
        'impuesto_total',
        'descuento_total',
        'total',
        'observaciones',
        'metadatos',
    ];

    protected $casts = [
        'tipo' => TipoDocumentoVenta::class,
        'estado' => EstadoDocumentoVenta::class,
        'fecha_emision' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuesto_total' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'total' => 'decimal:2',
        'metadatos' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function esEmitido(): bool
    {
        return $this->estado === EstadoDocumentoVenta::EMITIDO;
    }

    public function esAnulado(): bool
    {
        return $this->estado === EstadoDocumentoVenta::ANULADO;
    }

    public function esBorrador(): bool
    {
        return $this->estado === EstadoDocumentoVenta::BORRADOR;
    }

    public function scopeEmitidos(Builder $query): Builder
    {
        return $query->where('estado', EstadoDocumentoVenta::EMITIDO);
    }

    public function scopeFacturas(Builder $query): Builder
    {
        return $query->where('tipo', TipoDocumentoVenta::FACTURA);
    }

    public function scopeTickets(Builder $query): Builder
    {
        return $query->where('tipo', TipoDocumentoVenta::TICKET);
    }
}
