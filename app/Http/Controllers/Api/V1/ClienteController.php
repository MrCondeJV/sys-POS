<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('razon_social', 'like', "%{$q}%")
                  ->orWhere('numero_documento', 'like', "%{$q}%")
                  ->orWhere('telefono', 'like', "%{$q}%");
            });
        }

        $clientes = $query->paginate($request->integer('per_page', 25));

        return $this->successResponse($clientes);
    }

    public function store(Request $request): JsonResponse
    {
        $empresaId = $request->user()->empresa_id;

        $validated = $request->validate([
            'tipo_documento' => ['required', 'string'],
            'numero_documento' => [
                'required',
                'string',
                Rule::unique('clientes')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'razon_social' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'cupo_credito' => ['nullable', 'numeric', 'min:0'],
            'plazo_dias' => ['nullable', 'integer', 'min:0'],
        ]);

        $cliente = Cliente::create(array_merge($validated, [
            'empresa_id' => $empresaId,
        ]));

        return $this->successResponse($cliente, 'Cliente creado exitosamente.', 201);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        $this->authorizeCompanyResource($cliente);
        $cliente->load('cuentasPorCobrar');

        return $this->successResponse($cliente);
    }
}
