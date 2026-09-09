<?php

namespace App\Http\Controllers;

use App\Actions\Caja\AbrirCajaAction;
use App\Actions\Caja\CerrarCajaAction;
use App\Actions\Caja\RegistrarMovimientoCajaAction;
use App\Enums\EstadoCaja;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoMovimientoCaja;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Models\Sucursal;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CajaController extends Controller
{
    /**
     * Tablero general de cajas y turnos.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Caja::class)) {
            abort(403, 'No tienes autorización para acceder al módulo de cajas.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId();

        $query = Caja::with([
            'sucursal',
            'sesionActual.cajero',
            'sesionActual.movimientos',
        ]);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        $cajas = $query->latest()->get();
        $sucursales = Sucursal::where('empresa_id', $empresaId)->get();

        // Métricas rápidas
        $totalCajas = $cajas->count();
        $cajasAbiertas = $cajas->filter(fn ($c) => $c->sesionActual !== null)->count();
        $cajasCerradas = $totalCajas - $cajasAbiertas;

        $totalDineroEnCaja = $cajas->reduce(function ($carry, $caja) {
            if ($caja->sesionActual) {
                return $carry + $caja->sesionActual->calcularSaldoEsperadoEfectivo();
            }
            return $carry;
        }, 0.0);

        return view('cajas.index', compact(
            'cajas',
            'sucursales',
            'totalCajas',
            'cajasAbiertas',
            'cajasCerradas',
            'totalDineroEnCaja'
        ));
    }

    /**
     * Registrar una nueva caja en la sucursal.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Caja::class)) {
            abort(403, 'No tienes permiso para registrar nuevas cajas.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cajas', 'codigo')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'sucursal_id' => [
                'required',
                new BelongsToActiveCompany('sucursales'),
            ],
        ], [
            'nombre.required' => 'El nombre de la caja es obligatorio.',
            'codigo.required' => 'El código de la caja es obligatorio.',
            'codigo.unique' => 'Ya existe una caja con este código en tu empresa.',
            'sucursal_id.required' => 'Debes seleccionar una sucursal.',
        ]);

        Caja::create([
            'empresa_id' => $empresaId,
            'sucursal_id' => $validated['sucursal_id'],
            'nombre' => $validated['nombre'],
            'codigo' => strtoupper($validated['codigo']),
            'estado' => EstadoCaja::ACTIVA,
        ]);

        return redirect()->route('cajas.index')
            ->with('success', 'Caja registrada exitosamente.');
    }

    /**
     * Detalle e historial de turnos de una caja.
     */
    public function show(Caja $caja): View
    {
        if (Gate::denies('view', $caja)) {
            abort(403, 'No tienes autorización para ver esta caja.');
        }

        $caja->load(['sucursal', 'sesionActual.cajero', 'sesionActual.movimientos.usuario']);

        $sesiones = $caja->sesiones()
            ->with(['cajero', 'cajeroCierre'])
            ->withCount('movimientos')
            ->latest('fecha_apertura')
            ->paginate(15);

        return view('cajas.show', compact('caja', 'sesiones'));
    }

    /**
     * Abrir turno en una caja con fondo inicial.
     */
    public function abrir(Caja $caja, Request $request, AbrirCajaAction $action): RedirectResponse
    {
        if (Gate::denies('abrir', $caja)) {
            abort(403, 'No tienes autorización para abrir turnos de caja.');
        }

        $validated = $request->validate([
            'monto_apertura' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'monto_apertura.required' => 'El monto de apertura es obligatorio.',
            'monto_apertura.min' => 'El monto de apertura no puede ser negativo.',
        ]);

        try {
            $action->execute(
                $caja,
                auth()->user(),
                (float) $validated['monto_apertura'],
                $validated['observaciones'] ?? null
            );

            $targetRoute = $request->input('redirect_to') === 'pos' ? 'pos.index' : 'cajas.index';

            return redirect()->route($targetRoute)
                ->with('success', "Turno abierto exitosamente en {$caja->nombre} con fondo de $" . number_format($validated['monto_apertura'], 2));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Registrar un ingreso o egreso de dinero manual.
     */
    public function movimiento(Caja $caja, Request $request, RegistrarMovimientoCajaAction $action): RedirectResponse
    {
        if (Gate::denies('movimiento', $caja)) {
            abort(403, 'No tienes autorización para registrar movimientos en esta caja.');
        }

        $sesion = $caja->sesionActual;
        if (! $sesion) {
            return back()->withErrors(['error' => 'La caja debe tener un turno abierto para registrar movimientos.']);
        }

        $validated = $request->validate([
            'tipo' => ['required', Rule::enum(TipoMovimientoCaja::class)],
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'string', 'max:30'],
            'comprobante' => ['nullable', 'string', 'max:100'],
        ], [
            'tipo.required' => 'El tipo de movimiento es obligatorio.',
            'concepto.required' => 'El concepto o motivo es obligatorio.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a cero.',
        ]);

        try {
            $tipo = TipoMovimientoCaja::from($validated['tipo']);

            $action->execute(
                $sesion,
                $tipo,
                $validated['concepto'],
                (float) $validated['monto'],
                $validated['metodo_pago'],
                $validated['comprobante'] ?? null,
                auth()->user()
            );

            return back()->with('success', "Movimiento ({$tipo->label()}) por $" . number_format($validated['monto'], 2) . ' registrado correctamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Pantalla de arqueo y preparación de cierre de caja.
     */
    public function cierre(Caja $caja): View|RedirectResponse
    {
        if (Gate::denies('cerrar', $caja)) {
            abort(403, 'No tienes autorización para cerrar turnos de caja.');
        }

        $sesion = $caja->sesionActual;
        if (! $sesion) {
            return redirect()->route('cajas.index')
                ->withErrors(['error' => 'Esta caja no tiene un turno abierto actualmente.']);
        }

        $sesion->load(['movimientos.usuario', 'cajero']);

        $montoInicial = (float) $sesion->monto_apertura;
        $saldoEsperadoEfectivo = $sesion->calcularSaldoEsperadoEfectivo();

        $ingresosPorMetodo = $sesion->movimientos
            ->where('tipo', TipoMovimientoCaja::INGRESO)
            ->groupBy('metodo_pago')
            ->map(fn ($group) => $group->sum('monto'));

        $egresosPorMetodo = $sesion->movimientos
            ->where('tipo', TipoMovimientoCaja::EGRESO)
            ->groupBy('metodo_pago')
            ->map(fn ($group) => $group->sum('monto'));

        return view('cajas.cierre', compact(
            'caja',
            'sesion',
            'montoInicial',
            'saldoEsperadoEfectivo',
            'ingresosPorMetodo',
            'egresosPorMetodo'
        ));
    }

    /**
     * Procesa el arqueo y cierra la caja formalmente.
     */
    public function storeCierre(Caja $caja, Request $request, CerrarCajaAction $action): RedirectResponse
    {
        if (Gate::denies('cerrar', $caja)) {
            abort(403, 'No tienes autorización para cerrar turnos de caja.');
        }

        $sesion = $caja->sesionActual;
        if (! $sesion) {
            return redirect()->route('cajas.index')
                ->withErrors(['error' => 'La caja no cuenta con un turno abierto para cerrar.']);
        }

        $validated = $request->validate([
            'monto_cierre_contado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'monto_cierre_contado.required' => 'Debes ingresar el monto físico contado en el arqueo.',
            'monto_cierre_contado.min' => 'El monto contado no puede ser negativo.',
        ]);

        try {
            $sesionCerrada = $action->execute(
                $sesion,
                auth()->user(),
                (float) $validated['monto_cierre_contado'],
                $validated['observaciones'] ?? null
            );

            return redirect()->route('cajas.comprobante', $sesionCerrada)
                ->with('success', 'Turno de caja cerrado exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Comprobante imprimible del reporte Z / Arqueo de cierre.
     */
    public function comprobante(CajaSesion $sesion): View
    {
        if (CompanyContext::getId() !== $sesion->empresa_id) {
            abort(404);
        }

        if (Gate::denies('viewAny', Caja::class)) {
            abort(403, 'No tienes autorización para consultar comprobantes de caja.');
        }

        $sesion->load(['caja.sucursal', 'cajero', 'cajeroCierre', 'empresa', 'movimientos.usuario']);

        return view('cajas.comprobante_cierre', compact('sesion'));
    }
}
