<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\PermisoSistema;
use App\Enums\TipoImpuesto;
use App\Models\Impuesto;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ImpuestoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Impuesto::class);

        $impuestos = Impuesto::orderByDesc('por_defecto')
            ->orderBy('nombre')
            ->paginate(15);

        return view('impuestos.index', compact('impuestos'));
    }

    public function create(): View
    {
        Gate::authorize('create', Impuesto::class);

        $tipos = TipoImpuesto::cases();
        $estados = EstadoGeneral::cases();

        return view('impuestos.create', compact('tipos', 'estados'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Impuesto::class);

        $empresaId = CompanyContext::getId() ?: $request->user()->empresa_id;

        $validated = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('impuestos')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::enum(TipoImpuesto::class)],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'por_defecto' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $porDefecto = $request->boolean('por_defecto');

        if ($porDefecto) {
            Impuesto::where('empresa_id', $empresaId)->update(['por_defecto' => false]);
        }

        Impuesto::create([
            'empresa_id' => $empresaId,
            'codigo' => strtoupper(trim($validated['codigo'])),
            'nombre' => trim($validated['nombre']),
            'tipo' => $validated['tipo'],
            'porcentaje' => (float) $validated['porcentaje'],
            'por_defecto' => $porDefecto,
            'estado' => $validated['estado'],
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return redirect()->route('impuestos.index')
            ->with('success', 'Impuesto configurado exitosamente.');
    }

    public function edit(Impuesto $impuesto): View
    {
        Gate::authorize('update', $impuesto);

        $tipos = TipoImpuesto::cases();
        $estados = EstadoGeneral::cases();

        return view('impuestos.edit', compact('impuesto', 'tipos', 'estados'));
    }

    public function update(Request $request, Impuesto $impuesto): RedirectResponse
    {
        Gate::authorize('update', $impuesto);

        $empresaId = $impuesto->empresa_id;

        $validated = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('impuestos')->where(fn ($query) => $query->where('empresa_id', $empresaId))->ignore($impuesto->id),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', Rule::enum(TipoImpuesto::class)],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'por_defecto' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        $porDefecto = $request->boolean('por_defecto');

        if ($porDefecto && ! $impuesto->por_defecto) {
            Impuesto::where('empresa_id', $empresaId)->update(['por_defecto' => false]);
        }

        $impuesto->update([
            'codigo' => strtoupper(trim($validated['codigo'])),
            'nombre' => trim($validated['nombre']),
            'tipo' => $validated['tipo'],
            'porcentaje' => (float) $validated['porcentaje'],
            'por_defecto' => $porDefecto,
            'estado' => $validated['estado'],
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return redirect()->route('impuestos.index')
            ->with('success', 'Impuesto actualizado exitosamente.');
    }

    public function destroy(Impuesto $impuesto): RedirectResponse
    {
        Gate::authorize('delete', $impuesto);

        if ($impuesto->productos()->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar este impuesto porque está asignado a uno o más productos.']);
        }

        $impuesto->delete();

        return redirect()->route('impuestos.index')
            ->with('success', 'Impuesto eliminado correctamente.');
    }

    public function hacerPorDefecto(Impuesto $impuesto): RedirectResponse
    {
        Gate::authorize('update', $impuesto);

        Impuesto::where('empresa_id', $impuesto->empresa_id)->update(['por_defecto' => false]);
        $impuesto->update(['por_defecto' => true]);

        return redirect()->route('impuestos.index')
            ->with('success', "El impuesto {$impuesto->nombre} fue marcado como predeterminado.");
    }
}
