<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboratorio extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'laboratorios';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'telefono',
        'email',
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
        return $this->hasMany(Producto::class, 'laboratorio_id');
    }
}
