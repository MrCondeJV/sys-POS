@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Colaboradores')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                    Equipo & Seguridad
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Gestión de Usuarios</h1>
            <p class="text-sm text-slate-500">Administra los colaboradores, cajeros, administradores y sus roles en el punto de venta.</p>
        </div>
        @can('create', \App\Models\User::class)
        <div class="flex items-center gap-3">
            <a href="{{ route('usuarios.create') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Usuario
            </a>
        </div>
        @endcan
    </div>

    <!-- KPIs de Usuarios -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Colaboradores</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalUsuarios) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuarios Activos</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($activosUsuarios) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cajeros</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalCajeros) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Administradores</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalAdmins) }}</p>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
        <form method="GET" action="{{ route('usuarios.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-5">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        placeholder="Buscar por nombre, correo, cargo o teléfono...">
                </div>
            </div>

            <div class="sm:col-span-2">
                <select name="sucursal_id"
                    class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $suc)
                    <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                        {{ $suc->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="rol"
                    class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los Roles</option>
                    @foreach($rolesDisponibles as $rol)
                    <option value="{{ $rol->value }}" {{ request('rol') === $rol->value ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', $rol->value) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-1">
                <select name="estado"
                    class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Estado</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'sucursal_id', 'rol', 'estado']))
                <a href="{{ route('usuarios.index') }}"
                    class="p-2.5 rounded-xl border border-slate-300 text-slate-500 hover:bg-slate-50 transition" title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Usuario / Colaborador</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Rol de Acceso</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Sede / Sucursal</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($usuarios as $user)
                    <tr class="hover:bg-slate-50/70 transition {{ $user->id === auth()->id() ? 'bg-indigo-50/30' : '' }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-sm text-white {{ $user->isSuperAdmin() ? 'bg-purple-600' : 'bg-indigo-600' }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                            Tú
                                        </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $user->cargo ?? 'Sin cargo definido' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-slate-800 text-xs">{{ $user->email }}</div>
                            <div class="text-xs text-slate-400">{{ $user->telefono ?? 'Sin teléfono' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $userRole = $user->roles->first()?->name;
                            @endphp
                            @if($userRole === \App\Enums\RolSistema::SUPER_ADMIN->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                    🛡️ Super Admin
                                </span>
                            @elseif($userRole === \App\Enums\RolSistema::ADMIN_EMPRESA->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    👑 Admin Empresa
                                </span>
                            @elseif($userRole === \App\Enums\RolSistema::ADMIN_SUCURSAL->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/60">
                                    🏢 Admin Sede
                                </span>
                            @elseif($userRole === \App\Enums\RolSistema::CAJERO->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    💵 Cajero
                                </span>
                            @elseif($userRole === \App\Enums\RolSistema::VENDEDOR->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    🏷️ Vendedor
                                </span>
                            @elseif($userRole === \App\Enums\RolSistema::CONTADOR->value)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200/60">
                                    📊 Contador
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    {{ $userRole ?? 'Sin Rol' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs text-slate-700 font-medium">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ $user->sucursal?->nombre ?? 'Todas / Global' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($user->estado === \App\Enums\EstadoGeneral::ACTIVO)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                ● Activo
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200/60">
                                ● Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @can('update', $user)
                                <a href="{{ route('usuarios.edit', $user) }}"
                                    class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition"
                                    title="Editar usuario y permisos">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.toggle-estado', $user) }}" class="inline"
                                    onsubmit="return confirm('¿Confirmas cambiar el estado de este usuario?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded-lg transition {{ $user->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }}"
                                        title="{{ $user->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'Inactivar acceso' : 'Reactivar acceso' }}">
                                        @if($user->estado === \App\Enums\EstadoGeneral::ACTIVO)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        @else
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        @endif
                                    </button>
                                </form>
                                @endif
                                @endcan

                                @can('delete', $user)
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.destroy', $user) }}" class="inline"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este usuario? No podrá volver a ingresar.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition"
                                        title="Eliminar usuario">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            <div class="h-12 w-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                👥
                            </div>
                            <p class="font-semibold text-slate-700">No se encontraron usuarios</p>
                            <p class="text-xs text-slate-400 mt-1">Registra nuevos colaboradores para que puedan operar el POS.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
