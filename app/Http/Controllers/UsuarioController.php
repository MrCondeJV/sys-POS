<?php

namespace App\Http\Controllers;

use App\Enums\EstadoGeneral;
use App\Enums\PermisoSistema;
use App\Enums\RolSistema;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    /**
     * Lista todos los usuarios de la empresa activa con sus roles y permisos.
     */
    public function index(Request $request): View
    {
        $this->authorize('usuarios.ver');

        $empresaId = auth()->user()->empresa_id;

        $query = User::with(['sucursal', 'roles', 'permissions'])
            ->where('empresa_id', $empresaId)
            ->orderBy('name');

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->rol));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $usuarios = $query->get();

        // KPIs
        $totalUsuarios   = $usuarios->count();
        $activos         = $usuarios->filter(fn($u) => $u->estado === EstadoGeneral::ACTIVO)->count();
        $inactivos       = $totalUsuarios - $activos;
        $rolesEnUso      = $usuarios->flatMap->roles->pluck('name')->unique()->count();

        // Roles disponibles para la empresa (scoped por team)
        $rolesDisponibles = Role::where('team_id', $empresaId)->get();

        // Sucursales de la empresa
        $sucursales = Sucursal::where('empresa_id', $empresaId)->activa()->orderBy('nombre')->get();

        // Agrupar permisos por módulo para mostrar en la vista
        $gruposPermisos = $this->getGruposPermisos();

        return view('usuarios.index', compact(
            'usuarios',
            'totalUsuarios',
            'activos',
            'inactivos',
            'rolesEnUso',
            'rolesDisponibles',
            'sucursales',
            'gruposPermisos',
        ));
    }

    /**
     * Formulario para crear un nuevo usuario.
     */
    public function create(): View
    {
        $this->authorize('usuarios.gestionar');

        $empresaId        = auth()->user()->empresa_id;
        $rolesDisponibles = Role::where('team_id', $empresaId)->get();
        $sucursales       = Sucursal::where('empresa_id', $empresaId)->activa()->orderBy('nombre')->get();
        $roles            = RolSistema::cases();

        return view('usuarios.create', compact('rolesDisponibles', 'sucursales', 'roles'));
    }

    /**
     * Guarda un nuevo usuario en la empresa activa.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('usuarios.gestionar');

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => ['required', Password::min(8)->letters()->numbers()],
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'cargo'       => 'nullable|string|max:100',
            'rol'         => 'nullable|string|exists:roles,name',
            'estado'      => 'required|in:activo,inactivo',
        ]);

        $empresaId = auth()->user()->empresa_id;

        $usuario = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'empresa_id'  => $empresaId,
            'sucursal_id' => $request->sucursal_id,
            'cargo'       => $request->cargo,
            'estado'      => $request->estado,
        ]);

        if ($request->filled('rol')) {
            setPermissionsTeamId($empresaId);
            $usuario->assignRole($request->rol);
        }

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} creado exitosamente.");
    }

    /**
     * Formulario para editar un usuario existente.
     */
    public function edit(User $usuario): View
    {
        $this->authorize('usuarios.gestionar');
        $this->verificarMismaEmpresa($usuario);

        $empresaId        = auth()->user()->empresa_id;
        $rolesDisponibles = Role::where('team_id', $empresaId)->get();
        $sucursales       = Sucursal::where('empresa_id', $empresaId)->activa()->orderBy('nombre')->get();
        $rolActual        = $usuario->roles->first()?->name;

        return view('usuarios.edit', compact('usuario', 'rolesDisponibles', 'sucursales', 'rolActual'));
    }

    /**
     * Actualiza los datos de un usuario.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('usuarios.gestionar');
        $this->verificarMismaEmpresa($usuario);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$usuario->id}",
            'password'    => ['nullable', Password::min(8)->letters()->numbers()],
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'cargo'       => 'nullable|string|max:100',
            'rol'         => 'nullable|string|exists:roles,name',
            'estado'      => 'required|in:activo,inactivo',
        ]);

        $usuario->update([
            'name'        => $request->name,
            'email'       => $request->email,
            'sucursal_id' => $request->sucursal_id,
            'cargo'       => $request->cargo,
            'estado'      => $request->estado,
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        // Actualizar rol
        $empresaId = auth()->user()->empresa_id;
        setPermissionsTeamId($empresaId);

        $usuario->syncRoles($request->filled('rol') ? [$request->rol] : []);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado correctamente.");
    }

    /**
     * Elimina (soft delete) un usuario de la empresa.
     */
    public function destroy(User $usuario): RedirectResponse
    {
        $this->authorize('usuarios.gestionar');
        $this->verificarMismaEmpresa($usuario);

        if ($usuario->isSuperAdmin()) {
            return back()->with('error', 'No se puede eliminar al Super Administrador del sistema.');
        }

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$nombre} eliminado.");
    }

    /**
     * Retorna los permisos agrupados por módulo para la vista.
     */
    private function getGruposPermisos(): array
    {
        return [
            'Ventas'          => ['ventas.ver', 'ventas.crear', 'ventas.anular', 'ventas.devolver'],
            'Productos'       => ['productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar'],
            'Inventario'      => ['inventario.ver', 'inventario.ajustar'],
            'Caja'            => ['caja.ver', 'caja.administrar', 'caja.abrir', 'caja.cerrar', 'caja.movimiento'],
            'Compras'         => ['compras.ver', 'compras.crear', 'compras.anular'],
            'Proveedores'     => ['proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.eliminar'],
            'Clientes'        => ['clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar'],
            'Cartera'         => ['cartera.ver', 'cartera.crear', 'cartera.abonar', 'cartera.anular'],
            'Reportes'        => ['reportes.ver'],
            'Auditoría'       => ['auditoria.ver'],
            'Empresa'         => ['empresa.gestionar', 'sucursales.gestionar'],
            'Usuarios'        => ['usuarios.ver', 'usuarios.gestionar'],
            'Listas de Precios' => ['listas_precios.ver', 'listas_precios.crear', 'listas_precios.editar', 'listas_precios.eliminar'],
            'Impuestos'       => ['impuestos.ver', 'impuestos.crear', 'impuestos.editar', 'impuestos.eliminar'],
            'Documentos'      => ['documentos.ver', 'documentos.emitir', 'documentos.anular'],
        ];
    }

    /**
     * Verifica que el usuario pertenezca a la misma empresa del autenticado.
     */
    private function verificarMismaEmpresa(User $usuario): void
    {
        if ($usuario->empresa_id !== auth()->user()->empresa_id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'No tiene acceso a este usuario.');
        }
    }
}
