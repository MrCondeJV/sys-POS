@extends('layouts.app')

@section('title', 'Gestión de Empresas (Super Admin)')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Principal -->
    <x-page-header
        title="Gestión de Empresas (Multi-Tenant)"
        subtitle="Administra los comercios suscritos, sedes activas, credenciales e ingresa a cualquier negocio para soporte directo."
    >
        <x-slot:actions>
            @if($empresaActivaId)
            <form method="POST" action="{{ route('empresas.salir') }}">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <svg class="h-4 w-4 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Salir del Negocio Activo
                </button>
            </form>
            @endif
            <a href="{{ route('empresas.create') }}" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Empresa
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- KPIs Globales de la Plataforma -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Total Empresas"
            value="{{ number_format($totalEmpresas) }}"
            context="Tenants registrados"
            icon="<svg class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' /></svg>"
        />

        <x-kpi-card
            label="Empresas Activas"
            value="{{ number_format($activasEmpresas) }}"
            context="Operando en plataforma"
            icon="<svg class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>"
        />

        <x-kpi-card
            label="Sedes / Sucursales"
            value="{{ number_format($totalSucursales) }}"
            context="Puntos físicos configurados"
            icon="<svg class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' /><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 11a3 3 0 11-6 0 3 3 0 016 0z' /></svg>"
        />

        <x-kpi-card
            label="Usuarios Globales"
            value="{{ number_format($totalUsuarios) }}"
            context="Cuentas activas e inactivas"
            icon="<svg class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' /></svg>"
        />
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <x-filter-bar
        :action="route('empresas.index')"
        :hasActiveFilters="request()->hasAny(['buscar', 'estado'])"
        :resetUrl="route('empresas.index')"
    >
        <div class="sm:col-span-12 lg:col-span-7">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-text-muted pointer-events-none">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input
                    type="text"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    class="input pl-9"
                    placeholder="Buscar por nombre comercial, razón social, NIT o ciudad..."
                />
            </div>
        </div>
        <div class="sm:col-span-6 lg:col-span-3">
            <select name="estado" class="input">
                <option value="">Todos los Estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activas</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivas / Suspendidas</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla de Empresas -->
    <x-table>
        <x-slot:header>
            <tr>
                <th>Empresa / Razón Social</th>
                <th>Identificación Fiscal</th>
                <th>Ubicación / Contacto</th>
                <th class="text-center">Sedes</th>
                <th class="text-center">Usuarios</th>
                <th class="text-center">Estado</th>
                <th class="text-right">Acciones</th>
            </tr>
        </x-slot:header>

        @forelse($empresas as $emp)
        @php
            $esActivaEnSesion = ($empresaActivaId === $emp->id);
            $empEstadoActivo = $emp->estado === \App\Enums\EstadoGeneral::ACTIVO;
        @endphp
        <tr class="{{ $esActivaEnSesion ? 'bg-info-bg/40' : '' }}">
            <td>
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg flex items-center justify-center font-semibold text-xs shrink-0 {{ $esActivaEnSesion ? 'bg-brand-600 text-white' : 'bg-surface-muted text-text-secondary border border-border' }}">
                        {{ strtoupper(substr($emp->nombre_comercial, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-text-primary flex items-center gap-2">
                            <span>{{ $emp->nombre_comercial }}</span>
                            @if($esActivaEnSesion)
                            <x-badge variant="info" :dot="true">En sesión</x-badge>
                            @endif
                        </div>
                        <div class="text-xs text-text-muted">{{ $emp->razon_social }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="font-mono font-medium text-text-primary text-xs">
                    {{ $emp->tipo_documento?->value ?? 'NIT' }}: {{ $emp->nit }}{{ $emp->dv !== null && $emp->dv !== '' ? '-' . $emp->dv : '' }}
                </div>
                <div class="text-xs text-text-muted">Moneda: {{ $emp->moneda }} ({{ $emp->simbolo_moneda }})</div>
            </td>
            <td>
                <div class="text-xs font-medium text-text-primary">{{ $emp->ciudad ?? 'Sin ciudad' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}</div>
                <div class="text-xs text-text-muted">{{ $emp->email ?? $emp->telefono ?? 'Sin contacto' }}</div>
            </td>
            <td class="text-center">
                <x-badge variant="neutral" class="tabular-nums">{{ $emp->sucursales_count }}</x-badge>
            </td>
            <td class="text-center">
                <x-badge variant="neutral" class="tabular-nums">{{ $emp->users_count }}</x-badge>
            </td>
            <td class="text-center">
                <x-status-badge :status="$emp->estado" />
            </td>
            <td class="text-right">
                <div class="inline-flex items-center gap-1.5 justify-end">
                    <!-- Entrar al Negocio (Contexto Super Admin) -->
                    <form method="POST" action="{{ route('empresas.seleccionar') }}" class="inline">
                        @csrf
                        <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
                        <button
                            type="submit"
                            class="{{ $esActivaEnSesion ? 'btn btn-secondary text-xs h-8 px-2.5' : 'btn btn-primary text-xs h-8 px-2.5' }}"
                            title="Entrar a administrar este negocio"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>{{ $esActivaEnSesion ? 'Gestionando' : 'Entrar' }}</span>
                        </button>
                    </form>

                    <!-- Editar -->
                    <x-icon-button
                        href="{{ route('empresas.edit', $emp) }}"
                        variant="ghost"
                        size="sm"
                        title="Editar datos de la empresa"
                        aria-label="Editar empresa {{ $emp->nombre_comercial }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </x-icon-button>

                    <!-- Activar / Suspender con confirmación accesible reutilizable -->
                    <x-icon-button
                        type="button"
                        variant="ghost"
                        size="sm"
                        :title="$empEstadoActivo ? 'Suspender empresa' : 'Activar empresa'"
                        :aria-label="($empEstadoActivo ? 'Suspender ' : 'Activar ') . $emp->nombre_comercial"
                        @click="$dispatch('open-modal-confirm-empresa', {
                            triggerEl: $el,
                            title: @js(($empEstadoActivo ? '¿Suspender ' : '¿Activar ') . $emp->nombre_comercial . '?'),
                            message: @js($empEstadoActivo
                                ? 'El estado del registro de la empresa cambiará a INACTIVO en el directorio de tenants.'
                                : 'El estado del registro de la empresa cambiará a ACTIVO en el directorio de tenants.'),
                            consequence: @js($empEstadoActivo
                                ? 'Al suspender la empresa, sus usuarios no podrán iniciar sesión ni operar en el sistema. Las sesiones activas serán cerradas en su siguiente petición.'
                                : 'Esta acción reactiva el acceso de la empresa y permite a sus usuarios volver a iniciar sesión y operar.'),
                            confirmText: @js($empEstadoActivo ? 'Suspender empresa' : 'Activar empresa'),
                            variant: @js($empEstadoActivo ? 'danger' : 'primary'),
                            action: @js(route('empresas.toggle-estado', $emp)),
                            method: 'POST',
                            formId: @js('form-toggle-empresa-' . $emp->id)
                        })"
                    >
                        @if($empEstadoActivo)
                        <svg class="h-4 w-4 text-text-secondary hover:text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        @else
                        <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        @endif
                    </x-icon-button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="p-0">
                <x-empty-state
                    title="No se encontraron empresas registradas"
                    description="Registra el primer comercio en la plataforma para habilitar sucursales, cajas y usuarios."
                >
                    <a href="{{ route('empresas.create') }}" class="btn btn-primary">
                        Registrar Primera Empresa
                    </a>
                </x-empty-state>
            </td>
        </tr>
        @endforelse

        @if($empresas->hasPages())
        <x-slot:footer>
            {{ $empresas->links() }}
        </x-slot:footer>
        @endif
    </x-table>

    {{-- Único modal reutilizable teletransportado a body para suspender/activar empresas --}}
    <x-modal-confirm name="confirm-empresa" />
</div>
@endsection
