@extends('layouts.app')

@section('title', 'Venta: ' . $venta->numero_venta)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ modalAnularOpen: false }">

    <!-- Encabezado y Navegación -->
    <x-page-header
        :title="$venta->numero_venta"
        subtitle="Emitida el {{ $venta->fecha->format('d/m/Y \a \l\a\s H:i') }} &bull; Sucursal: {{ $venta->sucursal?->nombre ?? 'Sin sucursal' }}"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-status-badge :status="$venta->estado" size="md" />
                <x-badge :status="$venta->tipo_pago->value === 'CONTADO' ? 'success' : 'warning'" size="md">
                    {{ $venta->tipo_pago->label() }}
                </x-badge>

                <x-button variant="outline" size="sm" href="{{ route('ventas.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </x-button>

                <x-button variant="secondary" size="sm" href="{{ route('ventas.ticket', $venta) }}" target="_blank">
                    <svg class="h-4 w-4 mr-1.5 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Ticket
                </x-button>

                <x-button variant="secondary" size="sm" href="{{ route('ventas.pdf', ['venta' => $venta, 'ver' => 1]) }}" target="_blank">
                    PDF Carta
                </x-button>

                <x-enviar-documento
                    :documento="$venta"
                    :correo-url="route('ventas.enviar-correo', $venta)"
                    :pdf-url="route('ventas.pdf', $venta)"
                />

                @if($venta->isCompletada())
                    @can('create', App\Models\Devolucion::class)
                    <x-button variant="outline" size="sm" href="{{ route('devoluciones.create', $venta) }}">
                        <svg class="h-4 w-4 mr-1.5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                        </svg>
                        Devolución
                    </x-button>
                    @endcan

                    @can('anular', $venta)
                    <x-button variant="danger" size="sm" @click="modalAnularOpen = true">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Anular
                    </x-button>
                    @endcan
                @endif
            </div>
        </x-slot:actions>
    </x-page-header>


    <!-- Alerta de Venta Anulada -->
    @if($venta->isAnulada())
    <div class="bg-danger-bg border border-danger-border rounded-lg p-4 flex items-start space-x-3">
        <svg class="h-5 w-5 text-danger flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h4 class="text-xs font-bold text-danger-text uppercase tracking-wide">Esta venta fue anulada formalmente</h4>
            <p class="text-xs text-danger-text mt-0.5">
                Anulada el <span class="tabular-nums font-medium">{{ $venta->fecha_anulacion?->format('d/m/Y H:i') }}</span> por <span class="font-medium">{{ $venta->anulador?->name ?? 'Usuario' }}</span>.
                <br>
                <strong>Motivo:</strong> {{ $venta->motivo_anulacion }}
            </p>
        </div>
    </div>
    @endif

    <!-- Datos Generales -->
    <div class="bg-surface rounded-lg border border-border shadow-xs p-5 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">Cliente</span>
            @if($venta->cliente)
                <div class="text-sm font-bold text-text-primary mt-1">{{ $venta->cliente->razon_social }}</div>
                <div class="text-xs text-text-secondary font-mono">{{ $venta->cliente->tipo_documento->value }}: {{ $venta->cliente->numero_documento }}</div>
                <div class="text-xs text-text-muted">{{ $venta->cliente->telefono ?? 'Sin teléfono' }}</div>
            @else
                <div class="text-sm font-bold text-text-primary mt-1">Consumidor Final</div>
                <div class="text-xs text-text-secondary">Sin registro de cliente</div>
            @endif
        </div>

        <div>
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">Operación</span>
            <div class="text-xs text-text-secondary mt-1"><strong>Vendedor:</strong> {{ $venta->usuario->name }}</div>
            <div class="text-xs text-text-secondary mt-0.5"><strong>Comprobante:</strong> {{ $venta->tipo_comprobante->label() }}</div>
            <div class="text-xs text-text-secondary mt-0.5"><strong>Método:</strong> {{ $venta->metodo_pago }}</div>
        </div>

        <div>
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">Condición Comercial</span>
            <div class="text-xs text-text-secondary mt-1"><strong>Tipo:</strong> {{ $venta->tipo_pago->label() }}</div>
            @if($venta->pago_con)
                <div class="text-xs text-text-secondary mt-0.5 tabular-nums"><strong>Efectivo Recibido:</strong> ${{ number_format($venta->pago_con, 2) }}</div>
                <div class="text-xs text-success-text font-semibold mt-0.5 tabular-nums"><strong>Cambio / Vuelto:</strong> ${{ number_format($venta->cambio, 2) }}</div>
            @endif
        </div>
    </div>

    <!-- Detalle de Ítems -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-text-primary">Productos Vendidos</h2>
            <span class="text-xs font-semibold text-text-secondary">{{ $venta->detalles->count() }} ítems</span>
        </div>

        <x-table>
            <x-slot:thead>
                <tr>
                    <th class="px-5 py-3 text-left uppercase">Producto</th>
                    <th class="px-5 py-3 text-center uppercase">Cantidad</th>
                    <th class="px-5 py-3 text-right uppercase">Precio Unit.</th>
                    <th class="px-5 py-3 text-right uppercase">Descuento</th>
                    <th class="px-5 py-3 text-right uppercase">Impuesto</th>
                    <th class="px-5 py-3 text-right uppercase">Total Línea</th>
                </tr>
            </x-slot:thead>

            @foreach($venta->detalles as $det)
                <tr class="hover:bg-surface-muted/60 transition">
                    <td class="px-5 py-3">
                        <div class="font-semibold text-text-primary text-xs">{{ $det->producto->nombre }}</div>
                        <div class="text-text-muted text-[11px] font-mono">{{ $det->producto->codigo_barras ?? $det->producto->codigo }}</div>
                    </td>
                    <td class="px-5 py-3 text-center font-medium text-text-primary text-xs tabular-nums whitespace-nowrap">
                        {{ number_format($det->cantidad, 2) }} {{ $det->producto->unidadMedida?->codigo ?? 'UND' }}
                    </td>
                    <td class="px-5 py-3 text-right text-text-secondary text-xs tabular-nums whitespace-nowrap">
                        ${{ number_format($det->precio_unitario, 2) }}
                    </td>
                    <td class="px-5 py-3 text-right text-text-muted text-xs tabular-nums whitespace-nowrap">
                        ${{ number_format($det->descuento, 2) }}
                    </td>
                    <td class="px-5 py-3 text-right text-text-muted text-xs tabular-nums whitespace-nowrap">
                        ${{ number_format($det->impuesto_monto, 2) }} ({{ $det->impuesto_porcentaje }}%)
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-text-primary text-xs tabular-nums whitespace-nowrap">
                        ${{ number_format($det->total, 2) }}
                    </td>
                </tr>
            @endforeach
        </x-table>

        <!-- Totales -->
        <div class="p-5 bg-surface rounded-lg border border-border shadow-xs flex justify-end">
            <div class="w-72 space-y-2 text-xs">
                <div class="flex justify-between text-text-secondary">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-text-primary tabular-nums">${{ number_format($venta->subtotal, 2) }}</span>
                </div>
                @if($venta->descuento > 0)
                <div class="flex justify-between text-danger font-medium">
                    <span>Descuento aplicado (antes de IVA):</span>
                    <span class="tabular-nums">&minus;${{ number_format($venta->descuento, 2) }}</span>
                </div>
                @endif
                @if((float) $venta->descuento_global > 0)
                <div class="p-2.5 rounded-lg bg-warning-bg border border-warning-border text-xs space-y-0.5">
                    <div class="flex justify-between font-semibold text-warning-text">
                        <span>Descuento sobre el total:</span>
                        <span class="tabular-nums">&minus;${{ number_format($venta->descuento_global, 2) }}</span>
                    </div>
                    <div class="text-text-secondary"><span class="font-semibold">Motivo:</span> {{ $venta->motivo_descuento }}</div>
                </div>
                @endif
                @if($venta->impuesto > 0)
                <div class="flex justify-between text-text-secondary">
                    <span>Impuestos:</span>
                    <span class="font-semibold text-text-primary tabular-nums">+${{ number_format($venta->impuesto, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-bold text-text-primary pt-2 border-t border-border">
                    <span>Total General:</span>
                    <span class="text-brand-700 tabular-nums">${{ number_format($venta->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL Anular Venta -->
    @if($venta->isCompletada())
    <div x-cloak x-show="modalAnularOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalAnularOpen" @click="modalAnularOpen = false" class="fixed inset-0 bg-brand-900/50 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalAnularOpen" class="relative bg-surface rounded-lg max-w-md w-full p-6 shadow-lg border border-border z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <span class="text-xs font-semibold text-danger uppercase tracking-wider block">Anulación de Venta</span>
                        <h3 class="text-base font-bold text-text-primary mt-0.5">Venta {{ $venta->numero_venta }}</h3>
                    </div>
                    <button @click="modalAnularOpen = false" class="text-text-muted hover:text-text-primary transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="bg-warning-bg p-3 rounded-md border border-warning-border text-xs text-warning-text">
                    Esta acción reingresará automáticamente las existencias al inventario y revertirá los saldos correspondientes.
                </div>

                <form action="{{ route('ventas.anular', $venta) }}" method="POST" class="space-y-4">
                    @csrf
                    <x-field label="Motivo de Anulación" name="motivo" :required="true">
                        <textarea name="motivo" required rows="3" placeholder="Ej: Error en digitación de producto / Devolución inmediata del cliente..."
                            class="input h-auto py-2"></textarea>
                    </x-field>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-border">
                        <x-button variant="outline" size="sm" type="button" @click="modalAnularOpen = false">
                            Cancelar
                        </x-button>
                        <x-button variant="danger" size="sm" type="submit">
                            Confirmar Anulación
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
