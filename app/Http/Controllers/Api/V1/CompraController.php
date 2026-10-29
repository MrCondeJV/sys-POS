<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Gate;
use App\Models\Compra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Compra::class);

        $query = Compra::with(['proveedor', 'sucursal', 'user'])
            ->latest('id');

        $compras = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($compras);
    }

    public function show(Compra $compra): JsonResponse
    {
        Gate::authorize('view', $compra);

        $this->authorizeCompanyResource($compra);
        $compra->load(['proveedor', 'sucursal', 'user', 'detalles.producto']);

        return $this->successResponse($compra);
    }
}
