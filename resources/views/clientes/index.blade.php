@extends('layouts.app')

@section('title', 'Catálogo de Clientes')

@section('content')
<div class="space-y-6">

    <!-- Encabezado & Botones de Acción -->
    <x-page-header
        title="Catálogo de Clientes"
        description="Gestión de clientes, identificación tributaria, parámetros de cartera y consumidor final para POS."
        :breadcrumbs="[
            ['label' => 'Clientes']
        ]">
        <x-slot:actions>
            <x-button variant="primary" href="{{ route('clientes.create') }}">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Cliente
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Tarjetas de Métricas Rápidas (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            title="Total Clientes"
            :value="number_format($totalClientes)"
            subtitle="Registrados en la empresa"
            icon="users"
        />
        <x-kpi-card
            title="Clientes Activos"
            :value="number_format($totalActivos)"
            subtitle="Habilitados para facturar"
            icon="check"
        />
        <x-kpi-card
            title="Con Crédito Comercial"
            :value="number_format($totalConCredito)"
            subtitle="Cupo y cartera autorizada"
            icon="credit-card"
        />
        <x-kpi-card
            title="Empresas / Jurídicas"
            :value="number_format($totalJuridicos)"
            subtitle="Sociedades con NIT"
            icon="building"
        />
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <x-filter-bar :action="route('clientes.index')" :clear-url="route('clientes.index')" :has-active-filters="!empty($term) || !empty($tipoPersona) || !empty($estado)">
        <div class="sm:col-span-12 lg:col-span-5 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term ?? '' }}"
                placeholder="Buscar por razón social, documento, teléfono..."
                class="input pl-9">
        </div>

        <div class="sm:col-span-6 lg:col-span-3">
            <select name="tipo_persona" class="input">
                <option value="">Todos los tipos de persona</option>
                @foreach(\App\Enums\TipoPersona::cases() as $tp)
                    <option value="{{ $tp->value }}" @selected(($tipoPersona ?? '') === $tp->value)>
                        {{ $tp->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="estado" class="input">
                <option value="">Todos los estados</option>
                <option value="ACTIVO" @selected(($estado ?? '') === 'ACTIVO')>Activo</option>
                <option value="INACTIVO" @selected(($estado ?? '') === 'INACTIVO')>Inactivo</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla Principal de Clientes (Desktop) -->
    <x-table class="hidden lg:block">
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-left">Cliente / Razón Social</th>
                <th class="hidden @4xl:table-cell px-4 py-3 text-left">Identificación</th>
                <th class="hidden @5xl:table-cell px-4 py-3 text-left">Contacto</th>
                <th class="px-4 py-3 text-right">Línea de Crédito</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </x-slot:head>

        @forelse($clientes as $cliente)
            <tr class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 flex-shrink-0 rounded-lg flex items-center justify-center font-bold text-xs {{ $cliente->isConsumidorFinal() ? 'bg-warning-bg text-warning-text border border-warning-border' : ($cliente->tipo_persona->value === 'JURIDICA' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                            @if($cliente->isConsumidorFinal())
                                CF
                            @else
                                {{ strtoupper(substr($cliente->razon_social, 0, 2)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="font-semibold text-slate-900 text-sm line-clamp-2">
                                    {{ $cliente->razon_social }}
                                </span>
                                @if($cliente->isConsumidorFinal())
                                    <x-badge color="warning">PREDETERMINADO POS</x-badge>
                                @endif
                            </div>
                            @if($cliente->nombre_comercial)
                                <span class="text-xs text-slate-500 block">
                                    Comercial: {{ $cliente->nombre_comercial }}
                                </span>
                            @endif
                            <span class="@4xl:hidden text-xs text-slate-600 font-mono block">
                                {{ $cliente->tipo_documento->value }} {{ $cliente->numero_documento }}
                            </span>
                            <span class="text-xs text-slate-400 block">
                                {{ $cliente->ciudad ?? 'Sin ciudad' }}{{ $cliente->departamento ? ', ' . $cliente->departamento : '' }}
                            </span>
                        </div>
                    </div>
                </td>
                <td class="hidden @4xl:table-cell px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-900 font-mono">
                        {{ $cliente->tipo_documento->value }} {{ $cliente->numero_documento }}
                    </div>
                    <div class="mt-1">
                        <x-badge color="neutral">
                            {{ $cliente->tipo_persona->label() }}
                        </x-badge>
                    </div>
                </td>
                <td class="hidden @5xl:table-cell px-4 py-3">
                    <div class="text-sm text-slate-700">
                        {{ $cliente->telefono ?? 'Sin teléfono' }}
                    </div>
                    <div class="text-xs text-slate-500 break-all">
                        {{ $cliente->email ?? 'Sin correo' }}
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                    @if($cliente->tieneCredito())
                        <div class="text-sm font-semibold font-mono tabular-nums text-slate-900">
                            ${{ number_format($cliente->cupo_credito, 2) }}
                        </div>
                        <div class="text-xs text-slate-500">
                            Plazo: {{ $cliente->plazo_dias }} días
                        </div>
                    @else
                        <span class="text-xs text-slate-400 italic">Sin crédito</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-center">
                    <x-status-badge :status="$cliente->estado->value === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO'" />
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end space-x-1">
                        <x-icon-button
                            href="{{ route('clientes.edit', $cliente) }}"
                            variant="secondary"
                            icon="pencil"
                            title="Editar cliente"
                        />

                        @if($cliente->puedeEliminarse())
                            <x-modal-confirm
                                name="delete-cliente-{{ $cliente->id }}"
                                title="Eliminar cliente {{ $cliente->razon_social }}"
                                message="¿Estás seguro de que deseas eliminar este cliente del directorio comercial?"
                                consequence="Se aplicará eliminación lógica (soft delete). Las facturas, ventas y cuentas por cobrar históricas asociadas conservarán su trazabilidad."
                                confirmText="Eliminar cliente"
                                variant="danger"
                                :action="route('clientes.destroy', $cliente)"
                                method="DELETE"
                            >
                                <x-slot:trigger>
                                    <x-icon-button
                                        type="button"
                                        variant="danger"
                                        icon="trash"
                                        title="Eliminar cliente"
                                    />
                                </x-slot:trigger>
                            </x-modal-confirm>
                        @else
                            <span class="p-1.5 text-slate-300 cursor-not-allowed" title="El Consumidor Final predeterminado no puede ser eliminado">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8">
                    <x-empty-state
                        title="No se encontraron clientes"
                        description="No hay clientes que coincidan con los criterios de búsqueda o filtros seleccionados."
                    >
                        <x-slot:actions>
                            <x-button variant="primary" href="{{ route('clientes.create') }}">
                                Registrar Cliente
                            </x-button>
                        </x-slot:actions>
                    </x-empty-state>
                </td>
            </tr>
        @endforelse
    </x-table>

    <!-- Vista Móvil y Tablet -->
    <div class="lg:hidden bg-white rounded-xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
        @forelse($clientes as $cliente)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="h-9 w-9 flex-shrink-0 rounded-lg flex items-center justify-center font-bold text-xs {{ $cliente->isConsumidorFinal() ? 'bg-warning-bg text-warning-text border border-warning-border' : ($cliente->tipo_persona->value === 'JURIDICA' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                            {{ $cliente->isConsumidorFinal() ? 'CF' : strtoupper(substr($cliente->razon_social, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 text-sm">{{ $cliente->razon_social }}</div>
                            <div class="text-xs text-slate-600 font-mono">{{ $cliente->tipo_documento->value }} {{ $cliente->numero_documento }}</div>
                        </div>
                    </div>
                    <x-status-badge :status="$cliente->estado->value === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO'" />
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    @if($cliente->isConsumidorFinal())
                        <x-badge color="warning">PREDETERMINADO POS</x-badge>
                    @endif
                    <div><span class="text-slate-400">Teléfono:</span> {{ $cliente->telefono ?? 'Sin teléfono' }}</div>
                    <div class="break-all"><span class="text-slate-400">Correo:</span> {{ $cliente->email ?? 'Sin correo' }}</div>
                    <div><span class="text-slate-400">Ciudad:</span> {{ $cliente->ciudad ?? 'Sin ciudad' }}{{ $cliente->departamento ? ', ' . $cliente->departamento : '' }}</div>
                </div>

                <div class="pt-2 flex items-center justify-between gap-3 border-t border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Línea de Crédito</span>
                        @if($cliente->tieneCredito())
                            <span class="font-bold font-mono text-sm tabular-nums text-slate-900">${{ number_format($cliente->cupo_credito, 2) }}</span>
                            <span class="text-[11px] text-slate-500">&bull; {{ $cliente->plazo_dias }} días</span>
                        @else
                            <span class="text-xs text-slate-400 italic">Sin crédito</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <x-icon-button href="{{ route('clientes.edit', $cliente) }}" variant="secondary" icon="pencil" title="Editar cliente" />
                        @if($cliente->puedeEliminarse())
                            <x-modal-confirm
                                name="delete-cliente-movil-{{ $cliente->id }}"
                                title="Eliminar cliente {{ $cliente->razon_social }}"
                                message="¿Estás seguro de que deseas eliminar este cliente del directorio comercial?"
                                consequence="Se aplicará eliminación lógica (soft delete). Las facturas, ventas y cuentas por cobrar históricas asociadas conservarán su trazabilidad."
                                confirmText="Eliminar cliente"
                                variant="danger"
                                :action="route('clientes.destroy', $cliente)"
                                method="DELETE"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar cliente" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state
                title="No se encontraron clientes"
                description="No hay clientes que coincidan con los criterios de búsqueda o filtros seleccionados."
            />
        @endforelse
    </div>

    @if($clientes->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50 rounded-xl">
            {{ $clientes->links() }}
        </div>
    @endif

</div>
@endsection
