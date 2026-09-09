<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Listado general de productos con búsqueda en tiempo real y filtros.
     */
    public function index(Request $request): View
    {
        $term = $request->input('buscar');
        $categoriaId = $request->input('categoria_id');
        $bajoStock = $request->boolean('bajo_stock');

        $query = Producto::with(['categoria', 'marca', 'unidadMedida'])->latest();

        if (! empty($term)) {
            $query->buscar($term);
        }

        if (! empty($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($bajoStock) {
            $query->bajoStock();
        }

        $productos = $query->paginate(15)->withQueryString();

        $categorias = Categoria::activa()->get();
        $marcas = Marca::activa()->get();

        return view('productos.index', compact('productos', 'categorias', 'marcas', 'term', 'categoriaId', 'bajoStock'));
    }

    /**
     * Muestra el formulario de registro de producto.
     */
    public function create(): View
    {
        if (Gate::denies('create', Producto::class)) {
            abort(403, 'No tienes autorización para crear productos.');
        }

        $categorias = Categoria::activa()->get();
        $marcas = Marca::activa()->get();
        $unidades = UnidadMedida::activa()->get();

        return view('productos.create', compact('categorias', 'marcas', 'unidades'));
    }

    /**
     * Almacena un nuevo producto forzando la asignación del tenant activo.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Producto::class)) {
            abort(403, 'No tienes autorización para crear productos.');
        }

        if ($request->filled('estado')) {
            $request->merge(['estado' => strtoupper($request->input('estado'))]);
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->where('empresa_id', $empresaId)->whereNull('deleted_at'),
            ],
            'codigo_barras' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'categoria_id' => ['nullable', new BelongsToActiveCompany('categorias')],
            'marca_id' => ['nullable', new BelongsToActiveCompany('marcas')],
            'unidad_medida_id' => ['nullable', new BelongsToActiveCompany('unidades_medida')],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'precio_mayorista' => ['nullable', 'numeric', 'min:0'],
            'precio_distribuidor' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['nullable', Rule::enum(EstadoGeneral::class)],
        ]);

        Producto::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'precio_compra' => $validated['precio_compra'] ?? 0,
            'stock' => $validated['stock'] ?? 0,
            'stock_minimo' => $validated['stock_minimo'] ?? 0,
            'iva' => $validated['iva'] ?? 0,
            'estado' => $validated['estado'] ?? EstadoGeneral::ACTIVO,
        ]));

        return redirect()->route('productos.index')
            ->with('success', 'Producto registrado exitosamente.');
    }

    /**
     * Muestra el formulario de edición de producto.
     */
    public function edit(Producto $producto): View
    {
        if (Gate::denies('update', $producto)) {
            abort(403, 'No tienes autorización para editar este producto.');
        }

        $categorias = Categoria::activa()->get();
        $marcas = Marca::activa()->get();
        $unidades = UnidadMedida::activa()->get();

        return view('productos.edit', compact('producto', 'categorias', 'marcas', 'unidades'));
    }

    /**
     * Actualiza el producto protegiendo el tenant.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        if (Gate::denies('update', $producto)) {
            abort(403, 'No tienes autorización para editar este producto.');
        }

        if ($request->filled('estado')) {
            $request->merge(['estado' => strtoupper($request->input('estado'))]);
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')
                    ->where('empresa_id', $empresaId)
                    ->whereNull('deleted_at')
                    ->ignore($producto->id),
            ],
            'codigo_barras' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'categoria_id' => ['nullable', new BelongsToActiveCompany('categorias')],
            'marca_id' => ['nullable', new BelongsToActiveCompany('marcas')],
            'unidad_medida_id' => ['nullable', new BelongsToActiveCompany('unidades_medida')],
            'precio_compra' => ['nullable', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'precio_mayorista' => ['nullable', 'numeric', 'min:0'],
            'precio_distribuidor' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['nullable', Rule::enum(EstadoGeneral::class)],
        ]);

        $producto->update(array_merge($validated, [
            'precio_compra' => $validated['precio_compra'] ?? 0,
            'stock' => $validated['stock'] ?? 0,
            'stock_minimo' => $validated['stock_minimo'] ?? 0,
            'iva' => $validated['iva'] ?? 0,
        ]));

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina el producto de forma suave (soft delete).
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        if (Gate::denies('delete', $producto)) {
            abort(403, 'No tienes autorización para eliminar este producto.');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
