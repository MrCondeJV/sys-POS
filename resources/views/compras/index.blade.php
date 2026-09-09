@extends('layouts.app')

@section('title', 'Historial de Compras')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">
    <!-- Encabezado de la página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Fase 6
                </span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Facturas de Compra de Mercancía</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Adquisición de inventario a proveedores, control de costos unitarios e ingreso directo a Kardex.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Listado
            </button>
            <a href="{{ route('compras.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Nueva Compra
            </a>
        </div>
    </div>

    <!-- KPIs del Módulo de Compras -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Inversión Registrada</div>
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">${{ number_format($montoTotalRegistrado, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Total de compras activas</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Facturas Registradas</div>
                <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-indigo-600">{{ number_format($comprasRegistradas) }}</div>
            <div class="text-xs text-slate-500 mt-1">Con stock ingresado a sedes</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Facturas Anuladas</div>
                <div class="p-2 rounded-xl bg-red-50 text-red-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-red-600">{{ number_format($comprasAnuladas) }}</div>
            <div class="text-xs text-slate-500 mt-1">Mercancía revertida al proveedor</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transacciones</div>
                <div class="p-2 rounded-xl bg-slate-100 text-slate-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-800">{{ number_format($totalCompras) }}</div>
            <div class="text-xs text-slate-500 mt-1">Histórico completo</div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form action="{{ route('compras.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Búsqueda textual -->
            <div class="lg:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term }}"
                    placeholder="Factura # o razón social..."
                    class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <!-- Filtro Sucursal -->
            <div>
                <select name="sucursal_id"
                    class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Proveedor -->
            <div>
                <select name="proveedor_id"
                    class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">Todos los Proveedores</option>
                    @foreach($proveedores as $p)
                        <option value="{{ $p->id }}" {{ $proveedorId == $p->id ? 'selected' : '' }}>
                            {{ $p->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Estado -->
            <div>
                <select name="estado"
                    class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">Cualquier Estado</option>
                    <option value="REGISTRADA" {{ $estado === 'REGISTRADA' ? 'selected' : '' }}>Registradas</option>
                    <option value="ANULADA" {{ $estado === 'ANULADA' ? 'selected' : '' }}>Anuladas</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition">
                    Filtrar
                </button>
                @if($term || $sucursalId || $proveedorId || $estado || $desde || $hasta)
                    <a href="{{ route('compras.index') }}"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition flex items-center justify-center">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Compras: Adaptable Desktop Table / Mobile Cards -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop (100% responsive, sin scroll horizontal) -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Factura #</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Proveedor</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Sede Destino</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Pago</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($compras as $c)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Fecha -->
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $c->fecha_emision->format('d/m/Y') }}
                        </td>

                        <!-- Factura # -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-sm text-slate-900">
                                #{{ $c->numero_factura }}
                            </span>
                        </td>

                        <!-- Proveedor -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-sm">{{ $c->proveedor?->razon_social ?? 'Proveedor Eliminado' }}</div>
                            <div class="text-xs text-slate-400 font-mono">
                                {{ $c->proveedor?->tipo_documento?->value }}: {{ $c->proveedor?->numero_documento }}
                            </div>
                        </td>

                        <!-- Sede Destino -->
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-700">
                            <div class="flex items-center">
                                <span class="h-2 w-2 rounded-full bg-indigo-500 mr-2"></span>
                                {{ $c->sucursal?->nombre ?? 'Sede N/A' }}
                            </div>
                        </td>

                        <!-- Pago -->
                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $c->tipo_pago?->value === 'CONTADO' ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $c->tipo_pago?->value ?? 'CONTADO' }}
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="px-5 py-4 whitespace-nowrap text-right font-black text-sm text-slate-900">
                            ${{ number_format($c->total, 2) }}
                        </td>

                        <!-- Estado -->
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            @if($c->isRegistrada())
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Registrada
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> Anulada
                                </span>
                            @endif
                        </td>

                        <!-- Acción -->
                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('compras.show', $c) }}?print=1" target="_blank"
                                    class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition shadow-sm"
                                    title="Imprimir Comprobante Oficial">
                                    <svg class="h-3.5 w-3.5 mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Imprimir
                                </a>
                                <a href="{{ route('compras.show', $c) }}"
                                    class="inline-flex items-center px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                    Detalle &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <div class="max-w-sm mx-auto">
                                <svg class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-700">No se encontraron facturas de compra</p>
                                <p class="text-xs text-slate-400 mt-1">Registra facturas comerciales para reabastecer existencias de productos en tus sedes.</p>
                                <a href="{{ route('compras.create') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition">
                                    Registrar Factura
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil / Tablet (Cards modernas sin scroll horizontal) -->
        <div class="block lg:hidden divide-y divide-slate-100">
            @forelse($compras as $c)
            <div class="p-4 sm:p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-bold text-slate-900 text-base font-mono">Factura #{{ $c->numero_factura }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Fecha: {{ $c->fecha_emision->format('d/m/Y') }}
                        </div>
                    </div>
                    @if($c->isRegistrada())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Registrada
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                            Anulada
                        </span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div><span class="text-slate-400">Proveedor:</span> <span class="font-bold text-slate-800">{{ $c->proveedor?->razon_social ?? 'Eliminado' }}</span></div>
                    <div><span class="text-slate-400">Sede Destino:</span> {{ $c->sucursal?->nombre ?? 'N/A' }}</div>
                    <div><span class="text-slate-400">Tipo de Pago:</span> {{ $c->tipo_pago?->value ?? 'CONTADO' }}</div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400">Total Factura:</span>
                        <span class="font-black text-slate-900 text-sm ml-1">${{ number_format($c->total, 2) }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('compras.show', $c) }}?print=1" target="_blank"
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition shadow-sm">
                            <svg class="h-3.5 w-3.5 mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Imprimir
                        </a>
                        <a href="{{ route('compras.show', $c) }}"
                            class="text-xs font-bold text-slate-700 hover:text-indigo-600">
                            Detalle &rarr;
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                No hay compras registradas en este período o filtro.
            </div>
            @endforelse
        </div>

        @if($compras->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $compras->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
