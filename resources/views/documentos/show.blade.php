@extends('layouts.app')

@section('title', 'Documento ' . $documento->numero_completo)

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Botones de Acción Superiores -->
    <div class="flex items-center justify-between mb-6 print:hidden">
        <a href="{{ route('documentos.index') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a Documentos
        </a>

        <div class="flex items-center space-x-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Documento
            </button>

            @if($documento->documentoElectronico)
                <a href="{{ route('facturacion-electronica.show', $documento->documentoElectronico) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-semibold rounded-xl border border-indigo-200 shadow-sm transition">
                    ⚡ Ver Factura Electrónica ({{ $documento->documentoElectronico->consecutivo_completo }})
                </a>
            @elseif($documento->esEmitido())
                <form action="{{ route('facturacion-electronica.emitir', $documento) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition" onclick="return confirm('¿Transmitir esta factura electrónicamente a la DIAN?');">
                        ⚡ Emitir Factura Electrónica DIAN
                    </button>
                </form>
            @endif

            @can('anular', $documento)
                @if($documento->esEmitido())
                    <button type="button" @click="$dispatch('open-modal-anular')" class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 text-sm font-semibold rounded-xl border border-red-200 shadow-sm transition">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Anular Documento
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <!-- Comprobante / Factura Formato Oficial -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-10 print:shadow-none print:border-none print:p-0">
        <!-- Encabezado del comprobante -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between pb-8 border-b border-slate-200 gap-6">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
                    {{ $documento->empresa->nombre_comercial ?? 'EMPRESA COMERCIAL' }}
                </h1>
                <p class="text-sm font-semibold text-slate-600 mt-1">
                    {{ $documento->empresa->razon_social }}
                </p>
                <div class="text-xs text-slate-500 mt-2 space-y-0.5">
                    <p><span class="font-medium text-slate-700">NIT:</span> {{ $documento->empresa->nit ?? '900.000.000-1' }}</p>
                    <p><span class="font-medium text-slate-700">Sede:</span> {{ $documento->sucursal->nombre ?? 'Principal' }}</p>
                    <p>{{ $documento->sucursal->direccion ?? $documento->empresa->direccion }}</p>
                    <p>{{ $documento->sucursal->telefono ?? $documento->empresa->telefono }}</p>
                </div>
            </div>

            <div class="sm:text-right bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-xl">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border mb-2 {{ $documento->estado->badgeClasses() }}">
                    {{ $documento->estado->label() }}
                </span>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $documento->tipo->label() }}
                </div>
                <div class="text-2xl font-black text-indigo-600 font-mono tracking-tight mt-0.5">
                    {{ $documento->numero_completo }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    <p><span class="font-medium text-slate-700">Fecha de Emisión:</span> {{ $documento->fecha_emision->format('d/m/Y H:i') }}</p>
                    @if($documento->usuario)
                        <p><span class="font-medium text-slate-700">Cajero/Emisor:</span> {{ $documento->usuario->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="py-6 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cliente / Receptor</span>
                <p class="font-bold text-slate-900 text-base">{{ $documento->cliente?->razon_social ?? 'Consumidor Final' }}</p>
                <p class="text-slate-500 text-xs mt-0.5"><span class="font-medium text-slate-700">Documento / NIT:</span> {{ $documento->cliente?->numero_documento ?? '222222222222' }}</p>
                @if($documento->cliente?->direccion)
                    <p class="text-slate-500 text-xs"><span class="font-medium text-slate-700">Dirección:</span> {{ $documento->cliente->direccion }}</p>
                @endif
                @if($documento->cliente?->telefono)
                    <p class="text-slate-500 text-xs"><span class="font-medium text-slate-700">Teléfono:</span> {{ $documento->cliente->telefono }}</p>
                @endif
            </div>

            <div class="sm:text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Condiciones Comerciales</span>
                @if($documento->venta)
                    <p class="text-slate-700 text-xs"><span class="font-medium">Forma de Pago:</span> {{ $documento->venta->tipo_pago->value ?? 'CONTADO' }}</p>
                    <p class="text-slate-700 text-xs"><span class="font-medium">Medio de Pago:</span> {{ $documento->venta->metodo_pago ?? 'EFECTIVO' }}</p>
                    <p class="text-slate-700 text-xs"><span class="font-medium">Nº Venta POS:</span> {{ $documento->venta->numero_venta }}</p>
                @endif
            </div>
        </div>

        <!-- Tabla de Artículos -->
        <div class="py-6">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">
                        <th class="pb-3">Descripción</th>
                        <th class="pb-3 text-center">Cant.</th>
                        <th class="pb-3 text-right">Precio Unit.</th>
                        <th class="pb-3 text-right">IVA %</th>
                        <th class="pb-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($documento->venta && $documento->venta->detalles)
                        @foreach($documento->venta->detalles as $item)
                            <tr>
                                <td class="py-3.5 pr-3">
                                    <div class="font-medium text-slate-900">{{ $item->producto->nombre ?? 'Artículo' }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->producto->codigo ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-center font-mono">{{ number_format($item->cantidad, 2) }}</td>
                                <td class="py-3.5 px-3 text-right font-mono">${{ number_format($item->precio_unitario, 2) }}</td>
                                <td class="py-3.5 px-3 text-right font-mono">{{ number_format($item->impuesto_porcentaje ?? 0, 0) }}%</td>
                                <td class="py-3.5 pl-3 text-right font-bold text-slate-900 font-mono">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">Detalle consolidado del documento.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Totales y Resumen Financiero -->
        <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between gap-6">
            <div class="text-xs text-slate-500 max-w-sm">
                <span class="font-semibold text-slate-700 block mb-1">Observaciones / Notas:</span>
                <p>{{ $documento->observaciones ?: 'Sin observaciones registradas.' }}</p>

                <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-slate-600">
                    <p class="font-bold text-slate-800 mb-0.5">Régimen & Cumplimiento</p>
                    <p>Documento generado electrónicamente conforme a las normas de contabilidad comercial colombianas.</p>
                </div>
            </div>

            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Gravable:</span>
                    <span class="font-mono font-medium">${{ number_format($documento->subtotal, 2) }}</span>
                </div>
                @if($documento->descuento_total > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Descuento:</span>
                        <span class="font-mono font-medium">-${{ number_format($documento->descuento_total, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>IVA / Impuestos:</span>
                    <span class="font-mono font-medium">${{ number_format($documento->impuesto_total, 2) }}</span>
                </div>
                <div class="border-t border-slate-200 pt-2 flex justify-between text-base font-black text-slate-900">
                    <span>Total a Pagar:</span>
                    <span class="font-mono text-indigo-600 text-lg">${{ number_format($documento->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Anulación -->
@can('anular', $documento)
<div x-data="{ open: false }" @open-modal-anular.window="open = true" x-show="open" class="relative z-50" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100" @click.away="open = false">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Anular Documento {{ $documento->numero_completo }}</h3>
            <p class="text-sm text-slate-500 mb-4">Esta acción invalidará este comprobante comercial. Indique el motivo obligatorio:</p>
            <form action="{{ route('documentos.anular', $documento) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="motivo" rows="3" required placeholder="Motivo de la anulación..."
                        class="w-full text-sm border-slate-300 rounded-xl focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm">Confirmar Anulación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
