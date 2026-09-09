<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Proveedor;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    /**
     * Muestra el catálogo de proveedores de la empresa activa.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Proveedor::class)) {
            abort(403, 'No tienes autorización para ver proveedores.');
        }

        $term = $request->input('buscar');
        $estado = $request->input('estado');

        $query = Proveedor::withCount('compras')->latest();

        if (! empty($term)) {
            $query->buscar($term);
        }

        if (! empty($estado)) {
            $query->where('estado', $estado);
        }

        $proveedores = $query->paginate(15)->withQueryString();

        $totalProveedores = Proveedor::count();
        $totalActivos = Proveedor::activo()->count();

        return view('proveedores.index', compact(
            'proveedores',
            'term',
            'estado',
            'totalProveedores',
            'totalActivos'
        ));
    }

    /**
     * Registra un nuevo proveedor en la empresa activa.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Proveedor::class)) {
            abort(403, 'No tienes autorización para registrar proveedores.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_contacto' => ['nullable', 'string', 'max:150'],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoIdentidad::class)],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('proveedores', 'numero_documento')
                    ->where('empresa_id', $empresaId)
                    ->whereNull('deleted_at'),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        Proveedor::create(array_merge($validated, [
            'empresa_id' => $empresaId,
        ]));

        return back()->with('success', 'Proveedor registrado exitosamente.');
    }

    /**
     * Actualiza la información de un proveedor existente.
     */
    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        if (Gate::denies('update', $proveedor)) {
            abort(403, 'No tienes autorización para editar este proveedor.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_contacto' => ['nullable', 'string', 'max:150'],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoIdentidad::class)],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('proveedores', 'numero_documento')
                    ->where('empresa_id', $empresaId)
                    ->ignore($proveedor->id)
                    ->whereNull('deleted_at'),
            ],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $proveedor->update($validated);

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Elimina (soft delete) un proveedor del catálogo.
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        if (Gate::denies('delete', $proveedor)) {
            abort(403, 'No tienes autorización para eliminar este proveedor.');
        }

        $proveedor->delete();

        return back()->with('success', 'Proveedor eliminado con éxito.');
    }
}
