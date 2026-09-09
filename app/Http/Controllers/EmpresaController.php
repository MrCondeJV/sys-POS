<?php

namespace App\Http\Controllers;

use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    /**
     * Muestra la vista de configuración del perfil de la empresa activa.
     */
    public function perfil(): View
    {
        $empresa = CompanyContext::getCompany();

        if (! $empresa) {
            abort(404, 'Empresa no encontrada en el contexto actual.');
        }

        return view('empresa.perfil', compact('empresa'));
    }

    /**
     * Actualiza la información de la empresa activa sin permitir inyección de tenant.
     */
    public function updatePerfil(Request $request): RedirectResponse
    {
        $empresa = CompanyContext::getCompany();

        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        if ($request->user()->cannot('update', $empresa)) {
            abort(403, 'No tienes permisos para modificar los datos de la empresa.');
        }

        $validated = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'razon_social' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'simbolo_moneda' => ['required', 'string', 'max:5'],
        ]);

        // Asegurar que empresa_id no pueda ser manipulado
        $empresa->update($validated);

        return redirect()->route('empresa.perfil')
            ->with('success', 'Los datos de la empresa han sido actualizados exitosamente.');
    }
}
