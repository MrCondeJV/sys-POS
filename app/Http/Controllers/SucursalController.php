<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Models\Sucursal;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SucursalController extends Controller
{
    /**
     * Listado de sucursales de la empresa activa.
     */
    public function index(): View
    {
        $empresa = CompanyContext::getCompany();

        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        $sucursales = $empresa->sucursales()->latest()->get();

        return view('sucursales.index', compact('empresa', 'sucursales'));
    }

    /**
     * Registra una nueva sucursal asignando automáticamente la empresa activa.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Sucursal::class)) {
            abort(403, 'No tienes autorización para registrar sucursales.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('sucursales', 'nombre')->where('empresa_id', $empresaId)->whereNull('deleted_at'),
            ],
            'codigo' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'es_principal' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $empresaId, $request) {
            if ($request->boolean('es_principal')) {
                Sucursal::where('empresa_id', $empresaId)->update(['es_principal' => false]);
            }

            // Forzar empresa_id desde el backend, ignorando cualquier valor del frontend
            Sucursal::create(array_merge($validated, [
                'empresa_id' => $empresaId,
                'es_principal' => $request->boolean('es_principal'),
                'estado' => EstadoGeneral::ACTIVO,
            ]));
        });

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal creada exitosamente.');
    }

    /**
     * Actualiza los datos de una sucursal existente.
     */
    public function update(Request $request, Sucursal $sucursal): RedirectResponse
    {
        if (Gate::denies('update', $sucursal)) {
            abort(403, 'No tienes autorización para modificar esta sucursal.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('sucursales', 'nombre')
                    ->where('empresa_id', $empresaId)
                    ->whereNull('deleted_at')
                    ->ignore($sucursal->id),
            ],
            'codigo' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'es_principal' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $sucursal, $empresaId, $request) {
            if ($request->boolean('es_principal')) {
                Sucursal::where('empresa_id', $empresaId)
                    ->where('id', '!=', $sucursal->id)
                    ->update(['es_principal' => false]);
            }

            $sucursal->update(array_merge($validated, [
                'es_principal' => $request->boolean('es_principal'),
            ]));
        });

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal actualizada exitosamente.');
    }

    /**
     * Elimina una sucursal siempre que no sea la principal.
     */
    public function destroy(Sucursal $sucursal): RedirectResponse
    {
        if (Gate::denies('delete', $sucursal)) {
            abort(403, 'No puedes eliminar esta sucursal (es la sede principal o no tienes permisos).');
        }

        $sucursal->delete();

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal eliminada correctamente.');
    }

    /**
     * Cambia la sucursal activa en la sesión de trabajo actual.
     */
    public function seleccionar(Request $request): RedirectResponse
    {
        $request->validate([
            'sucursal_id' => ['required', new BelongsToActiveCompany('sucursales')],
        ]);

        BranchContext::setId((int) $request->sucursal_id);

        $nombreSucursal = BranchContext::getBranch()?->nombre ?? 'Sucursal';

        return back()->with('success', "Has cambiado a la sucursal: {$nombreSucursal}");
    }
}
