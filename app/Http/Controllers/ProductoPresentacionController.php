<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UnidadMedida;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoPresentacionController extends Controller
{
    public function index(Producto $producto): View
    {
        $presentaciones = $producto->presentaciones()->with('unidadMedida')->latest('id')->get();
        $unidades = UnidadMedida::all();

        return view('ferreteria.presentaciones.index', compact('producto', 'presentaciones', 'unidades'));
    }

    public function store(Request $request, Producto $producto): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('producto_presentaciones')->where(fn ($q) => 
                    $q->where('empresa_id', $empresaId)->where('producto_id', $producto->id)
                ),
            ],
            'unidad_medida_id' => ['nullable', 'exists:unidades_medida,id'],
            'factor_conversion' => ['required', 'numeric', 'min:0.0001'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'codigo_barras' => ['nullable', 'string', 'max:100'],
            'es_predeterminada' => ['nullable', 'boolean'],
        ]);

        $validated['empresa_id'] = $empresaId;
        $validated['producto_id'] = $producto->id;

        if (!empty($validated['es_predeterminada'])) {
            ProductoPresentacion::where('producto_id', $producto->id)->update(['es_predeterminada' => false]);
        }

        ProductoPresentacion::create($validated);

        return redirect()->route('productos.presentaciones.index', $producto)
            ->with('success', "Presentación {$validated['nombre']} configurada exitosamente.");
    }

    public function destroy(Producto $producto, ProductoPresentacion $presentacion): RedirectResponse
    {
        $presentacion->delete();

        return redirect()->route('productos.presentaciones.index', $producto)
            ->with('success', 'Presentación eliminada.');
    }
}
