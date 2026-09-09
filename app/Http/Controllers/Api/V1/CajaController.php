<?php

namespace App\Http\Controllers\Api\V1;

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
            'caja_id' => ['required', 'exists:cajas,id'],
            'monto_apertura' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $caja = Caja::findOrFail($validated['caja_id']);

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
            'sesion_id' => ['required', 'exists:cajas_sesiones,id'],
            'monto_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        $sesion = CajaSesion::findOrFail($validated['sesion_id']);

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
        $this->authorizeCompanyResource($sesion);
        $movimientos = $sesion->movimientos()->with('usuario')->latest('id')->get();

        return $this->successResponse($movimientos);
    }
}
