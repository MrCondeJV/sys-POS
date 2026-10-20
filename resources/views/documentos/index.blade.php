@extends('layouts.app')

@section('title', 'Documentos de Venta')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-page-header
        title="Documentos Comerciales"
        subtitle="Historial de comprobantes emitidos: Facturas, Tickets POS y Documentos Equivalentes.">
        <x-slot:actions>
            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Ir a Ventas
            </a>
            <a href="{{ route('pos.index') }}" class="btn-primary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Terminal POS
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('documentos.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="q" class="label-base">Buscar Comprobante</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="FAC-000001, Cliente..."
                        class="input pl-9 text-xs">
                </div>
            </div>

            <div>
                <label for="tipo" class="label-base">Tipo Documento</label>
                <select name="tipo" id="tipo" class="input text-xs">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo') === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="estado" class="label-base">Estado</label>
                <select name="estado" id="estado" class="input text-xs">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $est)
                        <option value="{{ $est->value }}" {{ request('estado') === $est->value ? 'selected' : '' }}>
                            {{ $est->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="fecha_desde" class="label-base">Fecha Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                    class="input text-xs">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="btn-primary flex-1">
                    Filtrar
                </button>
                @if(request()->hasAny(['q', 'tipo', 'estado', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('documentos.index') }}" class="btn-secondary" title="Limpiar filtros">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Documentos -->
    <div class="card p-0 overflow-hidden">
        @if($documentos->isEmpty())
            <div class="py-12 text-center">
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="empty-state-title">No se encontraron documentos</p>
                    <p class="empty-state-desc">Los documentos comerciales se generarán automáticamente a medida que se registren ventas.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Comprobante</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Emisión</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">Impuesto</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentos as $doc)
                            <tr>
                                <td>
                                    <div class="font-mono font-semibold text-slate-900 text-xs">{{ $doc->numero_completo }}</div>
                                    @if($doc->venta)
                                        <div class="text-[11px] text-slate-400 font-mono">Venta: {{ $doc->venta->numero_venta }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-neutral">
                                        {{ $doc->tipo->label() }}
                                    </span>
                                </td>
                                <td class="text-xs">
                                    <div class="font-semibold text-slate-900">{{ $doc->cliente?->razon_social ?? 'Consumidor Final' }}</div>
                                    @if($doc->cliente?->numero_documento)
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $doc->cliente->numero_documento }}</div>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-600 font-mono">
                                    <div>{{ $doc->fecha_emision->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $doc->fecha_emision->format('H:i') }}</div>
                                </td>
                                <td class="text-xs text-slate-700 text-right font-mono tabular-nums">
                                    ${{ number_format($doc->subtotal, 2) }}
                                </td>
                                <td class="text-xs text-slate-700 text-right font-mono tabular-nums">
                                    ${{ number_format($doc->impuesto_total, 2) }}
                                </td>
                                <td class="text-xs text-slate-900 font-semibold text-right font-mono tabular-nums">
                                    ${{ number_format($doc->total, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $doc->estado->badgeClasses() }}">
                                        {{ $doc->estado->label() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('documentos.show', $doc) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                        Detalle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $documentos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
