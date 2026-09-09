<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Muestra el catálogo de clientes de la empresa activa.
     */
    public function index(Request $request): View
    {
        if (Gate::denies('viewAny', Cliente::class)) {
            abort(403, 'No tienes autorización para ver clientes.');
        }

        $term = $request->input('buscar');
        $tipoPersona = $request->input('tipo_persona');
        $estado = $request->input('estado');

        $query = Cliente::latest();

        if (! empty($term)) {
            $query->buscar($term);
        }

        if (! empty($tipoPersona)) {
            $query->where('tipo_persona', $tipoPersona);
        }

        if (! empty($estado)) {
            $query->where('estado', $estado);
        }

        $clientes = $query->paginate(15)->withQueryString();

        // Métricas / KPIs del Catálogo
        $totalClientes = Cliente::count();
        $totalActivos = Cliente::activo()->count();
        $totalConCredito = Cliente::where('cupo_credito', '>', 0)->count();
        $totalJuridicos = Cliente::where('tipo_persona', TipoPersona::JURIDICA->value)->count();

        return view('clientes.index', compact(
            'clientes',
            'term',
            'tipoPersona',
            'estado',
            'totalClientes',
            'totalActivos',
            'totalConCredito',
            'totalJuridicos'
        ));
    }

    /**
     * Muestra el formulario para registrar un nuevo cliente.
     */
    public function create(): View
    {
        if (Gate::denies('create', Cliente::class)) {
            abort(403, 'No tienes autorización para crear clientes.');
        }

        return view('clientes.create');
    }

    /**
     * Almacena un nuevo cliente en la empresa activa.
     */
    public function store(Request $request): RedirectResponse
    {
        if (Gate::denies('create', Cliente::class)) {
            abort(403, 'No tienes autorización para crear clientes.');
        }

        $empresaId = CompanyContext::getId();

        $validated = $request->validate([
            'tipo_persona' => ['required', Rule::enum(TipoPersona::class)],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoIdentidad::class)],
            'numero_documento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('clientes', 'numero_documento')
                    ->where('empresa_id', $empresaId)
                    ->whereNull('deleted_at'),
            ],
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'cupo_credito' => ['nullable', 'numeric', 'min:0'],
            'plazo_dias' => ['nullable', 'integer', 'min:0', 'max:365'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ], [
            'numero_documento.unique' => 'Ya existe un cliente registrado con este número de documento en la empresa.',
        ]);

        $validated['cupo_credito'] = (float) ($validated['cupo_credito'] ?? 0);
        $validated['plazo_dias'] = (int) ($validated['plazo_dias'] ?? 0);

        Cliente::create(array_merge($validated, [
            'empresa_id' => $empresaId,
            'es_predeterminado' => false,
        ]));

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un cliente existente.
     */
    public function edit(Cliente $cliente): View
    {
        if (Gate::denies('update', $cliente)) {
            abort(403, 'No tienes autorización para editar este cliente.');
        }

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Actualiza la información de un cliente.
     */
    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        if (Gate::denies('update', $cliente)) {
            abort(403, 'No tienes autorización para editar este cliente.');
        }

        $empresaId = CompanyContext::getId();

        $rules = [
            'tipo_persona' => ['required', Rule::enum(TipoPersona::class)],
            'tipo_documento' => ['required', Rule::enum(TipoDocumentoIdentidad::class)],
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'cupo_credito' => ['nullable', 'numeric', 'min:0'],
            'plazo_dias' => ['nullable', 'integer', 'min:0', 'max:365'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ];

        // El número de documento del consumidor final no se debe alterar
        if (! $cliente->isConsumidorFinal()) {
            $rules['numero_documento'] = [
                'required',
                'string',
                'max:50',
                Rule::unique('clientes', 'numero_documento')
                    ->where('empresa_id', $empresaId)
                    ->ignore($cliente->id)
                    ->whereNull('deleted_at'),
            ];
        }

        $validated = $request->validate($rules, [
            'numero_documento.unique' => 'Ya existe un cliente registrado con este número de documento en la empresa.',
        ]);

        $validated['cupo_credito'] = (float) ($validated['cupo_credito'] ?? 0);
        $validated['plazo_dias'] = (int) ($validated['plazo_dias'] ?? 0);

        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Elimina lógicamente a un cliente.
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        if (Gate::denies('delete', $cliente)) {
            if ($cliente->isConsumidorFinal()) {
                return back()->with('error', 'El cliente CONSUMIDOR FINAL es obligatorio para las ventas del sistema y no puede ser eliminado.');
            }
            abort(403, 'No tienes autorización para eliminar este cliente.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
