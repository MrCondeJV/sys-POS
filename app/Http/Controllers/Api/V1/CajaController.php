<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Gate;
use App\Enums\PermisoSistema;
use App\Rules\BelongsToActiveCompany;
use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\CajaSesion;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CajaController extends BaseApiController
{
    public function estado(Request $request): JsonResponse
    {
        abort_unless($request->user()->can(PermisoSistema::CAJA_VER->value), 403);

        $user = $request->user();
        $sesionActiva = CajaSesion::where('user_id', $user->id)
            ->where('estado', EstadoSesionCaja::ABIERTA)
            ->with(['caja.sucursal'])
            ->first();

        return $this->successResponse([
            'caja_abierta' => $sesionActiva !== null,
            'sesion' => $sesionActiva,
        ]);
    }

    public function abrir(Request $request, AbrirCajaAction $action): JsonResponse
    {
        $validated = $request->validate([
            'caja_id' => ['required', 'exists:cajas,id', new BelongsToActiveCompany('cajas')],
            'monto_apertura' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $caja = Caja::findOrFail($validated['caja_id']);
        Gate::authorize('abrir', $caja);

        try {
            $sesion = $action->execute(
                caja: $caja,
                cajero: $request->user(),
                montoApertura: (float) $validated['monto_apertura'],
                observaciones: $validated['observaciones'] ?? null
            );

            return $this->successResponse($sesion, 'Caja aperturada exitosamente.', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function cerrar(Request $request, CerrarCajaAction $action): JsonResponse
    {
        $validated = $request->validate([
            'sesion_id' => ['required', 'exists:cajas_sesiones,id', new BelongsToActiveCompany('cajas_sesiones')],
            'monto_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $sesion = CajaSesion::findOrFail($validated['sesion_id']);
        Gate::authorize('cerrar', $sesion->caja);

        try {
            $sesionCerrada = $action->execute(
                sesion: $sesion,
                auditor: $request->user(),
                montoContado: (float) $validated['monto_contado'],
                observaciones: $validated['observaciones'] ?? null
            );

            return $this->successResponse($sesionCerrada, 'Turno de caja cerrado exitosamente.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function movimientos(Request $request, CajaSesion $sesion): JsonResponse
    {
        Gate::authorize('view', $sesion->caja);

        $this->authorizeCompanyResource($sesion);
        $movimientos = $sesion->movimientos()->with('usuario')->latest('id')->get();

        return $this->successResponse($movimientos);
    }
}
