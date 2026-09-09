<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Models\Laboratorio;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaboratorioController extends Controller
{
    public function index(Request $request): View
    {
        $query = Laboratorio::withCount('productos')->latest('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('nombre', 'like', "%{$q}%")
                  ->orWhere('codigo', 'like', "%{$q}%");
        }

        $laboratorios = $query->paginate(15);

        return view('farmacia.laboratorios.index', compact('laboratorios'));
    }

    public function create(): View
    {
        return view('farmacia.laboratorios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('laboratorios')->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'codigo' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $validated['empresa_id'] = $empresaId;
        Laboratorio::create($validated);

        return redirect()->route('laboratorios.index')->with('success', 'Laboratorio registrado exitosamente.');
    }

    public function edit(Laboratorio $laboratorio): View
    {
        return view('farmacia.laboratorios.edit', compact('laboratorio'));
    }

    public function update(Request $request, Laboratorio $laboratorio): RedirectResponse
    {
        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('laboratorios')->ignore($laboratorio->id)->where(fn ($q) => $q->where('empresa_id', $empresaId)),
            ],
            'codigo' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $laboratorio->update($validated);

        return redirect()->route('laboratorios.index')->with('success', 'Laboratorio actualizado exitosamente.');
    }

    public function destroy(Laboratorio $laboratorio): RedirectResponse
    {
        if ($laboratorio->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar el laboratorio porque tiene medicamentos asociados.');
        }

        $laboratorio->delete();

        return redirect()->route('laboratorios.index')->with('success', 'Laboratorio eliminado.');
    }
}
