<?php

namespace App\Models;

use App\Enums\NivelNotificacion;
use App\Enums\TipoNotificacion;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionSistema extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'notificaciones_sistema';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'nivel',
        'titulo',
        'mensaje',
        'url_accion',
        'leida',
        'leida_at',
        'datos',
    ];

    protected $casts = [
        'tipo' => TipoNotificacion::class,
        'nivel' => NivelNotificacion::class,
        'leida' => 'boolean',
        'leida_at' => 'datetime',
        'datos' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeNoLeidas(Builder $query): Builder
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas(Builder $query): Builder
    {
        return $query->where('leida', true);
    }

    public function marcarComoLeida(): bool
    {
        return $this->update([
            'leida' => true,
            'leida_at' => now(),
        ]);
    }
}
