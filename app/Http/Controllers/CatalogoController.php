<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Muestra la vista de gestión unificada de Catálogos auxiliares.
     */
    public function index(): View
    {
        $categorias = Categoria::withCount('productos')->latest()->get();
        $marcas = Marca::withCount('productos')->latest()->get();
        $unidades = UnidadMedida::withCount('productos')->latest()->get();

        return view('catalogos.index', compact('categorias', 'marcas', 'unidades'));
    }

    /**
     * Registra una categoría en la empresa activa.
     */
    public function storeCategoria(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Categoria::class)) {
            abort(403, 'No tienes autorización para crear categorías.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categorias', 'nombre')->where('empresa_id', $empresaId)->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Categoria::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return back()->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Elimina una categoría.
     */
    public function destroyCategoria(Categoria $categoria): RedirectResponse
    {
        if (Gate::denies('delete', $categoria)) {
            abort(403, 'No tienes autorización para eliminar esta categoría.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    /**
     * Registra una marca en la empresa activa.
     */
    public function storeMarca(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Marca::class)) {
            abort(403, 'No tienes autorización para crear marcas.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('marcas', 'nombre')->where('empresa_id', $empresaId)->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Marca::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return back()->with('success', 'Marca creada exitosamente.');
    }

    /**
     * Elimina una marca.
     */
    public function destroyMarca(Marca $marca): RedirectResponse
    {
        if (Gate::denies('delete', $marca)) {
            abort(403, 'No tienes autorización para eliminar esta marca.');
        }

        $marca->delete();

        return back()->with('success', 'Marca eliminada.');
    }

    /**
     * Registra una unidad de medida en la empresa activa.
     */
    public function storeUnidad(Request $request): RedirectResponse
    {
        if (Gate::denies('create', UnidadMedida::class)) {
            abort(403, 'No tienes autorización para crear unidades de medida.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('unidades_medida', 'codigo')->where('empresa_id', $empresaId)->whereNull('deleted_at'),
            ],
        ]);

        UnidadMedida::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'activo' => true,
        ]));

        return back()->with('success', 'Unidad de medida creada exitosamente.');
    }

    /**
     * Elimina una unidad de medida.
     */
    public function destroyUnidad(UnidadMedida $unidad): RedirectResponse
    {
        if (Gate::denies('delete', $unidad)) {
            abort(403, 'No tienes autorización para eliminar esta unidad de medida.');
        }

        $unidad->delete();

        return back()->with('success', 'Unidad de medida eliminada.');
    }
}
