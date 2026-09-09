<?php

namespace App\Http\Controllers;

use App\Actions\Devoluciones\RegistrarDevolucionAction;
use App\Enums\EstadoSesionCaja;
use App\Enums\TipoReintegroDevolucion;
use App\Models\CajaSesion;
use App\Models\Devolucion;
use App\Models\Venta;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DevolucionController extends Controller
{
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Devolucion::class)) {
            abort(403, 'No tienes autorización para ver devoluciones.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId();

        $query = Devolucion::where('empresa_id', $empresaId)
            ->with(['venta.cliente', 'usuario', 'sucursal']);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero_devolucion', 'like', "%{$search}%")
                  ->orWhereHas('venta', function ($v) use ($search) {
                      $v->where('numero_venta', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $devoluciones = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $totalDevueltoHoy = Devolucion::where('empresa_id', $empresaId)
            ->whereDate('created_at', today())
            ->sum('total');

        $conteoTotal = Devolucion::where('empresa_id', $empresaId)->count();

        return view('devoluciones.index', compact('devoluciones', 'totalDevueltoHoy', 'conteoTotal'));
    }

    public function create(Venta $venta): View
    {
        if (Gate::denies('create', Devolucion::class)) {
            abort(403, 'No tienes autorización para registrar devoluciones.');
        }

        if (Gate::denies('view', $venta)) {
            abort(403, 'No tienes autorización para ver esta venta.');
        }

        $empresaId = CompanyContext::getId();
        $sucursalId = BranchContext::getId() ?? $venta->sucursal_id;

        $venta->load(['detalles.producto', 'cliente', 'sucursal']);

        // Buscar caja abierta en caso de reintegro en efectivo
        $sesionCaja = CajaSesion::where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->latest('fecha_apertura')
            ->first();

        return view('devoluciones.create', compact('venta', 'sesionCaja'));
    }

    public function store(Request $request, Venta $venta, RegistrarDevolucionAction $action): RedirectResponse
    {
        if (Gate::denies('create', Devolucion::class)) {
            abort(403, 'No tienes autorización para registrar devoluciones.');
        }

        if (Gate::denies('view', $venta)) {
            abort(403, 'No tienes autorización para acceder a esta venta.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'caja_sesion_id' => [
                'nullable',
                Rule::exists('cajas_sesiones', 'id')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'tipo_reintegro' => ['required', Rule::enum(TipoReintegroDevolucion::class)],
            'motivo' => ['required', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.venta_detalle_id' => ['required', 'exists:venta_detalles,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0'],
            'items.*.reingresa_inventario' => ['nullable', 'boolean'],
        ], [
            'motivo.required' => 'Debes indicar el motivo de la devolución.',
            'items.required' => 'Debes seleccionar items a devolver.',
        ]);

        // Filtrar items con cantidad > 0
        $itemsParaDevolver = array_filter($validated['items'], fn ($i) => (float) $i['cantidad'] > 0);

        if (empty($itemsParaDevolver)) {
            return back()->withErrors(['items' => 'Debes ingresar una cantidad mayor a 0 en al menos un producto.'])->withInput();
        }

        try {
            $tipoReintegro = TipoReintegroDevolucion::from($validated['tipo_reintegro']);
            $cajaSesionId = ! empty($validated['caja_sesion_id']) ? (int) $validated['caja_sesion_id'] : null;

            $devolucion = $action->execute(
                venta: $venta,
                userId: auth()->id(),
                items: $itemsParaDevolver,
                tipoReintegro: $tipoReintegro,
                cajaSesionId: $cajaSesionId,
                motivo: $validated['motivo']
            );

            return redirect()->route('devoluciones.show', $devolucion)
                ->with('success', "Devolución {$devolucion->numero_devolucion} procesada exitosamente por un total de $" . number_format($devolucion->total, 2));

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Devolucion $devolucion): View
    {
        if (Gate::denies('view', $devolucion)) {
            abort(403, 'No tienes autorización para ver esta devolución.');
        }

        $devolucion->load([
            'venta.cliente',
            'sucursal',
            'usuario',
            'cajaSesion.caja',
            'detalles.producto',
        ]);

        return view('devoluciones.show', compact('devolucion'));
    }

    public function comprobante(Devolucion $devolucion): View
    {
        if (Gate::denies('view', $devolucion)) {
            abort(403, 'No tienes autorización para ver esta devolución.');
        }

        $devolucion->load([
            'venta.cliente',
            'sucursal.empresa',
            'usuario',
            'detalles.producto',
        ]);

        return view('devoluciones.comprobante', compact('devolucion'));
    }
}
