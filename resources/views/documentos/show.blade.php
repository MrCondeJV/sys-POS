@extends('layouts.app')

@section('title', 'Documento ' . $documento->numero_completo)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Botones de Acción Superiores -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('documentos.index') }}" class="btn-secondary">
            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a Documentos
        </a>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir
            </button>

            @if($documento->documentoElectronico)
                <a href="{{ route('facturacion-electronica.show', $documento->documentoElectronico) }}" class="btn-secondary">
                    Ver Factura Electrónica ({{ $documento->documentoElectronico->consecutivo_completo }})
                </a>
            @elseif($documento->esEmitido() && auth()->user()->can('documentos.emitir'))
                <x-modal-confirm
                    title="¿Emitir factura electrónica a la DIAN?"
                    message="Se generará y transmitirá el documento electrónico oficial a partir del comprobante {{ $documento->numero_completo }}."
                    action="{{ route('facturacion-electronica.emitir', $documento) }}"
                    method="POST"
                    confirmText="Transmitir a DIAN"
                    variant="primary"
                >
                    <x-slot:trigger>
                        <button type="button" class="btn-primary">
                            Emitir DIAN
                        </button>
                    </x-slot:trigger>
                </x-modal-confirm>
            @endif

            @can('anular', $documento)
                @if($documento->esEmitido())
                    <button type="button" @click="$dispatch('open-modal-anular')" class="btn-danger">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Anular Documento
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <!-- Comprobante / Factura Formato Oficial -->
    <div class="card p-8 sm:p-10 print:shadow-none print:border-none print:p-0">
        <!-- Encabezado del comprobante -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between pb-8 border-b border-slate-200 gap-6">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 uppercase">
                    {{ $documento->empresa->nombre_comercial ?? 'EMPRESA COMERCIAL' }}
                </h1>
                <p class="text-xs font-semibold text-slate-600 mt-1">
                    {{ $documento->empresa->razon_social }}
                </p>
                <div class="text-xs text-slate-500 mt-2 space-y-0.5">
                    <p><span class="font-medium text-slate-700">NIT:</span> <span class="font-mono">{{ $documento->empresa->nit ?? '900.000.000-1' }}</span></p>
                    <p><span class="font-medium text-slate-700">Sede:</span> {{ $documento->sucursal?->nombre ?? 'Principal' }}</p>
                    <p>{{ $documento->sucursal?->direccion ?? $documento->empresa?->direccion }}</p>
                    <p>{{ $documento->sucursal?->telefono ?? $documento->empresa?->telefono }}</p>
                </div>
            </div>

            <div class="sm:text-right bg-slate-50 sm:bg-transparent p-4 sm:p-0 rounded-lg">
                <span class="badge {{ $documento->estado->badgeClasses() }} mb-2">
                    {{ $documento->estado->label() }}
                </span>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    {{ $documento->tipo->label() }}
                </div>
                <div class="text-xl font-bold text-slate-900 font-mono tracking-tight mt-0.5">
                    {{ $documento->numero_completo }}
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    <p><span class="font-medium text-slate-700">Fecha de Emisión:</span> <span class="font-mono">{{ $documento->fecha_emision->format('d/m/Y H:i') }}</span></p>
                    @if($documento->usuario)
                        <p><span class="font-medium text-slate-700">Cajero/Emisor:</span> {{ $documento->usuario->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Datos del Cliente -->
        <div class="py-6 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="kpi-label block mb-1">Cliente / Receptor</span>
                <p class="font-semibold text-slate-900 text-sm">{{ $documento->cliente?->razon_social ?? 'Consumidor Final' }}</p>
                <p class="text-slate-500 text-xs mt-0.5"><span class="font-medium text-slate-700">Documento / NIT:</span> <span class="font-mono">{{ $documento->cliente?->numero_documento ?? '222222222222' }}</span></p>
                @if($documento->cliente?->direccion)
                    <p class="text-slate-500 text-xs"><span class="font-medium text-slate-700">Dirección:</span> {{ $documento->cliente->direccion }}</p>
                @endif
                @if($documento->cliente?->telefono)
                    <p class="text-slate-500 text-xs"><span class="font-medium text-slate-700">Teléfono:</span> <span class="font-mono tabular-nums">{{ $documento->cliente->telefono }}</span></p>
                @endif
            </div>

            <div class="sm:text-right">
                <span class="kpi-label block mb-1">Condiciones Comerciales</span>
                @if($documento->venta)
                    <p class="text-slate-700 text-xs"><span class="font-medium">Forma de Pago:</span> {{ $documento->venta->tipo_pago->value ?? 'CONTADO' }}</p>
                    <p class="text-slate-700 text-xs"><span class="font-medium">Medio de Pago:</span> {{ $documento->venta->metodo_pago ?? 'EFECTIVO' }}</p>
                    <p class="text-slate-700 text-xs"><span class="font-medium">Nº Venta POS:</span> <span class="font-mono">{{ $documento->venta->numero_venta }}</span></p>
                @endif
            </div>
        </div>

        <!-- Tabla de Artículos -->
        <div class="py-6 overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">IVA %</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if($documento->venta && $documento->venta->detalles)
                        @foreach($documento->venta->detalles as $item)
                            <tr>
                                <td>
                                    <div class="font-semibold text-slate-900 text-xs">{{ $item->producto->nombre ?? 'Artículo' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->producto->codigo ?? '' }}</div>
                                </td>
                                <td class="text-center font-mono text-xs text-slate-800 tabular-nums">{{ number_format($item->cantidad, 2) }}</td>
                                <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($item->precio_unitario, 2) }}</td>
                                <td class="text-right font-mono text-xs text-slate-500 tabular-nums">{{ number_format($item->impuesto_porcentaje ?? 0, 0) }}%</td>
                                <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($item->subtotal, 2) }}</td>
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

                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-slate-600">
                    <p class="font-semibold text-slate-800 mb-0.5">Régimen & Cumplimiento</p>
                    <p>Documento generado electrónicamente conforme a las normas de contabilidad comercial colombianas.</p>
                </div>
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Gravable:</span>
                    <span class="font-mono font-semibold text-slate-800 tabular-nums">${{ number_format($documento->subtotal, 2) }}</span>
                </div>
                @if($documento->descuento_total > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Descuento:</span>
                        <span class="font-mono font-semibold tabular-nums">-${{ number_format($documento->descuento_total, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>IVA / Impuestos:</span>
                    <span class="font-mono font-semibold text-slate-800 tabular-nums">${{ number_format($documento->impuesto_total, 2) }}</span>
                </div>
                <div class="border-t border-slate-200 pt-2 flex justify-between text-sm font-bold text-slate-900">
                    <span>Total a Pagar:</span>
                    <span class="font-mono text-base tabular-nums">${{ number_format($documento->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Anulación -->
@can('anular', $documento)
<div x-data="{ open: false }" @open-modal-anular.window="open = true" x-show="open" class="relative z-50" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.away="open = false">
            <h3 class="text-base font-semibold text-slate-900 mb-1">Anular Documento {{ $documento->numero_completo }}</h3>
            <p class="text-xs text-slate-500 mb-4">Esta acción invalidará este comprobante comercial. Indique el motivo obligatorio:</p>
            <form action="{{ route('documentos.anular', $documento) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="motivo" rows="3" required placeholder="Motivo de la anulación..."
                        class="input text-xs"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" @click="open = false" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-danger">Confirmar Anulación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
