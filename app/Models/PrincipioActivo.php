<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrincipioActivo extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'principios_activos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'concentracion',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => EstadoGeneral::class,
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'principio_activo_id');
    }
}
