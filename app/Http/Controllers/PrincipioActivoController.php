<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Models\PrincipioActivo;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrincipioActivoController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrincipioActivo::withCount('productos')->latest('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('nombre', 'like', "%{$q}%")
                  ->orWhere('concentracion', 'like', "%{$q}%");
        }

        $principios = $query->paginate(15);

        return view('farmacia.principios-activos.index', compact('principios'));
    }

    public function create(): View
    {
        return view('farmacia.principios-activos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $existe = PrincipioActivo::where('empresa_id', $empresaId)
            ->where('nombre', $validated['nombre'])
            ->where('concentracion', $validated['concentracion'] ?? null)
            ->exists();

        if ($existe) {
            return back()->withInput()->withErrors(['nombre' => 'Ya existe un principio activo con ese nombre y concentración.']);
        }

        $validated['empresa_id'] = $empresaId;
        PrincipioActivo::create($validated);

        return redirect()->route('principios-activos.index')->with('success', 'Principio activo registrado exitosamente.');
    }

    public function edit(PrincipioActivo $principios_activo): View
    {
        $principio = $principios_activo;
        return view('farmacia.principios-activos.edit', compact('principio'));
    }

    public function update(Request $request, PrincipioActivo $principios_activo): RedirectResponse
    {
        $principio = $principios_activo;
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'concentracion' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $existe = PrincipioActivo::where('empresa_id', $empresaId)
            ->where('nombre', $validated['nombre'])
            ->where('concentracion', $validated['concentracion'] ?? null)
            ->where('id', '!=', $principio->id)
            ->exists();

        if ($existe) {
            return back()->withInput()->withErrors(['nombre' => 'Ya existe otro principio activo con ese nombre y concentración.']);
        }

        $principio->update($validated);

        return redirect()->route('principios-activos.index')->with('success', 'Principio activo actualizado.');
    }

    public function destroy(PrincipioActivo $principios_activo): RedirectResponse
    {
        $principio = $principios_activo;

        if ($principio->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar el principio activo porque tiene medicamentos asociados.');
        }

        $principio->delete();

        return redirect()->route('principios-activos.index')->with('success', 'Principio activo eliminado.');
    }
}
