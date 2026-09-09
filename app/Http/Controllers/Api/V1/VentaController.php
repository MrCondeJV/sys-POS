<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Venta;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VentaController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Venta::with(['cliente', 'sucursal', 'usuario', 'documentoVenta'])
            ->latest('id');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $ventas = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($ventas);
    }

    public function show(Venta $venta): JsonResponse
    {
        $this->authorizeCompanyResource($venta);
        $venta->load(['cliente', 'sucursal', 'usuario', 'detalles.producto', 'pagos', 'documentoVenta']);

        return $this->successResponse($venta);
    }

    public function store(Request $request, RegistrarVentaAction $action): JsonResponse
    {
        $validated = $request->validate([
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'tipo_pago' => ['required', Rule::enum(TipoPago::class)],
            'metodo_pago' => ['required', 'string'],
            'tipo_comprobante' => ['nullable', Rule::enum(TipoComprobanteVenta::class)],
            'caja_sesion_id' => ['nullable', 'exists:cajas_sesiones,id'],
            'pago_con' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.impuesto_porcentaje' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $sucursalId = $validated['sucursal_id'] ?? $user->sucursal_id;

        try {
            $venta = $action->execute(
                empresaId: $user->empresa_id,
                sucursalId: (int) $sucursalId,
                userId: $user->id,
                items: $validated['items'],
                clienteId: $validated['cliente_id'] ?? null,
                tipoPago: TipoPago::from($validated['tipo_pago']),
                metodoPago: $validated['metodo_pago'],
                tipoComprobante: isset($validated['tipo_comprobante'])
                    ? TipoComprobanteVenta::from($validated['tipo_comprobante'])
                    : TipoComprobanteVenta::TICKET,
                cajaSesionId: $validated['caja_sesion_id'] ?? null,
                pagoCon: isset($validated['pago_con']) ? (float) $validated['pago_con'] : null,
                observaciones: $validated['observaciones'] ?? null
            );

            return $this->successResponse($venta, 'Venta registrada exitosamente.', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
