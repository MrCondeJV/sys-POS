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
    <x-table class="hidden lg:block">
        <x-slot:header>
            <tr>
                <th>Empresa / Razón Social</th>
                <th class="hidden @4xl:table-cell">Identificación Fiscal</th>
                <th class="hidden @5xl:table-cell">Ubicación / Contacto</th>
                <th class="hidden @6xl:table-cell text-center">Sedes</th>
                <th class="hidden @6xl:table-cell text-center">Usuarios</th>
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
                        <div class="font-semibold text-text-primary flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span>{{ $emp->nombre_comercial }}</span>
                            @if($esActivaEnSesion)
                            <x-badge variant="info" :dot="true">En sesión</x-badge>
                            @endif
                        </div>
                        <div class="text-xs text-text-muted">{{ $emp->razon_social }}</div>
                        <div class="@4xl:hidden text-xs text-text-muted font-mono">{{ $emp->tipo_documento?->value ?? 'NIT' }}: {{ $emp->nit }}{{ $emp->dv !== null && $emp->dv !== '' ? '-' . $emp->dv : '' }}</div>
                    </div>
                </div>
            </td>
            <td class="hidden @4xl:table-cell">
                <div class="font-mono font-medium text-text-primary text-xs whitespace-nowrap">
                    {{ $emp->tipo_documento?->value ?? 'NIT' }}: {{ $emp->nit }}{{ $emp->dv !== null && $emp->dv !== '' ? '-' . $emp->dv : '' }}
                </div>
                <div class="text-xs text-text-muted">Moneda: {{ $emp->moneda }} ({{ $emp->simbolo_moneda }})</div>
            </td>
            <td class="hidden @5xl:table-cell">
                <div class="text-xs font-medium text-text-primary">{{ $emp->ciudad ?? 'Sin ciudad' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}</div>
                <div class="text-xs text-text-muted break-all">{{ $emp->email ?? $emp->telefono ?? 'Sin contacto' }}</div>
            </td>
            <td class="hidden @6xl:table-cell text-center">
                <x-badge variant="neutral" class="tabular-nums">{{ $emp->sucursales_count }}</x-badge>
            </td>
            <td class="hidden @6xl:table-cell text-center">
                <x-badge variant="neutral" class="tabular-nums">{{ $emp->users_count }}</x-badge>
            </td>
            <td class="text-center">
                <x-status-badge :status="$emp->estado" />
            </td>
            <td class="text-right">
                @include('empresas.partials.acciones')
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
    </x-table>

    <!-- Vista Móvil y Tablet -->
    <div class="lg:hidden bg-surface rounded-xl border border-border shadow-xs divide-y divide-border overflow-hidden">
        @forelse($empresas as $emp)
        @php
            $esActivaEnSesion = ($empresaActivaId === $emp->id);
            $empEstadoActivo = $emp->estado === \App\Enums\EstadoGeneral::ACTIVO;
        @endphp
        <div class="p-4 space-y-3 {{ $esActivaEnSesion ? 'bg-info-bg/40' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="h-9 w-9 rounded-lg flex items-center justify-center font-semibold text-xs shrink-0 {{ $esActivaEnSesion ? 'bg-brand-600 text-white' : 'bg-surface-muted text-text-secondary border border-border' }}">
                        {{ strtoupper(substr($emp->nombre_comercial, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-text-primary text-sm">{{ $emp->nombre_comercial }}</div>
                        <div class="text-xs text-text-muted">{{ $emp->razon_social }}</div>
                    </div>
                </div>
                <x-status-badge :status="$emp->estado" />
            </div>

            <div class="text-xs text-text-secondary space-y-1">
                @if($esActivaEnSesion)
                    <x-badge variant="info" :dot="true">En sesión</x-badge>
                @endif
                <div class="font-mono">{{ $emp->tipo_documento?->value ?? 'NIT' }}: {{ $emp->nit }}{{ $emp->dv !== null && $emp->dv !== '' ? '-' . $emp->dv : '' }}</div>
                <div>{{ $emp->ciudad ?? 'Sin ciudad' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}</div>
                <div class="break-all text-text-muted">{{ $emp->email ?? $emp->telefono ?? 'Sin contacto' }}</div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3 border-t border-border">
                <div class="text-xs text-text-secondary tabular-nums">
                    {{ $emp->sucursales_count }} sedes &bull; {{ $emp->users_count }} usuarios
                </div>
                @include('empresas.partials.acciones')
            </div>
        </div>
        @empty
        <x-empty-state
            title="No se encontraron empresas registradas"
            description="Registra el primer comercio en la plataforma para habilitar sucursales, cajas y usuarios."
        >
            <a href="{{ route('empresas.create') }}" class="btn btn-primary">
                Registrar Primera Empresa
            </a>
        </x-empty-state>
        @endforelse
    </div>

    @if($empresas->hasPages())
        <div class="p-4 border border-border bg-surface-muted rounded-xl">
            {{ $empresas->links() }}
        </div>
    @endif

    {{-- Único modal reutilizable teletransportado a body para suspender/activar empresas --}}
    <x-modal-confirm name="confirm-empresa" />
</div>
@endsection
