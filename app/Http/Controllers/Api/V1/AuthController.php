<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::withoutGlobalScopes()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Credenciales inválidas.', 401);
        }

        $deviceName = $request->device_name ?: 'API Client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id,
            ],
        ], 'Autenticación exitosa.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Sesión finalizada exitosamente.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['empresa', 'sucursal', 'roles']);

        return $this->successResponse([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }
}
