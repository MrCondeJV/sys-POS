<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\Tenancy\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Listado de usuarios con filtros y KPIs de la empresa activa.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('viewAny', User::class)) {
            abort(403, 'No tienes autorización para ver usuarios.');
        }

        $empresaId = CompanyContext::getId();

        $query = User::withoutGlobalScopes()->with(['sucursal', 'empresa', 'roles']);

        if (! $currentUser->isSuperAdmin()) {
            $query->where('empresa_id', $empresaId);
        } elseif ($empresaId && ! $request->filled('todas_empresas')) {
            $query->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                    ->orWhereNull('empresa_id');
            });
        }

        // Filtro de búsqueda por texto
        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%")
                    ->orWhere('cargo', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            });
        }

        // Filtro por sucursal
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->rol));
        }

        $usuarios = $query->latest()->paginate(15)->withQueryString();

        // KPIs
        $baseKpiQuery = User::withoutGlobalScopes();
        if (! $currentUser->isSuperAdmin()) {
            $baseKpiQuery->where('empresa_id', $empresaId);
        } elseif ($empresaId && ! $request->filled('todas_empresas')) {
            $baseKpiQuery->where(function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId)
                    ->orWhereNull('empresa_id');
            });
        }

        $totalUsuarios = (clone $baseKpiQuery)->count();
        $activosUsuarios = (clone $baseKpiQuery)->where('estado', EstadoGeneral::ACTIVO->value)->count();
        $totalCajeros = (clone $baseKpiQuery)->whereHas('roles', fn ($q) => $q->where('name', RolSistema::CAJERO->value))->count();
        $totalAdmins = (clone $baseKpiQuery)->whereHas('roles', fn ($q) => $q->where('name', RolSistema::ADMIN_EMPRESA->value))->count();

        // Datos para filtros
        $sucursales = $empresaId
            ? Sucursal::where('empresa_id', $empresaId)->orderBy('nombre')->get()
            : Sucursal::orderBy('nombre')->get();

        $rolesDisponibles = RolSistema::cases();
        if (! $currentUser->isSuperAdmin()) {
            $rolesDisponibles = array_filter($rolesDisponibles, fn ($r) => $r !== RolSistema::SUPER_ADMIN);
        }

        return view('usuarios.index', compact(
            'usuarios',
            'totalUsuarios',
            'activosUsuarios',
            'totalCajeros',
            'totalAdmins',
            'sucursales',
            'rolesDisponibles'
        ));
    }

    /**
     * Formulario de creación de un nuevo colaborador.
     */
    public function create(Request $request): View
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('create', User::class)) {
            abort(403, 'No tienes autorización para registrar usuarios.');
        }

        $empresaId = CompanyContext::getId();
        $empresa = CompanyContext::getCompany();

        $sucursales = $empresa
            ? $empresa->sucursales()->where('estado', EstadoGeneral::ACTIVO->value)->orderBy('nombre')->get()
            : Sucursal::where('estado', EstadoGeneral::ACTIVO->value)->orderBy('nombre')->get();

        $roles = RolSistema::cases();
        if (! $currentUser->isSuperAdmin()) {
            $roles = array_filter($roles, fn ($r) => $r !== RolSistema::SUPER_ADMIN);
        }

        $empresas = $currentUser->isSuperAdmin() ? Empresa::orderBy('nombre_comercial')->get() : collect();

        return view('usuarios.create', compact('sucursales', 'roles', 'empresas', 'empresaId'));
    }

    /**
     * Almacena un nuevo usuario asignando rol y contexto multi-tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('create', User::class)) {
            abort(403, 'No tienes autorización para registrar usuarios.');
        }

        $empresaId = $currentUser->isSuperAdmin() && $request->filled('empresa_id')
            ? (int) $request->empresa_id
            : CompanyContext::getId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'rol' => ['required', 'string'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ]);

        // Validar que el rol no sea SUPER_ADMIN a menos que el usuario sea SuperAdmin
        if ($validated['rol'] === RolSistema::SUPER_ADMIN->value && ! $currentUser->isSuperAdmin()) {
            abort(403, 'No puedes asignar el rol de Super Administrador.');
        }

        DB::transaction(function () use ($validated, $empresaId) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'empresa_id' => $validated['rol'] === RolSistema::SUPER_ADMIN->value ? null : $empresaId,
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'cargo' => $validated['cargo'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'estado' => $validated['estado'],
            ]);

            // Asignar rol con Spatie (asociado al equipo/tenant de la empresa)
            $teamId = $user->empresa_id;
            setPermissionsTeamId($teamId);
            $user->assignRole($validated['rol']);
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$validated['name']}' creado exitosamente.");
    }

    /**
     * Formulario de edición de usuario.
     */
    public function edit(Request $request, User $usuario): View
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('update', $usuario)) {
            abort(403, 'No tienes autorización para editar este usuario.');
        }

        $empresaId = $usuario->empresa_id ?? CompanyContext::getId();

        $sucursales = Sucursal::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('estado', EstadoGeneral::ACTIVO->value)
            ->orderBy('nombre')
            ->get();

        $roles = RolSistema::cases();
        if (! $currentUser->isSuperAdmin()) {
            $roles = array_filter($roles, fn ($r) => $r !== RolSistema::SUPER_ADMIN);
        }

        // Obtener rol actual
        setPermissionsTeamId($usuario->empresa_id);
        $rolActual = $usuario->roles->first()?->name;

        return view('usuarios.edit', compact('usuario', 'sucursales', 'roles', 'rolActual'));
    }

    /**
     * Actualiza la información y rol del usuario.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('update', $usuario)) {
            abort(403, 'No tienes autorización para modificar este usuario.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($usuario->id)->whereNull('deleted_at')],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'rol' => ['required', 'string'],
            'estado' => ['required', Rule::enum(EstadoGeneral::class)],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        // No permitir que un usuario se auto-inactive
        if ($usuario->id === $currentUser->id && $validated['estado'] === EstadoGeneral::INACTIVO->value) {
            return back()->withErrors(['estado' => 'No puedes desactivar tu propia cuenta mientras estás en sesión.']);
        }

        // Validar que no se intente auto-degradar el Super Admin si no es superadmin
        if ($validated['rol'] === RolSistema::SUPER_ADMIN->value && ! $currentUser->isSuperAdmin()) {
            abort(403, 'No puedes asignar el rol de Super Administrador.');
        }

        DB::transaction(function () use ($usuario, $validated, $request) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'sucursal_id' => $validated['sucursal_id'] ?? null,
                'cargo' => $validated['cargo'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'estado' => $validated['estado'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $usuario->update($updateData);

            // Sincronizar rol
            setPermissionsTeamId($usuario->empresa_id);
            $usuario->syncRoles([$validated['rol']]);
        });

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado correctamente.");
    }

    /**
     * Elimina un usuario (soft delete), impidiendo eliminarse a sí mismo.
     */
    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('delete', $usuario)) {
            abort(403, 'No puedes eliminar este usuario.');
        }

        if ($usuario->id === $currentUser->id) {
            return back()->withErrors(['general' => 'No puedes eliminar tu propia cuenta.']);
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$nombre}' eliminado del sistema.");
    }

    /**
     * Alterna rápidamente el estado del usuario entre ACTIVO e INACTIVO.
     */
    public function toggleEstado(Request $request, User $usuario): RedirectResponse
    {
        $currentUser = $request->user();
        if ($currentUser->cannot('update', $usuario)) {
            abort(403, 'No tienes autorización.');
        }

        if ($usuario->id === $currentUser->id) {
            return back()->withErrors(['general' => 'No puedes inactivar tu propia cuenta.']);
        }

        $nuevoEstado = $usuario->estado === EstadoGeneral::ACTIVO
            ? EstadoGeneral::INACTIVO
            : EstadoGeneral::ACTIVO;

        $usuario->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === EstadoGeneral::ACTIVO ? 'activado' : 'inactivado';

        return back()->with('success', "El usuario '{$usuario->name}' ha sido {$accion}.");
    }
}
