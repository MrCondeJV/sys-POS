@extends('layouts.app')

@section('title', 'Kardex de Movimientos — ' . $producto->nombre)

@section('content')
<div class="space-y-6 max-w-[1680px] mx-auto overflow-x-hidden">
    <!-- Breadcrumb y Header de Navegación -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-indigo-600 transition">Inventario</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Kardex de Producto</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Kardex Histórico Inmutable</h1>
            <p class="text-sm text-slate-500 font-medium">Trazabilidad legal y auditoría cronológica de cada entrada, salida, ajuste o traslado.</p>
        </div>

        <div class="flex items-center space-x-2.5">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Kardex
            </button>

            <a href="{{ route('inventario.ajuste.create', ['producto_id' => $producto->id]) }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Ajustar Stock
            </a>

            <a href="{{ route('inventario.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Inventario
            </a>
        </div>
    </div>

    <!-- Tarjeta Maestra de Ficha de Producto y Existencias Multisucursal -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Info básica del producto con fotografía -->
            <div class="flex items-center space-x-4 min-w-0">
                <div class="h-20 w-20 rounded-2xl bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                    @if($producto->imagen_url)
                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="h-full w-full object-cover">
                    @else
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ $producto->categoria?->nombre ?? 'Sin categoría' }}
                        </span>
                        @if($producto->marca)
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-600">
                            {{ $producto->marca->nombre }}
                        </span>
                        @endif
                    </div>
                    <h2 class="text-xl font-black text-slate-900 mt-1 truncate">{{ $producto->nombre }}</h2>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                        @if($producto->codigo)
                        <span>SKU: <span class="font-mono font-bold text-slate-700">{{ $producto->codigo }}</span></span>
                        @endif
                        @if($producto->codigo_barras)
                        <span>• Código de Barras: <span class="font-mono font-bold text-slate-700">{{ $producto->codigo_barras }}</span></span>
                        @endif
                        <span>• Unidad: <span class="font-bold text-slate-700">{{ $producto->unidadMedida?->nombre ?? 'Unidad' }}</span></span>
                    </div>
                </div>
            </div>

            <!-- Balances Consolidados -->
            <div class="flex flex-wrap items-center gap-4 lg:border-l lg:border-slate-100 lg:pl-6">
                <div class="text-center sm:text-left bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stock Global</p>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">
                        {{ number_format($producto->stock, 2) }}
                        <span class="text-xs font-normal text-slate-500">{{ $producto->unidadMedida?->codigo ?? 'UND' }}</span>
                    </div>
                </div>

                <div class="text-center sm:text-left bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stock Mínimo</p>
                    <div class="text-2xl font-black text-slate-600 mt-0.5">
                        {{ number_format($producto->stock_minimo, 2) }}
                    </div>
                </div>

                <div class="text-center sm:text-left bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">PVP Final (IVA Inc)</p>
                    <div class="text-2xl font-black text-indigo-600 mt-0.5">
                        ${{ number_format($producto->calcularPrecioConIva(), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución de Existencias por Sucursal -->
        <div class="mt-5 pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5">Existencias Actuales por Sucursal</h4>
            <div class="flex flex-wrap gap-2.5">
                @forelse($existenciasPorSucursal as $inv)
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl border {{ $inv->tieneBajoStock() ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-slate-50 border-slate-200 text-slate-800' }}">
                    <span class="text-xs font-semibold">{{ $inv->sucursal->nombre }}:</span>
                    <span class="text-xs font-black">{{ number_format($inv->stock, 2) }}</span>
                    <span class="text-[10px] text-slate-400">/ Mín {{ number_format($inv->stock_minimo, 2) }}</span>
                </div>
                @empty
                <span class="text-xs text-slate-400 italic">No hay existencias registradas en ninguna sucursal aún.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Filtros de Kardex -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('inventario.kardex', $producto->id) }}" class="flex flex-col sm:flex-row flex-wrap gap-3 items-end">
            <!-- Sucursal -->
            <div class="w-full sm:w-56">
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Sucursal</label>
                <select name="sucursal_id" class="block w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-700 transition">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $suc)
                    <option value="{{ $suc->id }}" {{ $sucursalId === $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo de Movimiento -->
            <div class="w-full sm:w-56">
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Tipo de Movimiento</label>
                <select name="tipo" class="block w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-700 transition">
                    <option value="">Todos los Tipos</option>
                    @foreach($tiposMovimiento as $t)
                    <option value="{{ $t->value }}" {{ $tipo === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fecha Desde -->
            <div class="w-full sm:w-40">
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="block w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-800 transition">
            </div>

            <!-- Fecha Hasta -->
            <div class="w-full sm:w-40">
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="block w-full py-2.5 px-3 text-sm border border-slate-300 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-medium text-slate-800 transition">
            </div>

            <!-- Botones -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition shadow-sm">
                    Filtrar Kardex
                </button>
                @if($sucursalId || $tipo || $desde || $hasta)
                <a href="{{ route('inventario.kardex', $producto->id) }}" class="px-3.5 py-2.5 text-slate-500 hover:text-slate-900 text-sm font-semibold transition">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Histórica del Kardex -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($movimientos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-100">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="py-3 px-4">Fecha / Hora</th>
                        <th scope="col" class="py-3 px-3">Sucursal</th>
                        <th scope="col" class="py-3 px-3">Tipo de Operación</th>
                        <th scope="col" class="py-3 px-4">Referencia / Motivo</th>
                        <th scope="col" class="py-3 px-3">Operador</th>
                        <th scope="col" class="py-3 px-3 text-right">Entrada (+)</th>
                        <th scope="col" class="py-3 px-3 text-right">Salida (-)</th>
                        <th scope="col" class="py-3 px-3 text-right font-black">Saldo Sucursal</th>
                        <th scope="col" class="py-3 px-4 text-right">Costo Histórico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($movimientos as $mov)
                    @php
                        $esEntrada = $mov->tipo->esEntrada();
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Fecha / Hora -->
                        <td class="py-3 px-4 whitespace-nowrap text-xs">
                            <span class="font-bold text-slate-800">{{ $mov->created_at->format('d/m/Y') }}</span>
                            <span class="text-slate-400 ml-1">{{ $mov->created_at->format('H:i') }}</span>
                        </td>

                        <!-- Sucursal -->
                        <td class="py-3 px-3 whitespace-nowrap text-xs">
                            <span class="font-bold text-slate-800">{{ $mov->sucursal->nombre }}</span>
                            @if($mov->sucursalDestino)
                            <div class="text-[10px] text-indigo-600 font-semibold">
                                {{ $mov->tipo === \App\Enums\TipoMovimientoInventario::TRASLADO_SALIDA ? 'Hacia: ' : 'Desde: ' }} {{ $mov->sucursalDestino->nombre }}
                            </div>
                            @endif
                        </td>

                        <!-- Tipo de Operación -->
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $mov->tipo->badgeClasses() }}">
                                {{ $mov->tipo->label() }}
                            </span>
                        </td>

                        <!-- Referencia / Motivo -->
                        <td class="py-3 px-4 text-xs max-w-xs">
                            <div class="font-bold text-slate-900 truncate" title="{{ $mov->referencia }}">{{ $mov->referencia }}</div>
                            @if($mov->notas)
                            <div class="text-slate-400 truncate text-[11px]" title="{{ $mov->notas }}">{{ $mov->notas }}</div>
                            @endif
                        </td>

                        <!-- Operador -->
                        <td class="py-3 px-3 whitespace-nowrap text-xs text-slate-600">
                            {{ $mov->user?->name ?? 'Sistema / Automático' }}
                        </td>

                        <!-- Entrada (+) -->
                        <td class="py-3 px-3 text-right whitespace-nowrap">
                            @if($esEntrada)
                            <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg text-xs">
                                +{{ number_format($mov->cantidad, 2) }}
                            </span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        <!-- Salida (-) -->
                        <td class="py-3 px-3 text-right whitespace-nowrap">
                            @if(! $esEntrada)
                            <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-lg text-xs">
                                -{{ number_format($mov->cantidad, 2) }}
                            </span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        <!-- Saldo Sucursal -->
                        <td class="py-3 px-3 text-right whitespace-nowrap">
                            <span class="text-sm font-black text-slate-900">
                                {{ number_format($mov->stock_posterior, 2) }}
                            </span>
                            <div class="text-[10px] text-slate-400">Antes: {{ number_format($mov->stock_anterior, 2) }}</div>
                        </td>

                        <!-- Costo Histórico -->
                        <td class="py-3 px-4 text-right whitespace-nowrap text-xs text-slate-600 font-mono">
                            ${{ number_format($mov->costo_unitario, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $movimientos->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-12 px-4">
            <div class="h-14 w-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">No hay movimientos registrados para este producto</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                Cada vez que realices una compra, venta, ajuste o traslado, se asentará aquí el registro histórico correspondiente.
            </p>
            <div class="mt-4">
                <a href="{{ route('inventario.ajuste.create', ['producto_id' => $producto->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl hover:bg-indigo-700 transition">
                    Asentar Primer Ajuste de Stock
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
