<?php

namespace App\Http\Controllers;

use App\Actions\Compras\AnularCompraAction;
use App\Actions\Compras\RegistrarCompraAction;
use App\DTOs\CompraItemDTO;
use App\DTOs\RegistrarCompraDTO;
use App\Enums\EstadoCompra;
use App\Enums\TipoPago;
use App\Exceptions\StockInsuficienteException;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Rules\BelongsToActiveCompany;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompraController extends Controller
{
    /**
     * Listado e historial de compras con métricas y filtros.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Compra::class)) {
            abort(403, 'No tienes autorización para ver compras.');
        }

        $sucursalId = $request->input('sucursal_id');
        $proveedorId = $request->input('proveedor_id');
        $estado = $request->input('estado');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $term = $request->input('buscar');

        $query = Compra::with(['proveedor', 'sucursal', 'user'])
            ->latest('fecha_emision')
            ->latest('id');

        if (! empty($sucursalId)) {
            $query->porSucursal((int) $sucursalId);
        }

        if (! empty($proveedorId)) {
            $query->porProveedor((int) $proveedorId);
        }

        if (! empty($estado)) {
            $query->where('estado', $estado);
        }

        if (! empty($desde) || ! empty($hasta)) {
            $query->enRangoFechas($desde, $hasta);
        }

        if (! empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('numero_factura', 'like', "%{$term}%")
                    ->orWhereHas('proveedor', function ($pq) use ($term) {
                        $pq->where('razon_social', 'like', "%{$term}%")
                            ->orWhere('numero_documento', 'like', "%{$term}%");
                    });
            });
        }

        $compras = $query->paginate(15)->withQueryString();

        // KPIs
        $totalCompras = Compra::count();
        $montoTotalRegistrado = Compra::registrada()->sum('total');
        $comprasRegistradas = Compra::registrada()->count();
        $comprasAnuladas = Compra::where('estado', EstadoCompra::ANULADA->value)->count();

        $sucursales = Sucursal::activa()->get();
        $proveedores = Proveedor::activo()->orderBy('razon_social')->get();

        return view('compras.index', compact(
            'compras',
            'sucursales',
            'proveedores',
            'sucursalId',
            'proveedorId',
            'estado',
            'desde',
            'hasta',
            'term',
            'totalCompras',
            'montoTotalRegistrado',
            'comprasRegistradas',
            'comprasAnuladas'
        ));
    }

    /**
     * Muestra el formulario para registrar una nueva compra de mercancías.
     */
    public function create(): View
    {
        if (Gate::denies('create', Compra::class)) {
            abort(403, 'No tienes autorización para registrar compras.');
        }

        $sucursales = Sucursal::activa()->get();
        $proveedores = Proveedor::activo()->orderBy('razon_social')->get();
        $productos = Producto::with(['categoria', 'marca', 'unidadMedida'])
            ->orderBy('nombre')
            ->get();

        return view('compras.create', compact('sucursales', 'proveedores', 'productos'));
    }

    /**
     * Procesa y registra la compra transaccionalmente, ingresando el stock al Kardex.
     */
    public function store(Request $request, RegistrarCompraAction $action): RedirectResponse
    {
        if (Gate::denies('create', Compra::class)) {
            abort(403, 'No tienes autorización para registrar compras.');
        }

        $validated = $request->validate([
            'sucursal_id' => ['required', 'integer', new BelongsToActiveCompany('sucursales')],
            'proveedor_id' => ['required', 'integer', new BelongsToActiveCompany('proveedores')],
            'numero_factura' => ['required', 'string', 'max:100'],
            'fecha_emision' => ['required', 'date', 'before_or_equal:today'],
            'tipo_pago' => ['required', Rule::enum(TipoPago::class)],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', new BelongsToActiveCompany('productos')],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.costo_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.porcentaje_iva' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'items.*.producto_id.distinct' => 'Cada línea de compra debe ser un artículo único. No puedes repetir el mismo producto en varias filas.',
        ]);

        $items = array_map(function ($item) {
            return new CompraItemDTO(
                productoId: (int) $item['producto_id'],
                cantidad: (float) $item['cantidad'],
                costoUnitario: (float) $item['costo_unitario'],
                porcentajeIva: (float) ($item['porcentaje_iva'] ?? 0.0)
            );
        }, $validated['items']);

        $dto = new RegistrarCompraDTO(
            proveedorId: (int) $validated['proveedor_id'],
            sucursalId: (int) $validated['sucursal_id'],
            numeroFactura: $validated['numero_factura'],
            fechaEmision: $validated['fecha_emision'],
            tipoPago: TipoPago::from($validated['tipo_pago']),
            items: $items,
            descuento: (float) ($validated['descuento'] ?? 0.0),
            observaciones: $validated['observaciones'] ?? null,
            userId: auth()->id()
        );

        try {
            $compra = $action->execute($dto);

            return redirect()->route('compras.show', $compra)
                ->with('success', "Factura de compra #{$compra->numero_factura} registrada con éxito. Mercancía cargada a inventario.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar la compra: '.$e->getMessage());
        }
    }

    /**
     * Muestra el detalle visual de una factura de compra y sus líneas.
     */
    public function show(Compra $compra): View
    {
        if (Gate::denies('view', $compra)) {
            abort(403, 'No tienes autorización para ver esta compra.');
        }

        $compra->load(['detalles.producto.unidadMedida', 'proveedor', 'sucursal', 'user']);

        return view('compras.show', compact('compra'));
    }

    /**
     * Anula una compra y devuelve las existencias en inventario.
     */
    public function anular(Request $request, Compra $compra, AnularCompraAction $action): RedirectResponse
    {
        if (Gate::denies('anular', $compra)) {
            abort(403, 'No tienes autorización para anular esta compra.');
        }

        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        try {
            $action->execute($compra, $validated['motivo'], auth()->id());

            return back()->with('success', "Factura #{$compra->numero_factura} anulada exitosamente y stock revertido.");
        } catch (StockInsuficienteException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'No fue posible anular la compra: '.$e->getMessage());
        }
    }
}
