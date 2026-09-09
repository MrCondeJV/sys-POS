<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Producto::with(['categoria', 'marca', 'unidadMedida', 'impuesto']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%")
                  ->orWhere('codigo_barras', 'like', "%{$q}%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($productos);
    }

    public function show(Producto $producto): JsonResponse
    {
        $this->authorizeCompanyResource($producto);
        $producto->load(['categoria', 'marca', 'unidadMedida', 'impuesto', 'inventarios.sucursal']);

        return $this->successResponse($producto);
    }
}
