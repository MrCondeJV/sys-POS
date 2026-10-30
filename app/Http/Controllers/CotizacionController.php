<?php

namespace App\Http\Controllers;

use App\Actions\Ventas\RegistrarCotizacionAction;
use App\Enums\EstadoCotizacion;
use App\Enums\EstadoGeneral;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Inventario;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Rules\BelongsToActiveCompany;
use App\Services\DocumentoPdfService;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    /**
     * Listado de cotizaciones con filtros por estado y búsqueda.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Cotizacion::class)) {
            abort(403, 'No tienes autorización para consultar cotizaciones.');
        }

        $term = $request->input('buscar');
        $estado = $request->input('estado');

        $query = Cotizacion::with(['cliente', 'usuario', 'venta'])->latest('id');

        if ($sucursalId = BranchContext::getId()) {
            $query->where('sucursal_id', $sucursalId);
        }

        $query->buscar($term);

        if (in_array($estado, array_column(EstadoCotizacion::cases(), 'value'), true)) {
            $query->where('estado', $estado);
        }

        $cotizaciones = $query->paginate(15)->withQueryString();

        $totalPendientes = Cotizacion::where('estado', EstadoCotizacion::PENDIENTE->value)->count();
        $montoPendiente = (float) Cotizacion::where('estado', EstadoCotizacion::PENDIENTE->value)->sum('total');
        $convertidasMes = Cotizacion::where('estado', EstadoCotizacion::CONVERTIDA->value)
            ->whereMonth('fecha_conversion', now()->month)
            ->whereYear('fecha_conversion', now()->year)
            ->count();

        return view('cotizaciones.index', compact('cotizaciones', 'term', 'estado', 'totalPendientes', 'montoPendiente', 'convertidasMes'));
    }

    /**
     * Formulario para armar una cotización desde el catálogo.
     */
    public function create(): View
    {
        if (Gate::denies('create', Cotizacion::class)) {
            abort(403, 'No tienes autorización para crear cotizaciones.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        // Existencias de la sede: informativas (se puede cotizar sin stock, el inventario se valida al vender)
        $stockSucursal = Inventario::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->pluck('stock', 'producto_id');

        $listasPrecios = ListaPrecio::where('empresa_id', $empresaId)
            ->where('estado', EstadoGeneral::ACTIVO->value)
            ->with('detalles')
            ->get();

        $productos = Producto::where('empresa_id', $empresaId)
            ->activo()
            ->with(['categoria', 'unidadMedida', 'presentaciones' => fn ($q) => $q->where('estado', EstadoGeneral::ACTIVO->value)->with('unidadMedida')])
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) use ($listasPrecios, $stockSucursal) {
                $preciosListas = [];
                foreach ($listasPrecios as $lp) {
                    $preciosListas[$lp->id] = $lp->calcularPrecio($p);
                }

                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'codigo' => $p->codigo ?? '',
                    'codigo_barras' => $p->codigo_barras ?? '',
                    'sku' => $p->sku ?? '',
                    'precio_venta' => (float) $p->precio_venta,
                    'precios_listas' => $preciosListas,
                    'iva_porcentaje' => (float) $p->porcentaje_iva,
                    'stock' => (float) ($stockSucursal[$p->id] ?? 0),
                    'categoria_id' => $p->categoria_id,
                    'categoria_nombre' => $p->categoria?->nombre ?? 'General',
                    'unidad' => $p->unidadMedida?->codigo ?? 'UND',
                    'permite_decimales' => (bool) $p->unidadMedida?->permite_decimales,
                    'imagen_url' => $p->imagen_url,
                    'presentaciones' => $p->presentaciones->map(fn ($pr) => [
                        'id' => $pr->id,
                        'nombre' => $pr->nombre,
                        'factor' => (float) $pr->factor_conversion,
                        'precio' => (float) $pr->precio_venta > 0 ? (float) $pr->precio_venta : round((float) $p->precio_venta * (float) $pr->factor_conversion, 2),
                        'permite_decimales' => (bool) $pr->unidadMedida?->permite_decimales,
                        'predeterminada' => (bool) $pr->es_predeterminada,
                    ])->values(),
                ];
            })->values();

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->orderByDesc('es_predeterminado')
            ->orderBy('razon_social')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'razon_social' => $c->razon_social,
                'numero_documento' => $c->numero_documento,
                'es_consumidor_final' => (bool) $c->es_predeterminado,
                'lista_precio_id' => $c->lista_precio_id,
            ])->values();

        $categorias = Categoria::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('cotizaciones.create', compact('productos', 'clientes', 'categorias'));
    }

    public function store(Request $request, RegistrarCotizacionAction $action): RedirectResponse
    {
        if (Gate::denies('create', Cotizacion::class)) {
            abort(403, 'No tienes autorización para crear cotizaciones.');
        }

        $validated = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id', new BelongsToActiveCompany('clientes')],
            'fecha_validez' => ['nullable', 'date', 'after_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id', new BelongsToActiveCompany('productos')],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.presentacion_id' => ['nullable', 'integer', 'exists:producto_presentaciones,id', new BelongsToActiveCompany('producto_presentaciones')],
        ]);

        try {
            $cotizacion = $action->execute(
                empresaId: CompanyContext::getId(),
                sucursalId: BranchContext::getId() ?? auth()->user()->sucursal_id,
                userId: auth()->id(),
                items: $validated['items'],
                clienteId: $validated['cliente_id'] ?? null,
                fechaValidez: $validated['fecha_validez'] ?? null,
                observaciones: $validated['observaciones'] ?? null
            );

            return redirect()->route('cotizaciones.show', $cotizacion)
                ->with('success', "Cotización {$cotizacion->numero_cotizacion} creada correctamente.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Cotizacion $cotizacion): View
    {
        if (Gate::denies('view', $cotizacion)) {
            abort(403, 'No tienes autorización para ver esta cotización.');
        }

        $cotizacion->load(['cliente', 'sucursal', 'usuario', 'venta', 'detalles.producto.unidadMedida', 'detalles.presentacion']);

        return view('cotizaciones.show', compact('cotizacion'));
    }

    /**
     * Descarga (o muestra en el navegador con ?ver=1) el PDF de la cotización.
     */
    public function pdf(Request $request, Cotizacion $cotizacion, DocumentoPdfService $pdfService): Response
    {
        if (Gate::denies('view', $cotizacion)) {
            abort(403, 'No tienes autorización para ver esta cotización.');
        }

        $pdf = $pdfService->cotizacion($cotizacion);
        $archivo = $pdfService->nombreArchivo($cotizacion);

        return $request->boolean('ver') ? $pdf->stream($archivo) : $pdf->download($archivo);
    }

    public function anular(Cotizacion $cotizacion): RedirectResponse
    {
        if (Gate::denies('anular', $cotizacion)) {
            abort(403, 'No tienes autorización para anular esta cotización.');
        }

        if (! $cotizacion->isPendiente()) {
            return back()->with('error', 'Solo se pueden anular cotizaciones pendientes.');
        }

        $cotizacion->update(['estado' => EstadoCotizacion::ANULADA]);

        return back()->with('success', "La cotización {$cotizacion->numero_cotizacion} fue anulada.");
    }
}
