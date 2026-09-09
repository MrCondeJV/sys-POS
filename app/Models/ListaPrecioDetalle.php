<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListaPrecioDetalle extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'lista_precio_detalles';

    protected $fillable = [
        'empresa_id',
        'lista_precio_id',
        'producto_id',
        'precio',
    ];

    protected $casts = [
        'precio' => 'float',
    ];

    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class, 'lista_precio_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
