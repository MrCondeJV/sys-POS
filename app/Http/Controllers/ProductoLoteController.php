<?php

namespace App\Http\Controllers;

use App\Enums\EstadoLote;
use App\Models\Producto;
use App\Models\ProductoLote;
use App\Models\Sucursal;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoLoteController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProductoLote::with(['producto', 'sucursal'])->latest('id');

        if ($request->filled('filtro')) {
            if ($request->filtro === 'vencidos') {
                $query->vencidos();
            } elseif ($request->filtro === 'proximos') {
                $query->proximosAVencer(30);
            } elseif ($request->filtro === 'disponibles') {
                $query->disponibles();
            }
        }

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('numero_lote', 'like', "%{$q}%")
                  ->orWhereHas('producto', fn ($p) => $p->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%"));
            });
        }

        $lotes = $query->paginate(20);
        $sucursales = Sucursal::all();

        return view('farmacia.lotes.index', compact('lotes', 'sucursales'));
    }

    public function create(): View
    {
        $productos = Producto::where('estado', \App\Enums\EstadoGeneral::ACTIVO)->get();
        $sucursales = Sucursal::all();

        return view('farmacia.lotes.create', compact('productos', 'sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'producto_id' => ['required', 'exists:productos,id'],
            'numero_lote' => [
                'required', 'string', 'max:60',
                Rule::unique('producto_lotes')->where(fn ($q) => 
                    $q->where('empresa_id', $empresaId)
                      ->where('sucursal_id', $request->sucursal_id)
                      ->where('producto_id', $request->producto_id)
                ),
            ],
            'fecha_fabricacion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['required', 'date'],
            'stock_inicial' => ['required', 'numeric', 'min:0'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['empresa_id'] = $empresaId;
        $validated['stock_actual'] = $validated['stock_inicial'];

        $lote = ProductoLote::create($validated);
        $lote->actualizarEstadoAutomatico();

        return redirect()->route('lotes.index')->with('success', 'Lote de medicamento registrado exitosamente.');
    }

    public function destroy(ProductoLote $lote): RedirectResponse
    {
        $lote->delete();

        return redirect()->route('lotes.index')->with('success', 'Lote eliminado.');
    }
}
