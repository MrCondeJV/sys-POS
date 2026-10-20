@extends('layouts.app')

@section('title', 'Plataforma SaaS — Gestión de Empresas')

@section('content')
@php
    $inactivasEmpresas = max(0, ($totalEmpresas ?? 0) - ($activasEmpresas ?? 0));
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Encabezado Principal -->
    <x-page-header
        title="Plataforma Multiempresa"
        subtitle="Administración centralizada de comercios, sedes y suscripciones operativas del sistema POS."
    >
        <x-slot:actions>
            <x-button
                variant="primary"
                size="md"
                href="{{ route('empresas.create') }}"
                icon="<svg class='w-4 h-4' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='1.75'><path stroke-linecap='round' stroke-linejoin='round' d='M12 4v16m8-8H4' /></svg>"
            >
                Registrar empresa
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- 4 KPIs de Plataforma -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Empresas Registradas"
            :value="number_format($totalEmpresas)"
            :context="number_format($totalSucursales) . ' sedes comerciales habilitadas'"
            icon="building"
        />

        <x-kpi-card
            label="Empresas Activas"
            :value="number_format($activasEmpresas)"
            :context="($totalEmpresas > 0 ? round(($activasEmpresas / $totalEmpresas) * 100) : 0) . '% del total operativo'"
            icon="check"
        />

        <x-kpi-card
            label="Usuarios de Plataforma"
            :value="number_format($totalUsuarios)"
            context="Cuentas con acceso al sistema"
            icon="users"
        />

        <x-kpi-card
            label="Suspendidas / Inactivas"
            :value="number_format($inactivasEmpresas)"
            :context="$inactivasEmpresas > 0 ? 'Comercios con acceso pausado' : 'Todas las empresas operativas'"
            :alert="$inactivasEmpresas > 0 ? 'warning' : false"
            icon="alert"
        />
    </div>

    <!-- Barra de Filtros -->
    <x-filter-bar
        :action="route('empresas.index')"
        :resetUrl="route('empresas.index')"
        :hasActiveFilters="request()->hasAny(['buscar', 'estado'])"
    >
        <div class="sm:col-span-12 lg:col-span-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    class="input pl-9"
                    placeholder="Buscar por nombre comercial, razón social, NIT o ciudad..."
                />
            </div>
        </div>

        <div class="sm:col-span-12 lg:col-span-3">
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
                <th>Empresa / Comercio</th>
                <th>NIT / Identificación</th>
                <th>Ubicación & Contacto</th>
                <th class="text-center">Sedes / Usuarios</th>
                <th class="text-center">Estado</th>
                <th class="text-right">Acciones</th>
            </tr>
        </x-slot:header>

        @forelse($empresas as $emp)
            <tr class="{{ $emp->id === $empresaActivaId ? 'bg-brand-50/60' : '' }}">
                <td>
                    <div class="flex items-center gap-3">
                        @if($emp->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($emp->logo_path))
                            <div class="h-9 w-9 rounded-lg p-1 bg-surface border border-border-strong shadow-xs flex items-center justify-center overflow-hidden shrink-0">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($emp->logo_path) }}" alt="{{ $emp->nombre_comercial }}" class="h-full w-full object-contain">
                            </div>
                        @else
                            <div class="h-9 w-9 rounded-lg flex items-center justify-center font-bold text-xs text-white {{ $emp->id === $empresaActivaId ? 'bg-brand-600' : 'bg-brand-700' }} shrink-0 shadow-xs">
                                {{ mb_strtoupper(mb_substr($emp->nombre_comercial, 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-text-primary flex items-center gap-2">
                                <span class="truncate">{{ $emp->nombre_comercial }}</span>
                                @if($emp->id === $empresaActivaId)
                                    <x-badge variant="info" size="sm" :dot="true">En gestión</x-badge>
                                @endif
                            </div>
                            <div class="text-xs text-text-secondary truncate max-w-xs mt-0.5">{{ $emp->razon_social }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="font-mono text-xs font-semibold text-text-primary tabular-nums">
                        {{ $emp->nit }}{{ $emp->dv ? '-' . $emp->dv : '' }}
                    </div>
                    <div class="text-[11px] text-text-muted mt-0.5">{{ $emp->tipo_documento }} · {{ $emp->moneda ?? 'COP' }}</div>
                </td>
                <td>
                    <div class="text-xs font-medium text-text-primary">
                        {{ $emp->ciudad ?? 'N/A' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}
                    </div>
                    <div class="text-xs text-text-secondary truncate max-w-[220px] mt-0.5">
                        {{ $emp->email ?? $emp->telefono ?? 'Sin contacto registrado' }}
                    </div>
                </td>
                <td class="text-center">
                    <div class="inline-flex items-center justify-center gap-2">
                        <x-badge variant="neutral" size="sm">
                            {{ $emp->sucursales_count }} {{ $emp->sucursales_count === 1 ? 'sede' : 'sedes' }}
                        </x-badge>
                        <x-badge variant="neutral" size="sm">
                            {{ $emp->users_count }} {{ $emp->users_count === 1 ? 'usuario' : 'usuarios' }}
                        </x-badge>
                    </div>
                </td>
                <td class="text-center">
                    <x-status-badge :status="$emp->estado" size="sm" />
                </td>
                <td class="text-right">
                    <div class="inline-flex items-center justify-end gap-1.5">
                        @if($emp->id === $empresaActivaId)
                            <form method="POST" action="{{ route('empresas.salir') }}" class="inline">
                                @csrf
                                <x-button type="submit" variant="outline" size="sm" title="Salir del contexto de este negocio">
                                    Salir del negocio
                                </x-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('empresas.seleccionar') }}" class="inline">
                                @csrf
                                <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
                                <x-button type="submit" variant="secondary" size="sm" title="Gestionar {{ $emp->nombre_comercial }} como Super Admin">
                                    Gestionar
                                </x-button>
                            </form>
                        @endif

                        <x-icon-button
                            variant="ghost"
                            size="sm"
                            icon="pencil"
                            href="{{ route('empresas.edit', $emp) }}"
                            title="Editar empresa {{ $emp->nombre_comercial }}"
                            aria-label="Editar empresa {{ $emp->nombre_comercial }}"
                        />

                        @php
                            $esEmpresaActiva = $emp->estado === \App\Enums\EstadoGeneral::ACTIVO;
                        @endphp
                        <x-modal-confirm
                            name="toggle-empresa-{{ $emp->id }}"
                            :title="$esEmpresaActiva ? '¿Suspender empresa ' . $emp->nombre_comercial . '?' : '¿Reactivar empresa ' . $emp->nombre_comercial . '?'"
                            :message="$esEmpresaActiva
                                ? 'Al suspender este comercio, sus usuarios no podrán iniciar sesión ni registrar transacciones hasta que la cuenta sea reactivada.'
                                : 'Al reactivar este comercio, sus colaboradores recuperarán de inmediato el acceso operativo a sus sucursales y cajas.'"
                            :consequence="$esEmpresaActiva ? 'Se bloqueará el acceso operativo de ' . $emp->users_count . ' usuario(s) en ' . $emp->sucursales_count . ' sede(s).' : null"
                            :confirmText="$esEmpresaActiva ? 'Sí, suspender empresa' : 'Sí, reactivar empresa'"
                            :variant="$esEmpresaActiva ? 'danger' : 'primary'"
                            :action="route('empresas.toggle-estado', $emp)"
                            method="POST"
                        >
                            <x-slot:trigger>
                                <x-icon-button
                                    :variant="$esEmpresaActiva ? 'danger' : 'secondary'"
                                    size="sm"
                                    :title="$esEmpresaActiva ? 'Suspender comercio' : 'Reactivar comercio'"
                                    :aria-label="($esEmpresaActiva ? 'Suspender comercio ' : 'Reactivar comercio ') . $emp->nombre_comercial"
                                >
                                    @if($esEmpresaActiva)
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @endif
                                </x-icon-button>
                            </x-slot:trigger>
                        </x-modal-confirm>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-0">
                    <x-empty-state
                        title="No se encontraron comercios registrados"
                        description="Comienza registrando la primera empresa en la plataforma o restablece los filtros de búsqueda."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('empresas.create') }}">
                            Registrar empresa
                        </x-button>
                    </x-empty-state>
                </td>
            </tr>
        @endforelse

        @if($empresas->hasPages())
            <x-slot:footer>
                <div class="text-xs text-text-secondary tabular-nums">
                    Mostrando {{ $empresas->firstItem() ?? 0 }}–{{ $empresas->lastItem() ?? 0 }} de {{ $empresas->total() }} empresas
                </div>
                <div>
                    {{ $empresas->links() }}
                </div>
            </x-slot:footer>
        @endif
    </x-table>
</div>
@endsection
