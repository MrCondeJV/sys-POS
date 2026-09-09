<?php

namespace App\Http\Controllers;

use App\Actions\Inventario\RealizarAjusteInventarioAction;
use App\Actions\Inventario\RealizarTrasladoInventarioAction;
use App\Enums\TipoMovimientoInventario;
use App\Exceptions\StockInsuficienteException;
use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Producto;
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
use InvalidArgumentException;

class InventarioController extends Controller
{
    /**
     * Muestra el panel general de inventario con métricas, filtros y existencias por sucursal.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Inventario::class);

        $empresaId = CompanyContext::getId();
        $sucursales = Sucursal::activa()->get();
        $categorias = Categoria::activa()->get();

        $term = $request->input('buscar');
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->input('sucursal_id') : null;
        $categoriaId = $request->filled('categoria_id') ? (int) $request->input('categoria_id') : null;
        $estadoStock = $request->input('estado_stock', 'todos');

        // Construir consulta base de inventario con relaciones
        $query = Inventario::with([
            'producto' => function ($q) {
                $q->with(['categoria', 'marca', 'unidadMedida']);
            },
            'sucursal',
        ]);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($categoriaId) {
            $query->whereHas('producto', function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        if ($term) {
            $query->whereHas('producto', function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                    ->orWhere('codigo', 'like', "%{$term}%")
                    ->orWhere('codigo_barras', 'like', "%{$term}%");
            });
        }

        // Filtro por condición de stock
        if ($estadoStock === 'bajo_stock') {
            $query->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0);
        } elseif ($estadoStock === 'agotado') {
            $query->where('stock', '<=', 0);
        } elseif ($estadoStock === 'disponible') {
            $query->whereColumn('stock', '>', 'stock_minimo');
        }

        $inventarios = $query->orderBy('stock', 'asc')->paginate(15)->withQueryString();

        // Métricas globales del tenant para las tarjetas KPI superiores
        $metricas = DB::table('inventarios')
            ->join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->where('inventarios.empresa_id', $empresaId)
            ->whereNull('productos.deleted_at')
            ->when($sucursalId, fn ($q) => $q->where('inventarios.sucursal_id', $sucursalId))
            ->selectRaw('
                COUNT(inventarios.id) as total_items,
                COALESCE(SUM(inventarios.stock * productos.precio_compra), 0) as valor_costo,
                COALESCE(SUM(inventarios.stock * productos.precio_venta), 0) as valor_pvp,
                COALESCE(SUM(CASE WHEN inventarios.stock <= inventarios.stock_minimo AND inventarios.stock > 0 THEN 1 ELSE 0 END), 0) as bajo_stock_count,
                COALESCE(SUM(CASE WHEN inventarios.stock <= 0 THEN 1 ELSE 0 END), 0) as agotados_count
            ')
            ->first();

        $valorCosto = (float) ($metricas?->valor_costo ?? 0);
        $valorPvp = (float) ($metricas?->valor_pvp ?? 0);
        $totalItems = (int) ($metricas?->total_items ?? 0);
        $totalBajoStock = (int) ($metricas?->bajo_stock_count ?? 0);
        $totalAgotados = (int) ($metricas?->agotados_count ?? 0);

        return view('inventario.index', compact(
            'inventarios',
            'sucursales',
            'categorias',
            'term',
            'sucursalId',
            'categoriaId',
            'estadoStock',
            'valorCosto',
            'valorPvp',
            'totalItems',
            'totalBajoStock',
            'totalAgotados'
        ));
    }

    /**
     * Muestra el Kardex cronológico inmutable de movimientos de un producto.
     */
    public function kardex(Request $request, Producto $producto): View
    {
        Gate::authorize('viewKardex', [Inventario::class, $producto]);

        $sucursales = Sucursal::activa()->get();
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->input('sucursal_id') : null;
        $tipo = $request->input('tipo');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = $producto->movimientosInventario()
            ->with(['sucursal', 'sucursalDestino', 'user']);

        if ($sucursalId) {
            $query->where(function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                    ->orWhere('sucursal_destino_id', $sucursalId);
            });
        }

        if ($tipo) {
            $query->porTipo($tipo);
        }

        $query->enRango($desde, $hasta);

        $movimientos = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Existencias actuales distribuidas por sucursal
        $existenciasPorSucursal = $producto->inventarios()
            ->with('sucursal')
            ->get();

        $tiposMovimiento = TipoMovimientoInventario::cases();

        return view('inventario.kardex', compact(
            'producto',
            'movimientos',
            'existenciasPorSucursal',
            'sucursales',
            'tiposMovimiento',
            'sucursalId',
            'tipo',
            'desde',
            'hasta'
        ));
    }

    /**
     * Muestra el formulario para realizar un ajuste de inventario.
     */
    public function createAjuste(Request $request): View
    {
        Gate::authorize('ajustar', Inventario::class);

        $productoSeleccionado = null;
        if ($request->filled('producto_id')) {
            $productoSeleccionado = Producto::find($request->input('producto_id'));
        }

        $productos = Producto::activo()->with(['categoria', 'unidadMedida', 'inventarios'])->orderBy('nombre')->get();
        $sucursales = Sucursal::activa()->get();
        $sucursalActivaId = BranchContext::getId() ?: $sucursales->first()?->id;

        // Si hay producto y sucursal, obtener stock actual
        $stockActual = 0;
        if ($productoSeleccionado && $sucursalActivaId) {
            $inv = Inventario::where('sucursal_id', $sucursalActivaId)
                ->where('producto_id', $productoSeleccionado->id)
                ->first();
            $stockActual = $inv ? (float) $inv->stock : 0;
        }

        return view('inventario.ajuste', compact(
            'productos',
            'sucursales',
            'productoSeleccionado',
            'sucursalActivaId',
            'stockActual'
        ));
    }

    /**
     * Procesa y registra un ajuste de inventario manual.
     */
    public function storeAjuste(Request $request, RealizarAjusteInventarioAction $action): RedirectResponse
    {
        Gate::authorize('ajustar', Inventario::class);

        $validated = $request->validate([
            'producto_id' => ['required', 'integer', new BelongsToActiveCompany('productos')],
            'sucursal_id' => ['required', 'integer', new BelongsToActiveCompany('sucursales')],
            'tipo' => ['required', Rule::in(['AJUSTE_POSITIVO', 'AJUSTE_NEGATIVO'])],
            'cantidad' => ['required', 'numeric', 'gt:0'],
            'motivo' => ['required', 'string', 'max:150'],
            'notas' => ['nullable', 'string', 'max:500'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
        ]);

        $tipoEnum = TipoMovimientoInventario::from($validated['tipo']);

        try {
            $movimiento = $action->execute(
                productoId: (int) $validated['producto_id'],
                sucursalId: (int) $validated['sucursal_id'],
                tipo: $tipoEnum,
                cantidad: (float) $validated['cantidad'],
                motivo: $validated['motivo'],
                notas: $validated['notas'] ?? null,
                costoUnitario: isset($validated['costo_unitario']) ? (float) $validated['costo_unitario'] : null,
                userId: auth()->id()
            );

            return redirect()->route('inventario.kardex', $validated['producto_id'])
                ->with('success', "Ajuste de inventario registrado correctamente. Nuevo saldo: {$movimiento->stock_posterior} unidades.");
        } catch (StockInsuficienteException $e) {
            return back()->withErrors(['cantidad' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Muestra el formulario para realizar un traslado entre sucursales.
     */
    public function createTraslado(Request $request): View
    {
        Gate::authorize('trasladar', Inventario::class);

        $productoSeleccionado = null;
        if ($request->filled('producto_id')) {
            $productoSeleccionado = Producto::find($request->input('producto_id'));
        }

        $productos = Producto::activo()->with(['categoria', 'unidadMedida', 'inventarios'])->orderBy('nombre')->get();
        $sucursales = Sucursal::activa()->get();
        $sucursalOrigenId = BranchContext::getId() ?: $sucursales->first()?->id;

        return view('inventario.traslado', compact(
            'productos',
            'sucursales',
            'productoSeleccionado',
            'sucursalOrigenId'
        ));
    }

    /**
     * Procesa y transfiere existencias entre sucursales de forma atómica.
     */
    public function storeTraslado(Request $request, RealizarTrasladoInventarioAction $action): RedirectResponse
    {
        Gate::authorize('trasladar', Inventario::class);

        $validated = $request->validate([
            'producto_id' => ['required', 'integer', new BelongsToActiveCompany('productos')],
            'sucursal_origen_id' => ['required', 'integer', new BelongsToActiveCompany('sucursales')],
            'sucursal_destino_id' => [
                'required',
                'integer',
                'different:sucursal_origen_id',
                new BelongsToActiveCompany('sucursales'),
            ],
            'cantidad' => ['required', 'numeric', 'gt:0'],
            'motivo' => ['required', 'string', 'max:150'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $action->execute(
                productoId: (int) $validated['producto_id'],
                sucursalOrigenId: (int) $validated['sucursal_origen_id'],
                sucursalDestinoId: (int) $validated['sucursal_destino_id'],
                cantidad: (float) $validated['cantidad'],
                motivo: $validated['motivo'],
                notas: $validated['notas'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('inventario.kardex', $validated['producto_id'])
                ->with('success', 'Traslado de existencias completado exitosamente entre sucursales.');
        } catch (StockInsuficienteException $e) {
            return back()->withErrors(['cantidad' => $e->getMessage()])->withInput();
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }
}
