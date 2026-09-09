<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventarioController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Inventario::with(['producto', 'sucursal']);

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        $inventario = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($inventario);
    }

    public function kardex(Request $request): JsonResponse
    {
        $query = MovimientoInventario::with(['producto', 'sucursal', 'usuario'])
            ->latest('id');

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $movimientos = $query->paginate($request->integer('per_page', 30));

        return $this->successResponse($movimientos);
    }
}
