<?php

namespace App\Rules;

use App\Support\Tenancy\CompanyContext;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class BelongsToActiveCompany implements ValidationRule
{
    /**
     * @param  string  $table  Nombre de la tabla de la base de datos
     * @param  string  $column  Nombre de la columna primaria (default: 'id')
     */
    public function __construct(
        protected string $table,
        protected string $column = 'id'
    ) {}

    /**
     * Valida que el recurso pertenezca a la empresa activa.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $activeCompanyId = CompanyContext::getId();

        if (! $activeCompanyId) {
            $fail('No hay un contexto de empresa activo para validar este registro.');

            return;
        }

        $exists = DB::table($this->table)
            ->where($this->column, $value)
            ->where('empresa_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->exists();

        if (! $exists) {
            $fail("El valor seleccionado en {$attribute} no existe o no pertenece a la empresa activa.");
        }
    }
}
