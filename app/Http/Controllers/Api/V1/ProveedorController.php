<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Gate;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Proveedor::class);

        $query = Proveedor::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('razon_social', 'like', "%{$q}%")
                  ->orWhere('numero_documento', 'like', "%{$q}%");
            });
        }

        $proveedores = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($proveedores);
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        Gate::authorize('view', $proveedor);

        $this->authorizeCompanyResource($proveedor);
        return $this->successResponse($proveedor);
    }
}
