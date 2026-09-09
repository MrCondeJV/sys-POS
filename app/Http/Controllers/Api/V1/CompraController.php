<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Compra::with(['proveedor', 'sucursal', 'usuario'])
            ->latest('id');

        $compras = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($compras);
    }

    public function show(Compra $compra): JsonResponse
    {
        $this->authorizeCompanyResource($compra);
        $compra->load(['proveedor', 'sucursal', 'usuario', 'detalles.producto']);

        return $this->successResponse($compra);
    }
}
