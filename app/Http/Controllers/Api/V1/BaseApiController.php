<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function successResponse(mixed $data = null, string $message = 'Operación exitosa', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(string $message = 'Error en la solicitud', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function authorizeCompanyResource(mixed $model): void
    {
        $user = auth()->user();
        if ($user && isset($model->empresa_id) && (int) $model->empresa_id !== (int) $user->empresa_id) {
            abort(404, 'Recurso no encontrado.');
        }
    }
}
