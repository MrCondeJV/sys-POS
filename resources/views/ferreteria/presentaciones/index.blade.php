@extends('layouts.app')

@section('title', 'Presentaciones & Conversiones')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Presentaciones & Conversiones"
        description="Producto: {{ $producto->nombre }} — Unidad Base: {{ $producto->unidadMedida?->nombre ?? 'Unidad' }}"
        :breadcrumbs="[
            ['label' => 'Productos', 'url' => route('productos.index')],
            ['label' => 'Presentaciones']
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('productos.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Productos
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario de Creación -->
        @can('update', $producto)
        <div class="lg:col-span-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-sm">Nueva Presentación</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Configura empaques, bultos o múltiplos de venta.</p>
                </div>

                <form action="{{ route('productos.presentaciones.store', $producto) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre Presentación <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs"
                            placeholder="Ej. Caja x 100, Rollo 50m, Bulto 50kg">
                        @error('nombre') <p class="text-xs text-danger-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Factor de Conversión <span class="text-danger-text">*</span>
                        </label>
                        <input type="number" step="0.0001" name="factor_conversion" value="{{ old('factor_conversion', 1) }}" min="0.0001" required
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs font-mono tabular-nums">
                        <span class="text-[11px] text-slate-500 mt-1 block">Unidades base que contiene (Ej: 1 caja = 100 unidades -> factor = 100)</span>
                        @error('factor_conversion') <p class="text-xs text-danger-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Precio de Venta Presentación <span class="text-danger-text">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" min="0" required
                                class="block w-full pl-7 pr-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs font-mono tabular-nums">
                        </div>
                        @error('precio_venta') <p class="text-xs text-danger-text mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Unidad de Medida
                        </label>
                        <select name="unidad_medida_id"
                            class="input">
                            <option value="">Seleccione...</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}">{{ $u->nombre }} ({{ $u->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código de Barras (Opcional)
                        </label>
                        <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}"
                            class="block w-full px-3 py-2 font-mono uppercase border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>

                    <x-button variant="primary" type="submit" class="w-full justify-center">
                        Guardar Presentación
                    </x-button>
                </form>
            </div>
        </div>
        @endcan

        <!-- Listado de Presentaciones -->
        <div class="{{ auth()->user()->can('update', $producto) ? 'lg:col-span-8' : 'lg:col-span-12' }}">
            <x-table class="hidden lg:block">
                <x-slot:head>
                    <tr>
                        <th class="px-4 py-3 text-left">Presentación</th>
                        <th class="px-4 py-3 text-right">Factor</th>
                        <th class="px-4 py-3 text-left">Equivalencia Base</th>
                        <th class="px-4 py-3 text-right">Precio Venta</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </x-slot:head>

                @forelse($presentaciones as $pres)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3 text-slate-900 font-semibold text-sm">
                            <div class="flex items-center space-x-2">
                                <span>{{ $pres->nombre }}</span>
                                @if($pres->es_predeterminada)
                                    <x-badge color="brand">Default</x-badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-xs font-semibold text-slate-700 tabular-nums">
                            x{{ number_format($pres->factor_conversion, 2) }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            1 {{ $pres->nombre }} = {{ number_format($pres->factor_conversion, 2) }} {{ $producto->unidadMedida?->nombre ?? 'unidades' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-sm font-semibold text-slate-900 tabular-nums">
                            ${{ number_format($pres->precio_venta, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $producto)
                            <x-modal-confirm
                                title="¿Eliminar esta presentación?"
                                message="Se eliminará la presentación «{{ $pres->nombre }}» del producto."
                                action="{{ route('productos.presentaciones.destroy', [$producto, $pres]) }}"
                                method="DELETE"
                                confirmText="Eliminar"
                                variant="danger"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar presentación" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                            No hay presentaciones adicionales configuradas. El producto se comercializa únicamente en su unidad base ({{ $producto->unidadMedida?->nombre ?? 'Unidad' }}).
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <!-- Vista Móvil y Tablet -->
            <div class="lg:hidden bg-white rounded-xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
                @forelse($presentaciones as $pres)
                    <div class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 font-semibold text-slate-900 text-sm">
                                    <span>{{ $pres->nombre }}</span>
                                    @if($pres->es_predeterminada)
                                        <x-badge color="brand">Default</x-badge>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-600 mt-0.5">
                                    1 {{ $pres->nombre }} = {{ number_format($pres->factor_conversion, 2) }} {{ $producto->unidadMedida?->nombre ?? 'unidades' }}
                                </div>
                            </div>
                            <span class="font-mono text-xs font-semibold text-slate-700 tabular-nums">x{{ number_format($pres->factor_conversion, 2) }}</span>
                        </div>
                        <div class="pt-2 flex items-center justify-between gap-3 border-t border-slate-100">
                            <span class="font-mono text-base font-semibold text-slate-900 tabular-nums">${{ number_format($pres->precio_venta, 2) }}</span>
                            @can('update', $producto)
                            <x-modal-confirm
                                title="¿Eliminar esta presentación?"
                                message="Se eliminará la presentación «{{ $pres->nombre }}» del producto."
                                action="{{ route('productos.presentaciones.destroy', [$producto, $pres]) }}"
                                method="DELETE"
                                confirmText="Eliminar"
                                variant="danger"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar presentación" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-slate-500">
                        No hay presentaciones adicionales configuradas. El producto se comercializa únicamente en su unidad base ({{ $producto->unidadMedida?->nombre ?? 'Unidad' }}).
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
