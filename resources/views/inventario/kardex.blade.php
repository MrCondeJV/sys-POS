@extends('layouts.app')

@section('title', 'Kardex de Movimientos — ' . $producto->nombre)

@section('content')
<div class="space-y-6 max-w-[1680px] mx-auto overflow-x-hidden">
    <!-- Header de Navegación -->
    <x-page-header
        title="Kardex Histórico Inmutable"
        subtitle="Trazabilidad legal y auditoría cronológica de cada entrada, salida, ajuste o traslado."
        :breadcrumbs="[
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Kardex de Producto']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-button variant="outline" size="sm" type="button" onclick="window.print()">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </x-button>

                <x-button variant="secondary" size="sm" href="{{ route('inventario.ajuste.create', ['producto_id' => $producto->id]) }}">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Ajustar Stock
                </x-button>

                <x-button variant="outline" size="sm" href="{{ route('inventario.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Ficha de Producto y Existencias Multisucursal -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center space-x-4 min-w-0">
                <div class="h-16 w-16 rounded-lg bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                    @if($producto->imagen_url)
                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="h-full w-full object-cover">
                    @else
                    <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex items-center space-x-2">
                        <x-badge status="neutral" size="sm">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</x-badge>
                        @if($producto->marca)
                        <span class="text-xs text-slate-600 font-medium">{{ $producto->marca->nombre }}</span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold text-slate-900 mt-1 truncate">{{ $producto->nombre }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-0.5">
                        @if($producto->codigo)
                        <span>SKU: <span class="font-mono font-semibold text-slate-700">{{ $producto->codigo }}</span></span>
                        @endif
                        @if($producto->codigo_barras)
                        <span>&bull; Barras: <span class="font-mono font-semibold text-slate-700">{{ $producto->codigo_barras }}</span></span>
                        @endif
                        <span>&bull; Unidad: <span class="font-semibold text-slate-700">{{ $producto->unidadMedida?->nombre ?? 'Unidad' }}</span></span>
                    </div>
                </div>
            </div>

            <!-- Balances Consolidados -->
            <div class="flex flex-wrap items-center gap-4 lg:border-l lg:border-slate-100 lg:pl-6">
                <div class="bg-slate-50/70 p-3 rounded-lg border border-slate-200 text-xs">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Stock Global</p>
                    <div class="text-lg font-bold text-slate-900 mt-0.5 tabular-nums">
                        {{ number_format($producto->stock, 2) }}
                        <span class="text-xs font-normal text-slate-500">{{ $producto->unidadMedida?->codigo ?? 'UND' }}</span>
                    </div>
                </div>

                <div class="bg-slate-50/70 p-3 rounded-lg border border-slate-200 text-xs">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Stock Mínimo</p>
                    <div class="text-lg font-bold text-slate-700 mt-0.5 tabular-nums">
                        {{ number_format($producto->stock_minimo, 2) }}
                    </div>
                </div>

                <div class="bg-slate-50/70 p-3 rounded-lg border border-slate-200 text-xs">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">PVP Final (con IVA)</p>
                    <div class="text-lg font-bold text-brand-700 mt-0.5 tabular-nums">
                        ${{ number_format($producto->calcularPrecioConIva(), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución de Existencias por Sucursal -->
        <div class="mt-4 pt-4 border-t border-slate-100">
            <h4 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Existencias Actuales por Sucursal</h4>
            <div class="flex flex-wrap gap-2">
                @forelse($existenciasPorSucursal as $inv)
                <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-md border text-xs {{ $inv->tieneBajoStock() ? 'bg-amber-50/60 border-amber-200 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-800' }}">
                    <span class="font-medium">{{ $inv->sucursal?->nombre ?? 'Sin sucursal' }}:</span>
                    <span class="font-bold tabular-nums">{{ number_format($inv->stock, 2) }}</span>
                    <span class="text-[10px] text-slate-400 tabular-nums">/ Mín {{ number_format($inv->stock_minimo, 2) }}</span>
                </div>
                @empty
                <span class="text-xs text-slate-400 italic">No hay existencias registradas en ninguna sucursal aún.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filtros de Kardex -->
    <x-filter-bar
        :action="route('inventario.kardex', $producto->id)"
        :has-active-filters="$sucursalId || $tipo || $desde || $hasta"
        :clear-url="route('inventario.kardex', $producto->id)"
    >
        <div class="sm:col-span-6 lg:col-span-3">
            <label class="block text-xs font-semibold text-text-secondary mb-1 uppercase tracking-wider">Sucursal</label>
            <select name="sucursal_id" class="input">
                <option value="">Todas las Sucursales</option>
                @foreach($sucursales as $suc)
                <option value="{{ $suc->id }}" {{ $sucursalId === $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-3">
            <label class="block text-xs font-semibold text-text-secondary mb-1 uppercase tracking-wider">Tipo Movimiento</label>
            <select name="tipo" class="input">
                <option value="">Todos los Tipos</option>
                @foreach($tiposMovimiento as $t)
                <option value="{{ $t->value }}" {{ $tipo === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary mb-1 uppercase tracking-wider">Desde</label>
            <input type="date" name="desde" value="{{ $desde }}" class="input">
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary mb-1 uppercase tracking-wider">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}" class="input">
        </div>
    </x-filter-bar>

    <!-- Tabla Histórica del Kardex -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">Fecha / Hora</th>
                <th class="px-4 py-3 text-left uppercase">Sucursal</th>
                <th class="px-4 py-3 text-left uppercase">Tipo Operación</th>
                <th class="px-4 py-3 text-left uppercase">Referencia / Motivo</th>
                <th class="px-4 py-3 text-left uppercase">Operador</th>
                <th class="px-4 py-3 text-right uppercase">Entrada (+)</th>
                <th class="px-4 py-3 text-right uppercase">Salida (-)</th>
                <th class="px-4 py-3 text-right uppercase font-bold">Saldo Sucursal</th>
                <th class="px-5 py-3 text-right uppercase">Costo Histórico</th>
            </tr>
        </x-slot:thead>

        @forelse($movimientos as $mov)
        @php
            $esEntrada = $mov->tipo->esEntrada();
        @endphp
        <tr class="hover:bg-slate-50/70 transition">
            <td class="px-5 py-3 text-xs text-slate-600 whitespace-nowrap tabular-nums">
                {{ $mov->created_at->format('d/m/Y H:i:s') }}
            </td>
            <td class="px-4 py-3 text-xs text-slate-700 font-medium whitespace-nowrap">
                {{ $mov->sucursal?->nombre ?? 'Sin sucursal' }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <x-badge :status="$esEntrada ? 'success' : 'danger'" size="sm">
                    {{ $mov->tipo->label() }}
                </x-badge>
            </td>
            <td class="px-4 py-3 text-xs text-slate-700">
                <div class="line-clamp-2">{{ $mov->referencia ?? '—' }}</div>
                @if($mov->notas)
                <span class="text-[11px] text-slate-400 line-clamp-1">{{ $mov->notas }}</span>
                @endif
            </td>
            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                {{ $mov->user?->name ?? 'Sistema' }}
            </td>
            <td class="px-4 py-3 text-right text-xs font-semibold text-success whitespace-nowrap tabular-nums">
                {{ $esEntrada ? '+' . number_format($mov->cantidad, 2) : '—' }}
            </td>
            <td class="px-4 py-3 text-right text-xs font-semibold text-danger whitespace-nowrap tabular-nums">
                {{ ! $esEntrada ? '-' . number_format($mov->cantidad, 2) : '—' }}
            </td>
            <td class="px-4 py-3 text-right text-xs font-bold text-slate-900 whitespace-nowrap tabular-nums">
                {{ number_format($mov->stock_posterior, 2) }}
            </td>
            <td class="px-5 py-3 text-right text-xs text-slate-600 whitespace-nowrap tabular-nums">
                ${{ number_format($mov->costo_unitario, 2) }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="px-5 py-12 text-center">
                <x-empty-state
                    title="No hay movimientos en Kardex"
                    description="No se han registrado movimientos de inventario con los criterios seleccionados."
                />
            </td>
        </tr>
        @endforelse

        @if($movimientos->hasPages())
        <x-slot:pagination>
            {{ $movimientos->links() }}
        </x-slot:pagination>
        @endif
    </x-table>
</div>
@endsection
