<?php

namespace App\Http\Controllers;

use App\Actions\Ventas\AnularVentaAction;
use App\Actions\Ventas\RegistrarVentaAction;
use App\Enums\EstadoVenta;
use App\Enums\TipoComprobanteVenta;
use App\Enums\TipoPago;
use App\Models\Cliente;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VentaController extends Controller
{
    /**
     * Listado histórico y filtros de ventas.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Venta::class)) {
            abort(403, 'No tienes autorización para consultar ventas.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId();

        $query = Venta::with(['cliente', 'sucursal', 'usuario', 'detalles'])
            ->where('empresa_id', $empresaId);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->input('tipo_pago'));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->input('cliente_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('buscar')) {
            $termino = '%' . $request->input('buscar') . '%';
            $query->where(function ($q) use ($termino) {
                $q->where('numero_venta', 'like', $termino)
                    ->orWhereHas('cliente', fn ($c) => $c->where('razon_social', 'like', $termino));
            });
        }

        $ventas = $query->latest('fecha')->paginate(15)->withQueryString();
        $clientes = Cliente::where('empresa_id', $empresaId)->get();

        // Métricas de ventas
        $hoy = now()->toDateString();
        $mesInicio = now()->startOfMonth()->toDateString();

        $totalVentasHoy = (float) Venta::where('empresa_id', $empresaId)
            ->where('estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('fecha', $hoy)
            ->sum('total');

        $totalVentasMes = (float) Venta::where('empresa_id', $empresaId)
            ->where('estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('fecha', '>=', $mesInicio)
            ->sum('total');

        $conteoVentasHoy = Venta::where('empresa_id', $empresaId)
            ->where('estado', EstadoVenta::COMPLETADA->value)
            ->whereDate('fecha', $hoy)
            ->count();

        $ticketPromedio = $conteoVentasHoy > 0 ? ($totalVentasHoy / $conteoVentasHoy) : 0;

        return view('ventas.index', compact(
            'ventas',
            'clientes',
            'totalVentasHoy',
            'totalVentasMes',
            'conteoVentasHoy',
            'ticketPromedio'
        ));
    }

    /**
     * Ficha de detalle de una venta.
     */
    public function show(Venta $venta): View
    {
        if (CompanyContext::getId() !== $venta->empresa_id) {
            abort(404);
        }

        if (Gate::denies('view', $venta)) {
            abort(403, 'No tienes autorización para ver esta venta.');
        }

        $venta->load(['cliente', 'sucursal', 'usuario', 'anulador', 'detalles.producto.unidadMedida', 'pagos']);

        return view('ventas.show', compact('venta'));
    }

    /**
     * Endpoint transaccional para registrar una venta desde el motor.
     */
    public function store(Request $request, RegistrarVentaAction $action): RedirectResponse
    {
        if (Gate::denies('create', Venta::class)) {
            abort(403, 'No tienes autorización para registrar ventas.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? auth()->user()->sucursal_id;

        if (! $sucursalId) {
            return back()->withErrors(['error' => 'Debes tener una sucursal activa para registrar ventas.']);
        }

        $validated = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'tipo_pago' => ['required', Rule::enum(TipoPago::class)],
            'metodo_pago' => ['required', 'string'],
            'tipo_comprobante' => ['nullable', Rule::enum(TipoComprobanteVenta::class)],
            'caja_sesion_id' => ['nullable', 'exists:cajas_sesiones,id'],
            'pago_con' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.impuesto_porcentaje' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $tipoPago = TipoPago::from($validated['tipo_pago']);
            $tipoComprobante = isset($validated['tipo_comprobante'])
                ? TipoComprobanteVenta::from($validated['tipo_comprobante'])
                : TipoComprobanteVenta::TICKET;

            $venta = $action->execute(
                empresaId: $empresaId,
                sucursalId: $sucursalId,
                userId: auth()->id(),
                items: $validated['items'],
                clienteId: $validated['cliente_id'] ?? null,
                tipoPago: $tipoPago,
                metodoPago: $validated['metodo_pago'],
                tipoComprobante: $tipoComprobante,
                cajaSesionId: $validated['caja_sesion_id'] ?? null,
                pagoCon: $validated['pago_con'] ?? null,
                observaciones: $validated['observaciones'] ?? null
            );

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta {$venta->numero_venta} registrada exitosamente por $" . number_format($venta->total, 2));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Anulación formal de una venta.
     */
    public function anular(Venta $venta, Request $request, AnularVentaAction $action): RedirectResponse
    {
        if (CompanyContext::getId() !== $venta->empresa_id) {
            abort(404);
        }

        if (Gate::denies('anular', $venta)) {
            abort(403, 'No tienes autorización para anular ventas.');
        }

        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ], [
            'motivo.required' => 'El motivo de anulación es obligatorio.',
        ]);

        try {
            $action->execute($venta, auth()->user(), $validated['motivo']);

            return redirect()->route('ventas.show', $venta)
                ->with('success', "Venta {$venta->numero_venta} anulada correctamente y existencias reingresadas a inventario.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Ticket / Comprobante térmico para impresión directa.
     */
    public function ticket(Venta $venta): View
    {
        if (CompanyContext::getId() !== $venta->empresa_id) {
            abort(404);
        }

        if (Gate::denies('view', $venta)) {
            abort(403, 'No tienes autorización para consultar este comprobante.');
        }

        $venta->load(['cliente', 'sucursal', 'usuario', 'detalles.producto.unidadMedida', 'pagos']);

        return view('ventas.ticket', compact('venta'));
    }
}
