<?php

namespace App\Http\Controllers\Api\V1;

use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $empresa = $request->user()->empresa?->load('sucursales');

        if (! $empresa) {
            return $this->errorResponse('No se encontró información de la empresa.', 404);
        }

        return $this->successResponse($empresa);
    }
}
