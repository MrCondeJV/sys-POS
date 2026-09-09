<?php

namespace App\Http\Controllers;

use App\Actions\Cartera\AnularAbonoCarteraAction;
use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Enums\MetodoPagoCartera;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\PagoCliente;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CarteraController extends Controller
{
    /**
     * Tablero principal y catálogo de Cuentas por Cobrar.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', CuentaPorCobrar::class)) {
            abort(403, 'No tienes autorización para acceder al módulo de cartera.');
        }

        $term = $request->input('buscar');
        $estado = $request->input('estado');
        $clienteId = $request->input('cliente_id');

        $query = CuentaPorCobrar::with(['cliente', 'sucursal'])->latest();

        if (! empty($term)) {
            $query->buscar($term);
        }

        if (! empty($clienteId)) {
            $query->where('cliente_id', $clienteId);
        }

        if (! empty($estado)) {
            if ($estado === 'VENCIDA') {
                $query->vencidas();
            } elseif ($estado === 'VIGENTE') {
                $query->vigentes();
            } elseif ($estado === 'PENDIENTES') {
                $query->pendientes();
            } elseif (in_array($estado, ['PENDIENTE', 'PARCIAL', 'PAGADA', 'ANULADA'])) {
                $query->where('estado', $estado);
            }
        }

        $cuentas = $query->paginate(15)->withQueryString();

        // Cálculo de KPIs de Cartera
        $hoy = Carbon::today()->toDateString();

        $totalCartera = (float) CuentaPorCobrar::pendientes()->sum('saldo_pendiente');
        $carteraVigente = (float) CuentaPorCobrar::pendientes()->where('fecha_vencimiento', '>=', $hoy)->sum('saldo_pendiente');
        $carteraVencida = (float) CuentaPorCobrar::pendientes()->where('fecha_vencimiento', '<', $hoy)->sum('saldo_pendiente');
        $facturasEnMora = CuentaPorCobrar::pendientes()->where('fecha_vencimiento', '<', $hoy)->count();

        $totalRecaudadoMes = (float) PagoCliente::aplicados()
            ->whereMonth('fecha_pago', now()->month)
            ->whereYear('fecha_pago', now()->year)
            ->sum('monto');

        $clientes = Cliente::activo()->orderBy('razon_social')->get();

        return view('cartera.index', compact(
            'cuentas',
            'term',
            'estado',
            'clienteId',
            'clientes',
            'totalCartera',
            'carteraVigente',
            'carteraVencida',
            'facturasEnMora',
            'totalRecaudadoMes'
        ));
    }

    /**
     * Muestra el formulario para aperturar una nueva cuenta por cobrar o saldo inicial.
     */
    public function create(): View
    {
        if (Gate::denies('create', CuentaPorCobrar::class)) {
            abort(403, 'No tienes autorización para registrar cuentas por cobrar.');
        }

        $clientes = Cliente::activo()->orderBy('razon_social')->get();

        return view('cartera.create', compact('clientes'));
    }

    /**
     * Registra una nueva cuenta por cobrar en la empresa activa.
     */
    public function store(Request $request, RegistrarCuentaPorCobrarAction $action): RedirectResponse
    {
        if (Gate::denies('create', CuentaPorCobrar::class)) {
            abort(403, 'No tienes autorización para registrar cuentas por cobrar.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId();

        $validated = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id', new BelongsToActiveCompany('clientes')],
            'concepto' => ['required', 'string', 'max:255'],
            'monto_total' => ['required', 'numeric', 'min:0.01'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_emision'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $cuenta = $action->execute(
                empresaId: $empresaId,
                sucursalId: $sucursalId,
                clienteId: (int) $validated['cliente_id'],
                montoTotal: (float) $validated['monto_total'],
                fechaEmision: $validated['fecha_emision'],
                fechaVencimiento: $validated['fecha_vencimiento'],
                concepto: $validated['concepto'],
                observaciones: $validated['observaciones'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('cartera.show', $cuenta)
                ->with('success', "Cuenta por cobrar {$cuenta->numero_documento} aperturada exitosamente.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Ficha de detalle de la cuenta por cobrar con historial de abonos.
     */
    public function show(CuentaPorCobrar $cuenta): View
    {
        if (Gate::denies('view', $cuenta)) {
            abort(403, 'No tienes autorización para ver esta cuenta por cobrar.');
        }

        $cuenta->load(['cliente', 'sucursal', 'creador', 'pagos.usuario']);

        return view('cartera.show', compact('cuenta'));
    }

    /**
     * Registra un abono o pago a una cuenta por cobrar.
     */
    public function storeAbono(
        Request $request,
        CuentaPorCobrar $cuenta,
        RegistrarAbonoCarteraAction $action
    ): RedirectResponse {
        if (Gate::denies('abonar', $cuenta)) {
            abort(403, 'No tienes autorización para abonar a esta cuenta por cobrar.');
        }

        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01', 'max:'.((float) $cuenta->saldo_pendiente + 0.01)],
            'metodo_pago' => ['required', Rule::enum(MetodoPagoCartera::class)],
            'fecha_pago' => ['required', 'date'],
            'referencia_pago' => ['nullable', 'string', 'max:100'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $pago = $action->execute(
                cuenta: $cuenta,
                monto: (float) $validated['monto'],
                metodoPago: MetodoPagoCartera::from($validated['metodo_pago']),
                fechaPago: $validated['fecha_pago'],
                referenciaPago: $validated['referencia_pago'] ?? null,
                notas: $validated['notas'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('cartera.show', $cuenta)
                ->with('success', "Abono aplicado exitosamente con el recibo {$pago->numero_recibo}.")
                ->with('recibo_id', $pago->id);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Visualización y comprobante imprimible de un Recibo de Caja.
     */
    public function showRecibo(PagoCliente $pago): View
    {
        if (Gate::denies('view', $pago)) {
            abort(403, 'No tienes autorización para ver este recibo de pago.');
        }

        $pago->load(['cuentaPorCobrar.cliente', 'cliente', 'sucursal', 'usuario', 'empresa']);

        return view('cartera.recibo', compact('pago'));
    }

    /**
     * Anula un abono registrado previamente y restablece la deuda.
     */
    public function anularAbono(PagoCliente $pago, AnularAbonoCarteraAction $action): RedirectResponse
    {
        if (Gate::denies('anular', $pago)) {
            abort(403, 'No tienes autorización para anular este abono.');
        }

        try {
            $action->execute($pago);

            return back()->with('success', "El recibo {$pago->numero_recibo} ha sido anulado y el saldo fue restituido.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra el Estado de Cuenta consolidado de un cliente.
     */
    public function estadoCuenta(Cliente $cliente): View
    {
        if (CompanyContext::getId() !== $cliente->empresa_id) {
            abort(404);
        }

        if (Gate::denies('viewAny', CuentaPorCobrar::class)) {
            abort(403, 'No tienes autorización para consultar estados de cuenta.');
        }

        $cliente->load(['cuentasPorCobrar.pagos', 'pagos.cuentaPorCobrar', 'empresa']);

        $cuentasPendientes = $cliente->cuentasPorCobrar()->pendientes()->get();
        $totalDeuda = (float) $cuentasPendientes->sum('saldo_pendiente');
        $deudaVencida = (float) $cuentasPendientes->filter(fn ($c) => $c->estaVencida())->sum('saldo_pendiente');
        $cupoTotal = (float) $cliente->cupo_credito;
        $cupoDisponible = max(0.0, $cupoTotal - $totalDeuda);
        $porcentajeUtilizado = $cupoTotal > 0 ? min(100.0, round(($totalDeuda / $cupoTotal) * 100, 1)) : 0;

        $historialPagos = $cliente->pagos()->with('cuentaPorCobrar')->latest()->take(20)->get();

        return view('cartera.estado_cuenta', compact(
            'cliente',
            'cuentasPendientes',
            'totalDeuda',
            'deudaVencida',
            'cupoTotal',
            'cupoDisponible',
            'porcentajeUtilizado',
            'historialPagos'
        ));
    }

    /**
     * Vista de impresión standalone del Estado de Cuenta (sin layout principal).
     */
    public function printEstadoCuenta(Cliente $cliente, Request $request): View
    {
        if (CompanyContext::getId() !== $cliente->empresa_id) {
            abort(404);
        }

        if (Gate::denies('viewAny', CuentaPorCobrar::class)) {
            abort(403, 'No tienes autorización para consultar estados de cuenta.');
        }

        $cliente->load(['cuentasPorCobrar.pagos', 'pagos.cuentaPorCobrar', 'empresa']);

        $cuentasPendientes = $cliente->cuentasPorCobrar()->pendientes()->get();
        $totalDeuda = (float) $cuentasPendientes->sum('saldo_pendiente');
        $deudaVencida = (float) $cuentasPendientes->filter(fn ($c) => $c->estaVencida())->sum('saldo_pendiente');
        $cupoTotal = (float) $cliente->cupo_credito;
        $cupoDisponible = max(0.0, $cupoTotal - $totalDeuda);
        $porcentajeUtilizado = $cupoTotal > 0 ? min(100.0, round(($totalDeuda / $cupoTotal) * 100, 1)) : 0;

        $historialPagos = $cliente->pagos()->with('cuentaPorCobrar')->latest()->take(50)->get();

        return view('cartera.estado_cuenta_print', compact(
            'cliente',
            'cuentasPendientes',
            'totalDeuda',
            'deudaVencida',
            'cupoTotal',
            'cupoDisponible',
            'porcentajeUtilizado',
            'historialPagos'
        ));
    }
}
