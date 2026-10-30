<?php

namespace App\Http\Controllers;

use App\Actions\Inventario\ConvertirProductoAMedidaAction;
use App\Actions\Inventario\RegistrarMovimientoInventarioAction;
use App\DTOs\MovimientoInventarioDTO;
use App\Enums\EstadoGeneral;
use App\Enums\PermisoSistema;
use App\Enums\TipoMovimientoInventario;
use App\Models\Categoria;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Listado general de productos con búsqueda en tiempo real y filtros.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()->can(PermisoSistema::PRODUCTOS_VER->value), 403);

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
        $totalProductos = Producto::count();
        $totalBajoStock = Producto::bajoStock()->count();

        return view('productos.index', compact(
            'productos',
            'categorias',
            'marcas',
            'term',
            'categoriaId',
            'bajoStock',
            'totalProductos',
            'totalBajoStock'
        ));
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
        $impuestos = Impuesto::activo()->orderBy('porcentaje')->get();

        return view('productos.create', compact('categorias', 'marcas', 'unidades', 'impuestos'));
    }

    /**
     * Almacena un nuevo producto forzando la asignación del tenant activo.
     */
    public function store(
        Request $request,
        RegistrarMovimientoInventarioAction $movimientoAction,
        ConvertirProductoAMedidaAction $conversionAction
    ): RedirectResponse {
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
            'm2_por_caja' => ['nullable', 'numeric', 'gt:0', 'max:99999999.9999'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['nullable', Rule::enum(EstadoGeneral::class)],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);
        $factorM2PorCaja = $validated['m2_por_caja'] ?? null;
        unset($validated['m2_por_caja']);

        $imagenPath = null;
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $imagenPath = $request->file('imagen')->store("productos/{$empresaId}", 'public');
        }

        $stockInicial = (float) ($validated['stock'] ?? 0);
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        DB::transaction(function () use (
            $validated,
            $empresaId,
            $imagenPath,
            $stockInicial,
            $sucursalId,
            $movimientoAction,
            $conversionAction,
            $factorM2PorCaja
        ) {
            $producto = Producto::create(array_merge($validated, [
                'empresa_id' => $empresaId,
                'imagen_path' => $imagenPath,
                'precio_compra' => $validated['precio_compra'] ?? 0,
                // Con sucursal activa, el stock consolidado lo calcula el movimiento de inventario
                'stock' => $sucursalId ? 0 : $stockInicial,
                'stock_minimo' => $validated['stock_minimo'] ?? 0,
                'iva' => $validated['iva'] ?? 0,
                'estado' => $validated['estado'] ?? EstadoGeneral::ACTIVO,
            ]));

            // Registrar el stock inicial en la sucursal activa para que quede en inventario y Kardex
            if ($sucursalId && $stockInicial > 0) {
                $movimientoAction->execute(new MovimientoInventarioDTO(
                    productoId: $producto->id,
                    sucursalId: (int) $sucursalId,
                    tipo: TipoMovimientoInventario::AJUSTE_POSITIVO,
                    cantidad: $stockInicial,
                    referencia: 'STOCK-INICIAL',
                    costoUnitario: (float) $producto->precio_compra,
                    userId: auth()->id(),
                    notas: 'Stock inicial registrado al crear el producto',
                ));
            }

            if ($factorM2PorCaja !== null) {
                $conversionAction->execute($producto, (float) $factorM2PorCaja, auth()->id());
            }
        });

        return redirect()->route('productos.index')
            ->with(
                'success',
                $factorM2PorCaja !== null
                    ? 'Producto registrado y convertido a M2. Define el precio de venta por M2 para habilitar esa modalidad.'
                    : 'Producto registrado exitosamente.'
            );
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
        $impuestos = Impuesto::activo()->orderBy('porcentaje')->get();

        return view('productos.edit', compact('producto', 'categorias', 'marcas', 'unidades', 'impuestos'));
    }

    /**
     * Actualiza el producto protegiendo el tenant.
     */
    public function update(
        Request $request,
        Producto $producto,
        ConvertirProductoAMedidaAction $conversionAction
    ): RedirectResponse {
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
            'm2_por_caja' => ['nullable', 'numeric', 'gt:0', 'max:99999999.9999'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'estado' => ['nullable', Rule::enum(EstadoGeneral::class)],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'eliminar_imagen' => ['nullable', 'boolean'],
        ]);
        $factorM2PorCaja = $validated['m2_por_caja'] ?? null;
        unset($validated['m2_por_caja']);

        $imagenPath = $producto->imagen_path;

        if ($request->boolean('eliminar_imagen') && $imagenPath) {
            Storage::disk('public')->delete($imagenPath);
            $imagenPath = null;
        }

        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            if ($producto->imagen_path) {
                Storage::disk('public')->delete($producto->imagen_path);
            }
            $imagenPath = $request->file('imagen')->store("productos/{$empresaId}", 'public');
        }

        DB::transaction(function () use ($producto, $validated, $imagenPath, $factorM2PorCaja, $conversionAction) {
            $producto->update(array_merge($validated, [
                'imagen_path' => $imagenPath,
                'precio_compra' => $validated['precio_compra'] ?? 0,
                'stock' => $validated['stock'] ?? $producto->stock,
                'stock_minimo' => $validated['stock_minimo'] ?? 0,
                'iva' => $validated['iva'] ?? 0,
            ]));

            if ($factorM2PorCaja !== null) {
                $conversionAction->execute($producto, (float) $factorM2PorCaja, auth()->id());
            }
        });

        return redirect()->route('productos.index')
            ->with(
                'success',
                $factorM2PorCaja !== null
                    ? 'Producto convertido a M2. Define el precio de venta por M2 para habilitar esa modalidad.'
                    : 'Producto actualizado exitosamente.'
            );
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
