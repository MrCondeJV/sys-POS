<?php

namespace App\Http\Controllers;

use App\Actions\Ventas\SincronizarVentasOfflineAction;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosOfflineSyncController extends Controller
{
    public function sync(Request $request, SincronizarVentasOfflineAction $action): JsonResponse
    {
        $validated = $request->validate([
            'ventas' => ['required', 'array', 'min:1'],
            'ventas.*.client_transaction_id' => ['required', 'string', 'max:100'],
            'ventas.*.items' => ['required', 'array', 'min:1'],
            'ventas.*.items.*.producto_id' => ['required', 'integer'],
            'ventas.*.items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'ventas.*.items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'ventas.*.cliente_id' => ['nullable', 'integer'],
            'ventas.*.metodo_pago' => ['nullable', 'string'],
            'ventas.*.tipo_pago' => ['nullable', 'string'],
            'ventas.*.pago_con' => ['nullable', 'numeric'],
            'ventas.*.observaciones' => ['nullable', 'string', 'max:500'],
            'sucursal_id' => ['nullable', 'integer'],
        ]);

        $user = auth()->user();
        $empresaId = CompanyContext::getId() ?? $user->empresa_id;
        $sucursalId = $request->input('sucursal_id')
            ?? BranchContext::getId()
            ?? $user->sucursal_id
            ?? $user->empresa?->sucursalPrincipal?->id;

        $resultado = $action->execute(
            empresaId: $empresaId,
            sucursalId: (int) $sucursalId,
            userId: $user->id,
            ventasOffline: $validated['ventas']
        );

        return response()->json([
            'success' => true,
            'message' => "Sincronización finalizada: {$resultado['procesadas']} procesadas, {$resultado['duplicadas']} duplicadas ignoradas, {$resultado['fallidas']} fallidas.",
            'data' => $resultado,
        ]);
    }
}
