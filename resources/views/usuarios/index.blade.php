@extends('layouts.app')

@section('title', 'Gestión de Usuarios y Colaboradores')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Principal -->
    <x-page-header
        title="Gestión de Usuarios"
        subtitle="Administra los colaboradores, cajeros, administradores y sus roles en el punto de venta."
    >
        @can('create', \App\Models\User::class)
        <x-slot:actions>
            <x-button
                variant="primary"
                size="md"
                href="{{ route('usuarios.create') }}"
                icon="<svg class='w-4 h-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' /></svg>"
            >
                Nuevo Usuario
            </x-button>
        </x-slot:actions>
        @endcan
    </x-page-header>

    <!-- KPIs de Usuarios -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Total Colaboradores"
            value="{{ number_format($totalUsuarios) }}"
            icon="<svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' /></svg>"
        />

        <x-kpi-card
            label="Usuarios Activos"
            value="{{ number_format($activosUsuarios) }}"
            icon="<svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>"
        />

        <x-kpi-card
            label="Cajeros"
            value="{{ number_format($totalCajeros) }}"
            icon="<svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' /></svg>"
        />

        <x-kpi-card
            label="Administradores"
            value="{{ number_format($totalAdmins) }}"
            icon="<svg class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' /></svg>"
        />
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <x-filter-bar
        :action="route('usuarios.index')"
        :hasActiveFilters="request()->hasAny(['buscar', 'sucursal_id', 'rol', 'estado'])"
        :resetUrl="route('usuarios.index')"
    >
        <div class="sm:col-span-12 lg:col-span-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    class="input pl-9"
                    placeholder="Buscar por nombre, correo, cargo..."
                />
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="sucursal_id" class="input">
                <option value="">Todas las Sedes</option>
                @foreach($sucursales as $suc)
                <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                    {{ $suc->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="rol" class="input">
                <option value="">Todos los Roles</option>
                @foreach($rolesDisponibles as $rol)
                <option value="{{ $rol->value }}" {{ request('rol') === $rol->value ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $rol->value) }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="estado" class="input">
                <option value="">Todos los Estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla de Usuarios -->
    <x-table class="hidden lg:block">
        <x-slot:header>
            <tr>
                <th>Usuario / Colaborador</th>
                <th class="hidden @4xl:table-cell">Contacto</th>
                <th>Rol de Acceso</th>
                <th class="hidden @5xl:table-cell">Sede / Sucursal</th>
                <th class="text-center">Estado</th>
                <th class="text-right">Acciones</th>
            </tr>
        </x-slot:header>

        @forelse($usuarios as $user)
        <tr class="{{ $user->id === auth()->id() ? 'bg-surface-muted font-medium' : '' }}">
            <td>
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-md bg-brand-700 text-white font-semibold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-text-primary flex items-center gap-1.5 truncate">
                            <span>{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                            <span class="badge badge-neutral text-[10px] font-medium shrink-0">
                                Tú
                            </span>
                            @endif
                        </div>
                        <div class="text-xs text-text-secondary truncate">
                            {{ $user->cargo ?? 'Sin cargo definido' }}
                        </div>
                        <div class="@4xl:hidden text-xs text-text-muted font-mono break-all">{{ $user->email }}</div>
                    </div>
                </div>
            </td>
            <td class="hidden @4xl:table-cell">
                <div class="font-mono text-xs text-text-primary break-all">{{ $user->email }}</div>
                <div class="text-xs text-text-secondary mt-0.5">{{ $user->telefono ?? 'Sin teléfono' }}</div>
            </td>
            <td>
                @php
                    $userRole = $user->roles->first()?->name;
                    $isPrivileged = in_array($userRole, [\App\Enums\RolSistema::SUPER_ADMIN->value, \App\Enums\RolSistema::ADMIN_EMPRESA->value]);
                @endphp
                <x-badge :variant="$isPrivileged ? 'info' : 'neutral'" size="sm">
                    {{ $userRole ? str_replace('_', ' ', $userRole) : 'Sin Rol' }}
                </x-badge>
            </td>
            <td class="hidden @5xl:table-cell">
                <span class="inline-flex items-center gap-1.5 text-xs text-text-primary">
                    <svg class="h-3.5 w-3.5 text-text-muted shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <span>{{ $user->sucursal?->nombre ?? 'Todas / Global' }}</span>
                </span>
            </td>
            <td class="text-center">
                <x-status-badge :status="$user->estado" />
            </td>
            <td class="text-right">
                @include('usuarios.partials.acciones')
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="p-0">
                <x-empty-state
                    title="No se encontraron usuarios"
                    description="No hay colaboradores registrados con los criterios de búsqueda actuales."
                >
                    @can('create', \App\Models\User::class)
                    <x-button variant="primary" size="sm" href="{{ route('usuarios.create') }}">
                        Registrar Primer Usuario
                    </x-button>
                    @endcan
                </x-empty-state>
            </td>
        </tr>
        @endforelse
    </x-table>

    <!-- Vista Móvil y Tablet -->
    <div class="lg:hidden bg-surface rounded-xl border border-border shadow-xs divide-y divide-border overflow-hidden">
        @forelse($usuarios as $user)
        @php
            $userRole = $user->roles->first()?->name;
            $isPrivileged = in_array($userRole, [\App\Enums\RolSistema::SUPER_ADMIN->value, \App\Enums\RolSistema::ADMIN_EMPRESA->value]);
        @endphp
        <div class="p-4 space-y-3 {{ $user->id === auth()->id() ? 'bg-surface-muted' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="h-9 w-9 rounded-md bg-brand-700 text-white font-semibold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-text-primary text-sm flex flex-wrap items-center gap-1.5">
                            <span>{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                            <span class="badge badge-neutral text-[10px] font-medium">Tú</span>
                            @endif
                        </div>
                        <div class="text-xs text-text-secondary">{{ $user->cargo ?? 'Sin cargo definido' }}</div>
                    </div>
                </div>
                <x-status-badge :status="$user->estado" />
            </div>

            <div class="text-xs text-text-secondary space-y-1">
                <div class="font-mono text-text-primary break-all">{{ $user->email }}</div>
                <div>{{ $user->telefono ?? 'Sin teléfono' }} &bull; {{ $user->sucursal?->nombre ?? 'Todas / Global' }}</div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3 border-t border-border">
                <x-badge :variant="$isPrivileged ? 'info' : 'neutral'" size="sm">
                    {{ $userRole ? str_replace('_', ' ', $userRole) : 'Sin Rol' }}
                </x-badge>
                @include('usuarios.partials.acciones')
            </div>
        </div>
        @empty
        <x-empty-state
            title="No se encontraron usuarios"
            description="No hay colaboradores registrados con los criterios de búsqueda actuales."
        >
            @can('create', \App\Models\User::class)
            <x-button variant="primary" size="sm" href="{{ route('usuarios.create') }}">
                Registrar Primer Usuario
            </x-button>
            @endcan
        </x-empty-state>
        @endforelse
    </div>

    @if($usuarios->hasPages())
        <div class="px-4 py-3 border border-border bg-surface-muted rounded-xl flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-text-secondary">
                Mostrando {{ $usuarios->firstItem() ?? 0 }}–{{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} colaboradores
            </div>
            <div>
                {{ $usuarios->links() }}
            </div>
        </div>
    @endif

    {{-- Único modal reutilizable teletransportado a body para acciones de fila (toggle-estado y destroy) --}}
    <x-modal-confirm name="confirm-usuario" />
</div>
@endsection
