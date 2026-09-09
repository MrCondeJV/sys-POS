<?php

namespace App\Http\Controllers;

use App\Actions\Multisucursal\DespacharTrasladoAction;
use App\Actions\Multisucursal\RecibirTrasladoAction;
use App\Actions\Multisucursal\RechazarTrasladoAction;
use App\Enums\EstadoTraslado;
use App\Exceptions\StockInsuficienteException;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\TrasladoSucursal;
use App\Rules\BelongsToActiveCompany;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TrasladoSucursalController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = CompanyContext::getId();

        $query = TrasladoSucursal::with(['sucursalOrigen', 'sucursalDestino', 'usuarioDespacho', 'usuarioReceptor'])
            ->where('empresa_id', $empresaId)
            ->latest('id');

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('origen_id')) {
            $query->where('sucursal_origen_id', $request->input('origen_id'));
        }

        if ($request->filled('destino_id')) {
            $query->where('sucursal_destino_id', $request->input('destino_id'));
        }

        $traslados = $query->paginate(15)->withQueryString();
        $sucursales = Sucursal::activa()->get();
        $estados = EstadoTraslado::cases();

        return view('traslados.index', compact('traslados', 'sucursales', 'estados'));
    }

    public function create(Request $request): View
    {
        $sucursales = Sucursal::activa()->get();
        $sucursalOrigenId = BranchContext::getId() ?: $sucursales->first()?->id;

        $productos = Producto::activo()
            ->with(['inventarios' => fn($q) => $q->where('sucursal_id', $sucursalOrigenId), 'lotes'])
            ->orderBy('nombre')
            ->get();

        return view('traslados.create', compact('sucursales', 'productos', 'sucursalOrigenId'));
    }

    public function store(Request $request, DespacharTrasladoAction $action): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'sucursal_origen_id' => ['required', 'integer', new BelongsToActiveCompany('sucursales')],
            'sucursal_destino_id' => [
                'required',
                'integer',
                'different:sucursal_origen_id',
                new BelongsToActiveCompany('sucursales'),
            ],
            'motivo' => ['required', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', new BelongsToActiveCompany('productos')],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.lote_id' => ['nullable', 'integer'],
            'items.*.observaciones' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $traslado = $action->execute(
                sucursalOrigenId: (int) $validated['sucursal_origen_id'],
                sucursalDestinoId: (int) $validated['sucursal_destino_id'],
                motivo: $validated['motivo'],
                items: $validated['items'],
                observaciones: $validated['observaciones'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('traslados.show', $traslado)
                ->with('success', "Traslado {$traslado->consecutivo} despachado exitosamente en estado EN TRÁNSITO.");
        } catch (StockInsuficienteException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function show(TrasladoSucursal $traslado): View
    {
        $empresaId = CompanyContext::getId();
        if ($traslado->empresa_id !== $empresaId) {
            abort(404, 'Traslado no encontrado.');
        }

        $traslado->load(['detalles.producto', 'detalles.lote', 'sucursalOrigen', 'sucursalDestino', 'usuarioDespacho', 'usuarioReceptor']);

        return view('traslados.show', compact('traslado'));
    }

    public function recibir(Request $request, TrasladoSucursal $traslado, RecibirTrasladoAction $action): RedirectResponse
    {
        $empresaId = CompanyContext::getId();
        if ($traslado->empresa_id !== $empresaId) {
            abort(404, 'Traslado no encontrado.');
        }

        $validated = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $action->execute(
                traslado: $traslado,
                observacionesRecepcion: $validated['observaciones'] ?? null,
                userId: auth()->id()
            );

            return redirect()->route('traslados.show', $traslado)
                ->with('success', "Traslado {$traslado->consecutivo} recibido a satisfacción. Inventario ingresado a destino.");
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    public function rechazar(Request $request, TrasladoSucursal $traslado, RechazarTrasladoAction $action): RedirectResponse
    {
        $empresaId = CompanyContext::getId();
        if ($traslado->empresa_id !== $empresaId) {
            abort(404, 'Traslado no encontrado.');
        }

        $validated = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:255'],
        ]);

        try {
            $action->execute(
                traslado: $traslado,
                motivoRechazo: $validated['motivo_rechazo'],
                userId: auth()->id()
            );

            return redirect()->route('traslados.show', $traslado)
                ->with('success', "Traslado {$traslado->consecutivo} rechazado y reversado hacia el origen.");
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }
}
