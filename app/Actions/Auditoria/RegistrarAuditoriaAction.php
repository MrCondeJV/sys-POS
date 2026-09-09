<?php

namespace App\Actions\Auditoria;

use App\Models\Auditoria;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Database\Eloquent\Model;

class RegistrarAuditoriaAction
{
    /**
     * Registra de forma inmutable un evento de auditoría en la base de datos.
     */
    public static function execute(
        string $accion,
        string $modulo,
        ?Model $model = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $descripcion = null,
        ?User $usuario = null,
        ?int $empresaId = null
    ): Auditoria {
        $usuarioFinal = $usuario ?: auth()->user();
        $empresaIdFinal = $empresaId 
            ?: ($usuarioFinal?->empresa_id 
                ?: (CompanyContext::getId() ?: ($model?->empresa_id ?? null)));

        $ip = request()?->ip();
        $userAgent = request()?->userAgent();

        return Auditoria::create([
            'empresa_id' => $empresaIdFinal,
            'user_id' => $usuarioFinal?->id,
            'modulo' => strtoupper($modulo),
            'accion' => strtoupper($accion),
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->getKey(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'descripcion' => $descripcion,
            'ip' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
        ]);
    }
}
