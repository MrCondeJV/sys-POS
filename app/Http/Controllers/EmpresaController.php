<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\BranchContext;
use App\Support\Tenancy\CompanyContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmpresaController extends Controller
{
    /**
     * Listado general de empresas para Super Administradores.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            return redirect()->route('empresa.perfil');
        }

        $query = Empresa::withoutGlobalScopes()->withCount(['sucursales', 'users']);

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_comercial', 'like', "%{$buscar}%")
                    ->orWhere('razon_social', 'like', "%{$buscar}%")
                    ->orWhere('nit', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%")
                    ->orWhere('ciudad', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $empresas = $query->latest()->paginate(12)->withQueryString();

        // Métricas rápidas para el dashboard SaaS
        $totalEmpresas = Empresa::withoutGlobalScopes()->count();
        $activasEmpresas = Empresa::withoutGlobalScopes()->where('estado', EstadoGeneral::ACTIVO->value)->count();
        $totalSucursales = Sucursal::withoutGlobalScopes()->count();
        $totalUsuarios = User::withoutGlobalScopes()->count();

        $empresaActivaId = CompanyContext::getId();

        return view('empresas.index', compact(
            'empresas',
            'totalEmpresas',
            'activasEmpresas',
            'totalSucursales',
            'totalUsuarios',
            'empresaActivaId'
        ));
    }

    /**
     * Formulario para dar de alta una nueva empresa tenant.
     */
    public function create(): View
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Solo el Super Administrador puede crear nuevas empresas.');
        }

        $tiposDocumento = TipoDocumentoIdentidad::cases();

        return view('empresas.create', compact('tiposDocumento'));
    }

    /**
     * Registra una nueva empresa, crea sus roles base, sede principal y usuario administrador inicial.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Solo el Super Administrador puede crear nuevas empresas.');
        }

        $validated = $request->validate([
            // Empresa
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'razon_social' => ['nullable', 'string', 'max:200'],
            'tipo_documento' => ['required', 'string', 'max:20'],
            'nit' => ['required', 'string', 'max:50', Rule::unique('empresas', 'nit')->whereNull('deleted_at')],
            'dv' => ['nullable', 'string', 'max:2'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'simbolo_moneda' => ['required', 'string', 'max:5'],

            // Sede Principal
            'sucursal_nombre' => ['required', 'string', 'max:150'],
            'sucursal_codigo' => ['nullable', 'string', 'max:50'],
            'sucursal_direccion' => ['nullable', 'string', 'max:255'],
            'sucursal_ciudad' => ['nullable', 'string', 'max:100'],

            // Administrador Inicial
            'admin_name' => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'admin_telefono' => ['nullable', 'string', 'max:50'],
            'admin_cargo' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, &$empresa) {
            // 1. Crear Empresa
            $empresa = Empresa::create([
                'nombre_comercial' => $validated['nombre_comercial'],
                'razon_social' => $validated['razon_social'] ?? $validated['nombre_comercial'],
                'tipo_documento' => $validated['tipo_documento'],
                'nit' => $validated['nit'],
                'dv' => $validated['dv'] ?? null,
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'ciudad' => $validated['ciudad'] ?? null,
                'departamento' => $validated['departamento'] ?? null,
                'moneda' => $validated['moneda'] ?? 'COP',
                'simbolo_moneda' => $validated['simbolo_moneda'] ?? '$',
                'estado' => EstadoGeneral::ACTIVO,
            ]);

            // 2. Crear Roles y Permisos para el nuevo Tenant
            RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);

            // 3. Crear Sucursal Principal
            $sucursal = Sucursal::create([
                'empresa_id' => $empresa->id,
                'nombre' => $validated['sucursal_nombre'],
                'codigo' => $validated['sucursal_codigo'] ?? 'SUC-001',
                'direccion' => $validated['sucursal_direccion'] ?? $empresa->direccion,
                'ciudad' => $validated['sucursal_ciudad'] ?? $empresa->ciudad,
                'departamento' => $empresa->departamento,
                'es_principal' => true,
                'estado' => EstadoGeneral::ACTIVO,
            ]);

            // 4. Crear Usuario Administrador de Empresa
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
                'telefono' => $validated['admin_telefono'] ?? null,
                'cargo' => $validated['admin_cargo'] ?? 'Gerente General',
                'estado' => EstadoGeneral::ACTIVO,
            ]);

            setPermissionsTeamId($empresa->id);
            $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);

            // Establecer como empresa activa para la sesión del Super Admin
            session(['superadmin_empresa_id' => $empresa->id]);
            BranchContext::setId($sucursal->id);
        });

        return redirect()->route('empresas.index')
            ->with('success', "Empresa '{$empresa->nombre_comercial}' registrada exitosamente con su sucursal principal y usuario administrador.");
    }

    /**
     * Formulario de edición de una empresa (Super Admin).
     */
    public function edit(Empresa $empresa): View
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado.');
        }

        $tiposDocumento = TipoDocumentoIdentidad::cases();

        return view('empresas.edit', compact('empresa', 'tiposDocumento'));
    }

    /**
     * Actualiza la información fiscal y de contacto de la empresa.
     */
    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado.');
        }

        $validated = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'razon_social' => ['nullable', 'string', 'max:200'],
            'tipo_documento' => ['required', 'string', 'max:20'],
            'nit' => ['required', 'string', 'max:50', Rule::unique('empresas', 'nit')->ignore($empresa->id)->whereNull('deleted_at')],
            'dv' => ['nullable', 'string', 'max:2'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'simbolo_moneda' => ['required', 'string', 'max:5'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        $empresa->update($validated);

        return redirect()->route('empresas.index')
            ->with('success', "Empresa '{$empresa->nombre_comercial}' actualizada correctamente.");
    }

    /**
     * Activa o suspende una empresa.
     */
    public function toggleEstado(Empresa $empresa): RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Acceso denegado.');
        }

        $nuevoEstado = $empresa->estado === EstadoGeneral::ACTIVO
            ? EstadoGeneral::INACTIVO
            : EstadoGeneral::ACTIVO;

        $empresa->update(['estado' => $nuevoEstado]);

        $mensaje = $nuevoEstado === EstadoGeneral::ACTIVO
            ? "Empresa '{$empresa->nombre_comercial}' activada."
            : "Empresa '{$empresa->nombre_comercial}' suspendida / inactivada.";

        return back()->with('success', $mensaje);
    }

    /**
     * Cambia la empresa activa en la sesión de trabajo del Super Administrador.
     */
    public function seleccionar(Request $request): RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Solo el Super Administrador puede alternar entre empresas.');
        }

        $request->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
        ]);

        $empresa = Empresa::withoutGlobalScopes()->findOrFail($request->empresa_id);

        session(['superadmin_empresa_id' => $empresa->id]);
        session()->forget('sucursal_activa_id');

        CompanyContext::setCompany($empresa);
        setPermissionsTeamId($empresa->id);

        // Resolver sucursal principal
        $sucursalPrincipal = $empresa->sucursales()->where('es_principal', true)->first()
            ?? $empresa->sucursales()->first();

        if ($sucursalPrincipal) {
            BranchContext::setId($sucursalPrincipal->id);
        } else {
            BranchContext::clear();
        }

        return back()->with('success', "Has cambiado a la empresa activa: {$empresa->nombre_comercial}");
    }

    /**
     * Muestra la vista de configuración del perfil de la empresa activa.
     */
    public function perfil(): View
    {
        $empresa = CompanyContext::getCompany();

        if (! $empresa) {
            abort(404, 'Empresa no encontrada en el contexto actual.');
        }

        return view('empresa.perfil', compact('empresa'));
    }

    /**
     * Actualiza la información de la empresa activa sin permitir inyección de tenant.
     */
    public function updatePerfil(Request $request): RedirectResponse
    {
        $empresa = CompanyContext::getCompany();

        if (! $empresa) {
            abort(404, 'Empresa no encontrada.');
        }

        if ($request->user()->cannot('update', $empresa)) {
            abort(403, 'No tienes permisos para modificar los datos de la empresa.');
        }

        $validated = $request->validate([
            'nombre_comercial' => ['required', 'string', 'max:150'],
            'razon_social' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'moneda' => ['required', 'string', 'max:10'],
            'simbolo_moneda' => ['required', 'string', 'max:5'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'eliminar_logo' => ['nullable', 'boolean'],
            'color_primario' => ['nullable', 'string', 'in:indigo,blue,emerald,violet,rose,orange,amber,slate'],
        ]);

        // Procesar eliminación de logo
        if ($request->boolean('eliminar_logo')) {
            if ($empresa->logo_path && Storage::disk('public')->exists($empresa->logo_path)) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $empresa->logo_path = null;
        }

        // Procesar nuevo logo
        if ($request->hasFile('logo')) {
            if ($empresa->logo_path && Storage::disk('public')->exists($empresa->logo_path)) {
                Storage::disk('public')->delete($empresa->logo_path);
            }
            $logoPath = $request->file('logo')->store("logos/{$empresa->id}", 'public');
            $empresa->logo_path = $logoPath;
        }

        // Procesar configuración de color primario
        if ($request->filled('color_primario')) {
            $configuraciones = $empresa->configuraciones ?? [];
            $configuraciones['color_primario'] = $request->input('color_primario');
            $empresa->configuraciones = $configuraciones;
        }

        // Actualizar datos base
        unset($validated['logo'], $validated['eliminar_logo'], $validated['color_primario']);
        $empresa->fill($validated);
        $empresa->save();

        return redirect()->route('empresa.perfil')
            ->with('success', 'Los datos y la identidad visual de la empresa han sido actualizados exitosamente.');
    }
}
