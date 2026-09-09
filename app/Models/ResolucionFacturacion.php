<?php

namespace App\Models;

use App\Enums\EstadoResolucionFacturacion;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResolucionFacturacion extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'resoluciones_facturacion';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'numero_resolucion',
        'prefijo',
        'rango_desde',
        'rango_hasta',
        'consecutivo_actual',
        'fecha_inicio',
        'fecha_vigencia',
        'clave_tecnica',
        'estado',
        'es_predeterminada',
    ];

    protected $casts = [
        'rango_desde' => 'integer',
        'rango_hasta' => 'integer',
        'consecutivo_actual' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_vigencia' => 'date',
        'estado' => EstadoResolucionFacturacion::class,
        'es_predeterminada' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function documentosElectronicos(): HasMany
    {
        return $this->hasMany(DocumentoElectronico::class, 'resolucion_id');
    }

    public function estaVigente(): bool
    {
        return $this->estado === EstadoResolucionFacturacion::ACTIVA
            && now()->toDateString() >= $this->fecha_inicio->toDateString()
            && now()->toDateString() <= $this->fecha_vigencia->toDateString()
            && $this->consecutivo_actual <= $this->rango_hasta;
    }

    public function obtenerSiguienteConsecutivo(): int
    {
        $siguiente = $this->consecutivo_actual == 0 ? $this->rango_desde : $this->consecutivo_actual + 1;

        if ($siguiente > $this->rango_hasta) {
            $this->update(['estado' => EstadoResolucionFacturacion::AGOTADA]);
            throw new \RuntimeException("El rango de la resolución {$this->numero_resolucion} ({$this->prefijo}) ha sido agotado.");
        }

        $this->update(['consecutivo_actual' => $siguiente]);

        return $siguiente;
    }
}
