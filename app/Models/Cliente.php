<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'empresa_id',
        'lista_precio_id',
        'tipo_persona',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'telefono',
        'email',
        'direccion',
        'ciudad',
        'departamento',
        'cupo_credito',
        'plazo_dias',
        'estado',
        'es_predeterminado',
    ];

    protected $casts = [
        'tipo_persona' => TipoPersona::class,
        'tipo_documento' => TipoDocumentoIdentidad::class,
        'estado' => EstadoGeneral::class,
        'cupo_credito' => 'decimal:2',
        'plazo_dias' => 'integer',
        'es_predeterminado' => 'boolean',
    ];

    /**
     * Empresa propietaria del cliente.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Scope para filtrar únicamente clientes activos.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::ACTIVO->value);
    }

    /**
     * Scope de búsqueda general por documento, nombre, teléfono o email.
     */
    public function scopeBuscar(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('razon_social', 'like', "%{$term}%")
                ->orWhere('nombre_comercial', 'like', "%{$term}%")
                ->orWhere('numero_documento', 'like', "%{$term}%")
                ->orWhere('telefono', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Determina si el cliente es el Consumidor Final predeterminado.
     */
    public function isConsumidorFinal(): bool
    {
        return (bool) $this->es_predeterminado;
    }

    /**
     * Determina si el cliente puede ser eliminado.
     * El consumidor final nunca puede ser eliminado.
     */
    public function puedeEliminarse(): bool
    {
        return ! $this->isConsumidorFinal();
    }

    /**
     * Determina si el cliente tiene línea de crédito comercial aprobada.
     */
    public function tieneCredito(): bool
    {
        return (float) $this->cupo_credito > 0;
    }

    /**
     * Cuentas por cobrar del cliente.
     */
    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'cliente_id')->latest();
    }

    /**
     * Historial de pagos realizados por el cliente.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCliente::class, 'cliente_id')->latest();
    }

    /**
     * Saldo total adeudado actualmente por el cliente.
     */
    public function saldoTotalPendiente(): float
    {
        return (float) $this->cuentasPorCobrar()->pendientes()->sum('saldo_pendiente');
    }

    /**
     * Cupo de crédito restante disponible.
     */
    public function cupoDisponible(): float
    {
        $cupo = (float) $this->cupo_credito;
        $saldo = $this->saldoTotalPendiente();

        return max(0.0, $cupo - $saldo);
    }

    /**
     * Determina si el cliente tiene facturas vencidas en mora.
     */
    public function tieneMora(): bool
    {
        return $this->cuentasPorCobrar()->vencidas()->exists();
    }

    /**
     * Lista de precios asignada al cliente.
     */
    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }
}
