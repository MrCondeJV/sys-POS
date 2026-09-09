<?php

namespace App\Http\Controllers;

use App\Enums\EstadoResolucionFacturacion;
use App\Models\ResolucionFacturacion;
use App\Models\Sucursal;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ResolucionFacturacionController extends Controller
{
    public function index(): View
    {
        $resoluciones = ResolucionFacturacion::with('sucursal')
            ->latest('id')
            ->paginate(15);

        return view('facturacion-electronica.resoluciones.index', compact('resoluciones'));
    }

    public function create(): View
    {
        $sucursales = Sucursal::all();
        $estados = EstadoResolucionFacturacion::cases();

        return view('facturacion-electronica.resoluciones.create', compact('sucursales', 'estados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'numero_resolucion' => ['required', 'string', 'max:60'],
            'prefijo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('resoluciones_facturacion')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId)->where('numero_resolucion', $request->numero_resolucion)),
            ],
            'rango_desde' => ['required', 'integer', 'min:1'],
            'rango_hasta' => ['required', 'integer', 'gt:rango_desde'],
            'consecutivo_actual' => ['nullable', 'integer', 'gte:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vigencia' => ['required', 'date', 'after:fecha_inicio'],
            'clave_tecnica' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::enum(EstadoResolucionFacturacion::class)],
            'es_predeterminada' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['es_predeterminada'])) {
            ResolucionFacturacion::where('empresa_id', $empresaId)->update(['es_predeterminada' => false]);
        }

        $validated['consecutivo_actual'] = $validated['consecutivo_actual'] ?? ($validated['rango_desde'] - 1);
        $validated['empresa_id'] = $empresaId;

        ResolucionFacturacion::create($validated);

        return redirect()->route('resoluciones.index')->with('success', 'Resolución de facturación registrada exitosamente.');
    }

    public function edit(ResolucionFacturacion $resolucione): View
    {
        $resolucion = $resolucione;
        $sucursales = Sucursal::all();
        $estados = EstadoResolucionFacturacion::cases();

        return view('facturacion-electronica.resoluciones.edit', compact('resolucion', 'sucursales', 'estados'));
    }

    public function update(Request $request, ResolucionFacturacion $resolucione): RedirectResponse
    {
        $resolucion = $resolucione;
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'numero_resolucion' => ['required', 'string', 'max:60'],
            'prefijo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('resoluciones_facturacion')
                    ->ignore($resolucion->id)
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId)->where('numero_resolucion', $request->numero_resolucion)),
            ],
            'rango_desde' => ['required', 'integer', 'min:1'],
            'rango_hasta' => ['required', 'integer', 'gt:rango_desde'],
            'consecutivo_actual' => ['required', 'integer', 'gte:0'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_vigencia' => ['required', 'date', 'after:fecha_inicio'],
            'clave_tecnica' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::enum(EstadoResolucionFacturacion::class)],
            'es_predeterminada' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['es_predeterminada'])) {
            ResolucionFacturacion::where('empresa_id', $empresaId)
                ->where('id', '!=', $resolucion->id)
                ->update(['es_predeterminada' => false]);
        }

        $resolucion->update($validated);

        return redirect()->route('resoluciones.index')->with('success', 'Resolución de facturación actualizada.');
    }

    public function destroy(ResolucionFacturacion $resolucione): RedirectResponse
    {
        $resolucione->delete();

        return redirect()->route('resoluciones.index')->with('success', 'Resolución eliminada.');
    }
}
