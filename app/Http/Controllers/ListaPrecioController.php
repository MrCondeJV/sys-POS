<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\TipoAjusteListaPrecio;
use App\Models\ListaPrecio;
use App\Models\ListaPrecioDetalle;
use App\Models\Producto;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ListaPrecioController extends Controller
{
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', ListaPrecio::class)) {
            abort(403, 'No tienes autorización para ver listas de precios.');
        }

        $empresaId = CompanyContext::getId();

        $query = ListaPrecio::where('empresa_id', $empresaId)
            ->withCount(['detalles', 'clientes']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $listas = $query->orderByDesc('es_predeterminada')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('listas_precios.index', compact('listas'));
    }

    public function create(): View
    {
        if (Gate::denies('create', ListaPrecio::class)) {
            abort(403, 'No tienes autorización para crear listas de precios.');
        }

        return view('listas_precios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', ListaPrecio::class)) {
            abort(403, 'No tienes autorización para crear listas de precios.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('listas_precios', 'codigo')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipo_ajuste' => ['required', Rule::enum(TipoAjusteListaPrecio::class)],
            'porcentaje_defecto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'es_predeterminada' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $esPredeterminada = (bool) ($request->boolean('es_predeterminada'));

        DB::transaction(function () use ($empresaId, $validated, $esPredeterminada) {
            if ($esPredeterminada) {
                ListaPrecio::where('empresa_id', $empresaId)->update(['es_predeterminada' => false]);
            }

            ListaPrecio::create([
                'empresa_id' => $empresaId,
                'nombre' => $validated['nombre'],
                'codigo' => $validated['codigo'] ? strtoupper($validated['codigo']) : null,
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_ajuste' => $validated['tipo_ajuste'],
                'porcentaje_defecto' => $validated['porcentaje_defecto'] ?? 0,
                'es_predeterminada' => $esPredeterminada,
                'estado' => $validated['estado'],
            ]);
        });

        return redirect()->route('listas-precios.index')
            ->with('success', 'Lista de precios creada exitosamente.');
    }

    public function show(ListaPrecio $listaPrecio, Request $request): View
    {
        if (Gate::denies('view', $listaPrecio)) {
            abort(403, 'No tienes autorización para ver esta lista de precios.');
        }

        $empresaId = CompanyContext::getId();

        $listaPrecio->loadCount(['detalles', 'clientes', 'ventas']);

        $detalles = $listaPrecio->detalles()
            ->with('producto.categoria')
            ->paginate(20);

        // Catálogo de productos para asignación de precio específico
        $productos = Producto::where('empresa_id', $empresaId)
            ->where('estado', EstadoGeneral::ACTIVO->value)
            ->orderBy('nombre')
            ->get();

        return view('listas_precios.show', compact('listaPrecio', 'detalles', 'productos'));
    }

    public function edit(ListaPrecio $listaPrecio): View
    {
        if (Gate::denies('update', $listaPrecio)) {
            abort(403, 'No tienes autorización para editar esta lista de precios.');
        }

        return view('listas_precios.edit', compact('listaPrecio'));
    }

    public function update(Request $request, ListaPrecio $listaPrecio): RedirectResponse
    {
        if (Gate::denies('update', $listaPrecio)) {
            abort(403, 'No tienes autorización para actualizar esta lista de precios.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('listas_precios', 'codigo')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($listaPrecio->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipo_ajuste' => ['required', Rule::enum(TipoAjusteListaPrecio::class)],
            'porcentaje_defecto' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'es_predeterminada' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $esPredeterminada = (bool) ($request->boolean('es_predeterminada'));

        DB::transaction(function () use ($empresaId, $listaPrecio, $validated, $esPredeterminada) {
            if ($esPredeterminada && ! $listaPrecio->es_predeterminada) {
                ListaPrecio::where('empresa_id', $empresaId)->update(['es_predeterminada' => false]);
            }

            $listaPrecio->update([
                'nombre' => $validated['nombre'],
                'codigo' => $validated['codigo'] ? strtoupper($validated['codigo']) : null,
                'descripcion' => $validated['descripcion'] ?? null,
                'tipo_ajuste' => $validated['tipo_ajuste'],
                'porcentaje_defecto' => $validated['porcentaje_defecto'] ?? 0,
                'es_predeterminada' => $esPredeterminada,
                'estado' => $validated['estado'],
            ]);
        });

        return redirect()->route('listas-precios.index')
            ->with('success', "Lista de precios '{$listaPrecio->nombre}' actualizada exitosamente.");
    }

    public function destroy(ListaPrecio $listaPrecio): RedirectResponse
    {
        if (Gate::denies('delete', $listaPrecio)) {
            abort(403, 'No tienes autorización para eliminar esta lista de precios.');
        }

        if ($listaPrecio->es_predeterminada) {
            return back()->withErrors(['error' => 'No puedes eliminar la lista de precios predeterminada del sistema.']);
        }

        if ($listaPrecio->clientes()->exists()) {
            return back()->withErrors(['error' => 'No puedes eliminar esta lista porque está asignada a clientes activos.']);
        }

        $listaPrecio->delete();

        return redirect()->route('listas-precios.index')
            ->with('success', 'Lista de precios eliminada correctamente.');
    }

    /**
     * Guarda o actualiza el precio específico de un producto dentro de la lista.
     */
    public function guardarPrecioProducto(Request $request, ListaPrecio $listaPrecio): RedirectResponse
    {
        if (Gate::denies('update', $listaPrecio)) {
            abort(403, 'No tienes autorización para modificar precios en esta lista.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'producto_id' => [
                'required',
                Rule::exists('productos', 'id')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'precio' => ['required', 'numeric', 'min:0'],
        ]);

        ListaPrecioDetalle::updateOrCreate(
            [
                'empresa_id' => $empresaId,
                'lista_precio_id' => $listaPrecio->id,
                'producto_id' => $validated['producto_id'],
            ],
            [
                'precio' => $validated['precio'],
            ]
        );

        return back()->with('success', 'Precio de producto actualizado exitosamente en la lista.');
    }

    /**
     * Elimina el precio específico de un producto de la lista.
     */
    public function eliminarPrecioProducto(ListaPrecio $listaPrecio, ListaPrecioDetalle $detalle): RedirectResponse
    {
        if (Gate::denies('update', $listaPrecio)) {
            abort(403, 'No tienes autorización para modificar precios en esta lista.');
        }

        if ($detalle->lista_precio_id !== $listaPrecio->id) {
            abort(404);
        }

        $detalle->delete();

        return back()->with('success', 'Precio personalizado eliminado. El producto usará la regla general de la lista.');
    }
}
