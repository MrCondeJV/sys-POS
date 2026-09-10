@extends('layouts.app')

@section('title', 'Venta: ' . $venta->numero_venta)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ modalAnularOpen: false }">

    <!-- Encabezado y Navegación -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('ventas.index') }}" class="hover:text-indigo-600 transition">Ventas Realizadas</a>
                <span>/</span>
                <span class="text-slate-800">Ficha de Venta</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight font-mono">{{ $venta->numero_venta }}</h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $venta->estado->badgeClasses() }}">
                    {{ $venta->estado->label() }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $venta->tipo_pago->badgeClasses() }}">
                    {{ $venta->tipo_pago->label() }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Emitida el {{ $venta->fecha->format('d/m/Y \a \l\a\s H:i') }} &bull; Sucursal: {{ $venta->sucursal->nombre }}</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('ventas.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>

            <a href="{{ route('ventas.ticket', $venta) }}" target="_blank"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Ticket
            </a>

            @if($venta->isCompletada())
                @can('create', App\Models\Devolucion::class)
                <a href="{{ route('devoluciones.create', $venta) }}"
                    class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                    Hacer Devolución
                </a>
                @endcan

                @can('anular', $venta)
                <button @click="modalAnularOpen = true" type="button"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Anular Venta
                </button>
                @endcan
            @endif
        </div>
    </div>

    <!-- Alerta de Venta Anulada -->
    @if($venta->isAnulada())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex items-start space-x-3">
        <svg class="h-5 w-5 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="text-sm font-bold text-rose-900">Esta venta fue anulada formalmente</h4>
            <p class="text-xs text-rose-700 mt-0.5">
                Anulada el {{ $venta->fecha_anulacion?->format('d/m/Y H:i') }} por {{ $venta->anulador?->name ?? 'Usuario' }}.
                <br>
                <strong>Motivo:</strong> {{ $venta->motivo_anulacion }}
            </p>
        </div>
    </div>
    @endif

    <!-- Datos Generales -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cliente</span>
            @if($venta->cliente)
                <div class="text-sm font-bold text-slate-900 mt-1">{{ $venta->cliente->razon_social }}</div>
                <div class="text-xs text-slate-500">{{ $venta->cliente->tipo_documento->value }}: {{ $venta->cliente->numero_documento }}</div>
                <div class="text-xs text-slate-400">{{ $venta->cliente->telefono ?? 'Sin teléfono' }}</div>
            @else
                <div class="text-sm font-bold text-slate-900 mt-1">Consumidor Final</div>
                <div class="text-xs text-slate-500">Sin registro de cliente</div>
            @endif
        </div>

        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Operación</span>
            <div class="text-xs text-slate-700 mt-1"><strong>Vendedor:</strong> {{ $venta->usuario->name }}</div>
            <div class="text-xs text-slate-700 mt-0.5"><strong>Comprobante:</strong> {{ $venta->tipo_comprobante->label() }}</div>
            <div class="text-xs text-slate-700 mt-0.5"><strong>Método:</strong> {{ $venta->metodo_pago }}</div>
        </div>

        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Condición Comercial</span>
            <div class="text-xs text-slate-700 mt-1"><strong>Tipo:</strong> {{ $venta->tipo_pago->label() }}</div>
            @if($venta->pago_con)
                <div class="text-xs text-slate-700 mt-0.5"><strong>Efectivo Recibido:</strong> ${{ number_format($venta->pago_con, 2) }}</div>
                <div class="text-xs text-emerald-700 font-bold mt-0.5"><strong>Cambio / Vuelto:</strong> ${{ number_format($venta->cambio, 2) }}</div>
            @endif
        </div>
    </div>

    <!-- Detalle de Ítems -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Productos Vendidos</h2>
            <span class="text-xs font-bold text-slate-500">{{ $venta->detalles->count() }} ítems</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left uppercase">Producto</th>
                        <th class="px-6 py-3.5 text-center uppercase">Cantidad</th>
                        <th class="px-6 py-3.5 text-right uppercase">Precio Unit.</th>
                        <th class="px-6 py-3.5 text-right uppercase">Descuento</th>
                        <th class="px-6 py-3.5 text-right uppercase">Impuesto</th>
                        <th class="px-6 py-3.5 text-right uppercase">Total Línea</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($venta->detalles as $det)
                        <tr>
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900">{{ $det->producto->nombre }}</div>
                                <div class="text-slate-400 text-[10px] font-mono">{{ $det->producto->codigo_barras ?? $det->producto->codigo }}</div>
                            </td>
                            <td class="px-6 py-3.5 text-center font-bold text-slate-800 whitespace-nowrap">
                                {{ number_format($det->cantidad, 2) }} {{ $det->producto->unidadMedida?->codigo ?? 'UND' }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-slate-700 whitespace-nowrap">
                                ${{ number_format($det->precio_unitario, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-slate-500 whitespace-nowrap">
                                ${{ number_format($det->descuento, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-slate-500 whitespace-nowrap">
                                ${{ number_format($det->impuesto_monto, 2) }} ({{ $det->impuesto_porcentaje }}%)
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900 whitespace-nowrap">
                                ${{ number_format($det->total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex justify-end">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800">${{ number_format($venta->subtotal, 2) }}</span>
                </div>
                @if($venta->descuento > 0)
                <div class="flex justify-between text-red-600 font-semibold">
                    <span>Descuento aplicado:</span>
                    <span>-${{ number_format($venta->descuento, 2) }}</span>
                </div>
                @endif
                @if($venta->impuesto > 0)
                <div class="flex justify-between text-slate-500">
                    <span>Impuestos:</span>
                    <span class="font-bold text-slate-800">+${{ number_format($venta->impuesto, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total General:</span>
                    <span class="text-indigo-600">${{ number_format($venta->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL Anular Venta -->
    @if($venta->isCompletada())
    <div x-cloak x-show="modalAnularOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalAnularOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <div x-show="modalAnularOpen" @click.away="modalAnularOpen = false"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold text-red-600 uppercase tracking-wider block">Anulación de Venta</span>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5">Venta {{ $venta->numero_venta }}</h3>
                    </div>
                    <button @click="modalAnularOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="bg-amber-50 p-3.5 rounded-xl border border-amber-200 text-xs text-amber-800">
                    Esta acción reingresará automáticamente las cantidades al inventario y revertirá los saldos correspondientes.
                </div>

                <form action="{{ route('ventas.anular', $venta) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo de Anulación *</label>
                        <textarea name="motivo" required rows="3" placeholder="Ej: Error en digitación de producto / Devolución inmediata del cliente..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-red-600 focus:ring-1 focus:ring-red-600"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button @click="modalAnularOpen = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-sm font-bold text-white shadow-sm transition">
                            Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
