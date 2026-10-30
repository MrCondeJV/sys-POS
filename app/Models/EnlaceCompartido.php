<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Enlace corto y temporal al PDF de una venta o cotización, para compartir por WhatsApp Web.
 * Sin scope de empresa: se resuelve desde una ruta pública solo por su token.
 */
class EnlaceCompartido extends Model
{
    /** Días de vigencia de un enlace nuevo. */
    public const DIAS_VIGENCIA = 15;

    /** Si al enlace vigente le quedan menos días que esto, se emite uno nuevo para no compartir uno a punto de vencer. */
    private const DIAS_MINIMOS_RESTANTES = 3;

    protected $table = 'enlaces_compartidos';

    protected $fillable = [
        'empresa_id',
        'token',
        'tipo',
        'documento_id',
        'expira_en',
        'visitas',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
    ];

    public static function paraDocumento(Venta|Cotizacion $documento): self
    {
        $tipo = $documento instanceof Venta ? 'venta' : 'cotizacion';

        $vigente = static::where('tipo', $tipo)
            ->where('documento_id', $documento->id)
            ->where('expira_en', '>', now()->addDays(self::DIAS_MINIMOS_RESTANTES))
            ->latest('expira_en')
            ->first();

        return $vigente ?? static::create([
            'empresa_id' => $documento->empresa_id,
            'token' => Str::random(12),
            'tipo' => $tipo,
            'documento_id' => $documento->id,
            'expira_en' => now()->addDays(self::DIAS_VIGENCIA),
        ]);
    }

    public function estaVigente(): bool
    {
        return $this->expira_en->isFuture();
    }

    public function documento(): Venta|Cotizacion|null
    {
        return match ($this->tipo) {
            'venta' => Venta::withoutGlobalScopes()->find($this->documento_id),
            'cotizacion' => Cotizacion::withoutGlobalScopes()->find($this->documento_id),
            default => null,
        };
    }
}
