@extends('layouts.app')

@section('title', 'Procesar Devolución - Venta #' . $venta->numero_venta)

@section('content')
<div class="space-y-6">

    <x-page-header
        title="Devolución sobre Venta #{{ $venta->numero_venta }}"
        subtitle="Cliente: {{ $venta->cliente?->razon_social ?? 'Consumidor Final' }} • Fecha: {{ $venta->fecha->format('d/m/Y H:i') }} • Total Facturado: ${{ number_format($venta->total, 2) }}"
        :breadcrumbs="[
            ['label' => 'Ventas', 'url' => route('ventas.index')],
            ['label' => '#' . $venta->numero_venta, 'url' => route('ventas.show', $venta)],
            ['label' => 'Procesar Devolución']
        ]">
        <x-slot:actions>
            <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a la Venta
            </a>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('devoluciones.store', $venta) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Tabla de Items Vendidos -->
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-sm font-semibold text-slate-900">
                    Selecciona los artículos y cantidades a devolver
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ingresa la cantidad que regresa el cliente y confirma si debe reintegrarse físicamente al inventario.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table-corporate">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant. Vendida</th>
                            <th class="text-center">Ya Devuelto</th>
                            <th class="text-center">Disponible</th>
                            <th class="text-right">Precio Unitario</th>
                            <th class="w-36 text-center">Cant. a Devolver *</th>
                            <th class="text-center w-28">Reingresar Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($venta->detalles as $idx => $det)
                        @php
                            $disponible = $det->cantidadPendienteDevolucion();
                        @endphp
                        <tr class="{{ $disponible <= 0 ? 'bg-slate-50/70 opacity-60' : '' }}">
                            <td>
                                <div class="font-semibold text-slate-900 text-xs">{{ $det->producto->nombre }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $det->producto->codigo ?? $det->producto->sku ?? '' }}</div>
                                <input type="hidden" name="items[{{ $idx }}][venta_detalle_id]" value="{{ $det->id }}">
                            </td>
                            <td class="text-center font-mono font-medium text-xs text-slate-700 tabular-nums">
                                {{ number_format($det->cantidad, 2) }}
                            </td>
                            <td class="text-center font-mono font-medium text-xs text-amber-700 tabular-nums">
                                {{ number_format($det->cantidad_devuelta, 2) }}
                            </td>
                            <td class="text-center font-mono font-semibold text-xs text-emerald-700 tabular-nums">
                                {{ number_format($disponible, 2) }}
                            </td>
                            <td class="text-right font-mono text-xs text-slate-700 tabular-nums">
                                ${{ number_format($det->precio_unitario, 2) }}
                            </td>
                            <td class="text-center">
                                <input type="number" step="0.01" min="0" max="{{ $disponible }}"
                                       name="items[{{ $idx }}][cantidad]"
                                       value="{{ old("items.$idx.cantidad", 0) }}"
                                       {{ $disponible <= 0 ? 'disabled' : '' }}
                                       class="input-base text-center font-mono text-xs py-1.5 tabular-nums">
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="items[{{ $idx }}][reingresa_inventario]" value="1" checked
                                       {{ $disponible <= 0 ? 'disabled' : '' }}
                                       class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 h-4 w-4">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Opciones de Reintegro y Motivo -->
        <div class="card p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="label-base">Método de Reintegro <span class="text-red-500">*</span></label>
                    <select name="tipo_reintegro" class="input-select text-xs">
                        @foreach(App\Enums\TipoReintegroDevolucion::cases() as $tr)
                            <option value="{{ $tr->value }}" {{ old('tipo_reintegro') === $tr->value ? 'selected' : '' }}>
                                {{ $tr->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($sesionCaja)
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700">
                    <span class="font-semibold text-slate-900">Caja Abierta:</span> {{ $sesionCaja->caja->nombre }}
                    <input type="hidden" name="caja_sesion_id" value="{{ $sesionCaja->id }}">
                    <div class="text-[11px] text-slate-500 mt-0.5">Si se reintegra en efectivo, se creará el movimiento de egreso en esta caja.</div>
                </div>
                @else
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    No hay turno de caja abierto en tu sucursal. Si seleccionas reintegro en efectivo, no se afectará el arqueo de caja físico.
                </div>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <label class="label-base">Motivo de la Devolución <span class="text-red-500">*</span></label>
                    <textarea name="motivo" rows="3" required placeholder="Ej: Garantía por defecto de fábrica / Cambio de artículo..."
                              class="input-base text-xs">{{ old('motivo') }}</textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <a href="{{ route('ventas.show', $venta) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        Confirmar y Procesar Devolución
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
