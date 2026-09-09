<?php

namespace App\Support\Tenancy;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Inicializar el trait BelongsToCompany.
     */
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (empty($model->empresa_id) && CompanyContext::check()) {
                $model->empresa_id = CompanyContext::getId();
            }
        });
    }

    /**
     * Relación con la empresa propietaria del registro.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
