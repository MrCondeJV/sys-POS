@extends('layouts.app')

@section('title', 'Documentos de Venta')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Documentos Comerciales</h1>
            <p class="mt-1 text-sm text-slate-500">
                Historial de comprobantes emitidos: Facturas, Tickets POS y Documentos Equivalentes.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Ir a Ventas
            </a>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Terminal POS
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 mb-6">
        <form method="GET" action="{{ route('documentos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="q" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Buscar Comprobante</label>
                <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="FAC-000001, Cliente..."
                    class="w-full text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-400">
            </div>

            <div>
                <label for="tipo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tipo Documento</label>
                <select name="tipo" id="tipo" class="w-full text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo') === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="estado" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Estado</label>
                <select name="estado" id="estado" class="w-full text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $est)
                        <option value="{{ $est->value }}" {{ request('estado') === $est->value ? 'selected' : '' }}>
                            {{ $est->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha_desde" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                    class="w-full text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['q', 'tipo', 'estado', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('documentos.index') }}" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Limpiar filtros">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Documentos -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        @if($documentos->isEmpty())
            <div class="p-12 text-center">
                <div class="inline-flex p-4 rounded-full bg-slate-100 text-slate-400 mb-3">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">No se encontraron documentos</h3>
                <p class="text-sm text-slate-500 mt-1">Los documentos comerciales se generarán automáticamente a medida que se registren ventas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Comprobante</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Emisión</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Subtotal</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Impuesto</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($documentos as $doc)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 font-mono text-sm">{{ $doc->numero_completo }}</div>
                                    @if($doc->venta)
                                        <div class="text-xs text-slate-400">Venta: {{ $doc->venta->numero_venta }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $doc->tipo->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    <div class="font-medium text-slate-900">{{ $doc->cliente?->razon_social ?? 'Consumidor Final' }}</div>
                                    @if($doc->cliente?->numero_documento)
                                        <div class="text-xs text-slate-400">NIT/CC: {{ $doc->cliente->numero_documento }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <div>{{ $doc->fecha_emision->format('d/m/Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $doc->fecha_emision->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 text-right font-mono">
                                    ${{ number_format($doc->subtotal, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 text-right font-mono">
                                    ${{ number_format($doc->impuesto_total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-bold text-right font-mono">
                                    ${{ number_format($doc->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $doc->estado->badgeClasses() }}">
                                        {{ $doc->estado->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('documentos.show', $doc) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg font-medium text-xs transition">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ver Detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100">
                {{ $documentos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
