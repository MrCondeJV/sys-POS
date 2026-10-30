<?php

namespace App\Http\Controllers;

use App\Rules\BelongsToActiveCompany;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCotizacion;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoPagoCartera;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CuentaPorCobrar;
use App\Models\Inventario;
use App\Models\Producto;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Interfaz interactiva del Punto de Venta (POS).
     */
    public function index(Request $request): View
    {
        if (Gate::denies('create', \App\Models\Venta::class)) {
            abort(403, 'No tienes autorización para acceder a la terminal de punto de venta.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        // Verificar si existe una caja abierta en la sucursal operada por el usuario o activa
        $sesionCaja = CajaSesion::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->where(function ($q) {
                $q->where('user_id', auth()->id())
                    ->orWhereNull('user_id');
            })
            ->latest('fecha_apertura')
            ->first();

        // Si no hay sesión propia, buscar cualquier sesión abierta en la sucursal
        if (!$sesionCaja) {
            $sesionCaja = CajaSesion::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', EstadoSesionCaja::ABIERTA->value)
                ->latest('fecha_apertura')
                ->first();
        }

        // Si no hay ninguna caja abierta en la sucursal, mostrar pantalla para apertura rápida
        if (!$sesionCaja) {
            $cajasDisponibles = Caja::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->where('estado', EstadoCaja::ACTIVA->value)
                ->get();

            return view('pos.sin_caja', compact('cajasDisponibles'));
        }

        // Cargar Catálogo de Productos activos de la empresa. Se parte de Producto (y no de Inventario)
        // para que los productos sin registro de existencias en la sucursal también aparezcan (stock 0)
        // y para que los productos eliminados (soft delete) queden excluidos.
        $stockSucursal = Inventario::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->pluck('stock', 'producto_id');

        $catalogo = Producto::where('empresa_id', $empresaId)
            ->activo()
            ->with(['categoria', 'unidadMedida', 'impuesto', 'presentaciones' => fn ($q) => $q->where('estado', EstadoGeneral::ACTIVO->value)->with('unidadMedida')])
            ->orderBy('nombre')
            ->get();

        // Listas de Precios Activas
        $listasPrecios = \App\Models\ListaPrecio::where('empresa_id', $empresaId)
            ->where('estado', EstadoGeneral::ACTIVO->value)
            ->with('detalles')
            ->get();

        $productos = $catalogo->map(function ($p) use ($listasPrecios, $stockSucursal) {
            $preciosListas = [];
            foreach ($listasPrecios as $lp) {
                $preciosListas[$lp->id] = $lp->calcularPrecio($p);
            }

            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo' => $p->codigo ?? '',
                'codigo_barras' => $p->codigo_barras ?? '',
                'precio_venta' => (float) $p->precio_venta,
                'precios_listas' => $preciosListas,
                'stock' => (float) ($stockSucursal[$p->id] ?? 0),
                'categoria_id' => $p->categoria_id,
                'categoria_nombre' => $p->categoria?->nombre ?? 'General',
                'unidad' => $p->unidadMedida?->codigo ?? 'UND',
                'permite_decimales' => (bool) $p->unidadMedida?->permite_decimales,
                'iva_porcentaje' => $p->porcentaje_iva,
                'imagen_url' => $p->imagen_url,
                // Venta por medida: cómo más se puede vender (ej. Caja = 1,44 m²). El precio de la presentación no usa listas.
                'presentaciones' => $p->presentaciones->map(fn ($pr) => [
                    'id' => $pr->id,
                    'nombre' => $pr->nombre,
                    'factor' => (float) $pr->factor_conversion,
                    'precio' => (float) $pr->precio_venta > 0 ? (float) $pr->precio_venta : round((float) $p->precio_venta * (float) $pr->factor_conversion, 2),
                    'codigo_barras' => $pr->codigo_barras ?? '',
                    'permite_decimales' => (bool) $pr->unidadMedida?->permite_decimales,
                    'predeterminada' => (bool) $pr->es_predeterminada,
                ])->values(),
            ];
        })->values();

        // Clientes
        $clientes = Cliente::where('empresa_id', $empresaId)
            ->orderByDesc('es_predeterminado')
            ->orderBy('razon_social')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'razon_social' => $c->razon_social,
                    'numero_documento' => $c->numero_documento,
                    'tipo_documento' => $c->tipo_documento->value,
                    'es_consumidor_final' => (bool) $c->es_predeterminado,
                    'lista_precio_id' => $c->lista_precio_id,
                    'plazo_dias' => $c->plazo_dias,
                ];
            });

        // Categorías para filtrado rápido
        $categorias = Categoria::where('empresa_id', $empresaId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Conversión de cotización: precarga cliente e ítems en el carrito (?cotizacion=ID)
        $cotizacion = null;
        if ($request->filled('cotizacion')) {
            $modelo = Cotizacion::with('detalles')->find($request->integer('cotizacion'));

            if ($modelo && Gate::allows('view', $modelo) && $modelo->isPendiente()) {
                $cotizacion = [
                    'id' => $modelo->id,
                    'numero' => $modelo->numero_cotizacion,
                    'cliente_id' => $modelo->cliente_id,
                    'total' => (float) $modelo->total,
                    'vencida' => $modelo->estaVencida(),
                    'items' => $modelo->detalles->map(fn ($d) => [
                        'producto_id' => $d->producto_id,
                        'presentacion_id' => $d->presentacion_id,
                        'descripcion' => $d->descripcion,
                        'cantidad' => (float) $d->cantidad,
                        'descuento' => (float) $d->descuento,
                    ])->values(),
                ];
            }
        }

        return view('pos.index', compact(
            'sesionCaja',
            'productos',
            'clientes',
            'categorias',
            'listasPrecios',
            'cotizacion'
        ));
    }

    /**
     * Resumen de crédito del cliente para el POS: deuda pendiente, facturas abiertas e historial de abonos.
     * El cobro de los abonos se realiza en el módulo de Cartera.
     */
    public function carteraCliente(Cliente $cliente): JsonResponse
    {
        if (CompanyContext::getId() !== $cliente->empresa_id) {
            abort(404);
        }

        if (Gate::denies('viewAny', CuentaPorCobrar::class)) {
            return response()->json(['error' => 'No tienes autorización para consultar la cartera de clientes.'], 403);
        }

        $cuentasPendientes = $cliente->cuentasPorCobrar()->pendientes()->orderBy('fecha_emision')->get();
        $totalDeuda = (float) $cuentasPendientes->sum('saldo_pendiente');

        $abonos = $cliente->pagos()->with('cuentaPorCobrar')->take(10)->get();

        return response()->json([
            'cliente_id' => $cliente->id,
            'total_deuda' => $totalDeuda,
            'deuda_vencida' => (float) $cuentasPendientes->filter(fn ($c) => $c->estaVencida())->sum('saldo_pendiente'),
            'cuentas' => $cuentasPendientes->map(fn ($c) => [
                'id' => $c->id,
                'numero_documento' => $c->numero_documento,
                'concepto' => $c->concepto,
                'monto_total' => (float) $c->monto_total,
                'saldo_pendiente' => (float) $c->saldo_pendiente,
                'fecha_emision' => $c->fecha_emision->format('d/m/Y'),
                'dias_antiguedad' => $c->diasAntiguedad(),
                'fecha_vencimiento' => $c->fecha_vencimiento?->format('d/m/Y'),
                'vencida' => $c->estaVencida(),
                'estado' => $c->estado->label(),
                'url' => route('cartera.show', $c),
            ])->values(),
            'abonos' => $abonos->map(fn ($p) => [
                'id' => $p->id,
                'numero_recibo' => $p->numero_recibo,
                'monto' => (float) $p->monto,
                'metodo_pago' => $p->metodo_pago->label(),
                'fecha_pago' => $p->fecha_pago?->format('d/m/Y'),
                'documento' => $p->cuentaPorCobrar?->numero_documento,
                'anulado' => $p->estado === EstadoPagoCartera::ANULADO,
                'url' => route('cartera.recibo', $p),
            ])->values(),
            'estado_cuenta_url' => route('cartera.estado-cuenta', $cliente),
        ]);
    }

    /**
     * Procesa la venta enviada desde el frontend POS (vía AJAX / JSON).
     */
    public function procesar(Request $request, RegistrarVentaAction $action): JsonResponse
    {
        if (Gate::denies('create', \App\Models\Venta::class)) {
            return response()->json(['error' => 'No tienes autorización para registrar ventas.'], 403);
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        $validated = $request->validate([
            'caja_sesion_id' => ['required', 'exists:cajas_sesiones,id', new BelongsToActiveCompany('cajas_sesiones')],
            'cliente_id' => ['nullable', 'exists:clientes,id', new BelongsToActiveCompany('clientes')],
            'lista_precio_id' => ['nullable', 'exists:listas_precios,id', new BelongsToActiveCompany('listas_precios')],
            'tipo_pago' => ['required', 'in:CONTADO,CREDITO'],
            'metodo_pago' => ['required', 'string'],
            'tipo_comprobante' => ['nullable', 'in:TICKET,FACTURA,NOTA_VENTA'],
            'pago_con' => ['nullable', 'numeric', 'min:0'],
            'pagos' => ['nullable', 'array'],
            'pagos.*.metodo_pago' => ['required', 'string'],
            'pagos.*.monto' => ['required', 'numeric', 'min:0.01'],
            'pagos.*.referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'motivo_descuento' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id', new BelongsToActiveCompany('productos')],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.impuesto_porcentaje' => ['nullable', 'numeric', 'min:0'],
            'items.*.presentacion_id' => ['nullable', 'integer', 'exists:producto_presentaciones,id', new BelongsToActiveCompany('producto_presentaciones')],
            'cotizacion_id' => ['nullable', 'integer', 'exists:cotizaciones,id', new BelongsToActiveCompany('cotizaciones')],
        ]);

        try {
            $tipoPago = TipoPago::from($validated['tipo_pago']);
            $tipoComprobante = isset($validated['tipo_comprobante'])
                ? TipoComprobanteVenta::from($validated['tipo_comprobante'])
                : TipoComprobanteVenta::TICKET;

            // La venta y el cierre de la cotización van en la misma transacción
            $venta = DB::transaction(function () use ($action, $validated, $empresaId, $sucursalId, $tipoPago, $tipoComprobante) {
                $cotizacion = null;
                if (! empty($validated['cotizacion_id'])) {
                    $cotizacion = Cotizacion::lockForUpdate()->findOrFail($validated['cotizacion_id']);
                    if (! $cotizacion->isPendiente()) {
                        throw new InvalidArgumentException("La cotización {$cotizacion->numero_cotizacion} ya fue convertida o anulada.");
                    }
                }

                $venta = $action->execute(
                    empresaId: $empresaId,
                    sucursalId: $sucursalId,
                    userId: auth()->id(),
                    items: $validated['items'],
                    clienteId: $validated['cliente_id'] ?? null,
                    tipoPago: $tipoPago,
                    metodoPago: $validated['metodo_pago'],
                    tipoComprobante: $tipoComprobante,
                    cajaSesionId: (int) $validated['caja_sesion_id'],
                    pagoCon: isset($validated['pago_con']) ? (float) $validated['pago_con'] : null,
                    pagos: $validated['pagos'] ?? null,
                    observaciones: $validated['observaciones'] ?? null,
                    listaPrecioId: isset($validated['lista_precio_id']) ? (int) $validated['lista_precio_id'] : null,
                    descuentoGlobal: (float) ($validated['descuento_global'] ?? 0),
                    motivoDescuento: $validated['motivo_descuento'] ?? null
                );

                $cotizacion?->update([
                    'estado' => EstadoCotizacion::CONVERTIDA,
                    'venta_id' => $venta->id,
                    'fecha_conversion' => now(),
                ]);

                return $venta;
            });

            // Existencias frescas de la sucursal (incluye lo vendido por otras cajas) para refrescar el catálogo del POS
            $stocks = Inventario::where('empresa_id', $empresaId)
                ->where('sucursal_id', $sucursalId)
                ->pluck('stock', 'producto_id')
                ->map(fn ($stock) => (float) $stock);

            return response()->json([
                'success' => true,
                'stocks' => $stocks,
                'venta_id' => $venta->id,
                'numero_venta' => $venta->numero_venta,
                'total' => (float) $venta->total,
                'cambio' => (float) $venta->cambio,
                'ticket_url' => route('ventas.ticket', $venta),
                'show_url' => route('ventas.show', $venta),
                'message' => "¡Venta {$venta->numero_venta} registrada con éxito!",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
