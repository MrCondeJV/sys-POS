<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Sucursal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SucursalController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $sucursales = Sucursal::all();

        return $this->successResponse($sucursales);
    }

    public function show(Sucursal $sucursal): JsonResponse
    {
        $this->authorizeCompanyResource($sucursal);
        return $this->successResponse($sucursal);
    }
}
