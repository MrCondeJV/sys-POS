@extends('layouts.app')

@section('title', 'Procesar Devolución - Venta #' . $venta->numero_venta)

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Gestión de Devolución</span>
            <h1 class="text-xl font-black text-slate-900">Devolución sobre Venta #{{ $venta->numero_venta }}</h1>
            <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-3">
                <span>Cliente: <strong>{{ $venta->cliente?->razon_social ?? 'Consumidor Final' }}</strong></span>
                <span>&bull;</span>
                <span>Fecha Venta: <strong>{{ $venta->fecha->format('d/m/Y H:i') }}</strong></span>
                <span>&bull;</span>
                <span>Total Facturado: <strong>${{ number_format($venta->total, 2) }}</strong></span>
            </div>
        </div>

        <a href="{{ route('ventas.show', $venta) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
            &larr; Volver a la venta
        </a>
    </div>

    <form action="{{ route('devoluciones.store', $venta) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Tabla de Items Vendidos -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-6 space-y-4">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                Selecciona los items y cantidades a devolver
            </h3>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Producto</th>
                            <th class="py-3 px-4 text-center">Cant. Vendida</th>
                            <th class="py-3 px-4 text-center">Ya Devuelto</th>
                            <th class="py-3 px-4 text-center">Disponible p/ Devolver</th>
                            <th class="py-3 px-4 text-right">Precio Unitario</th>
                            <th class="py-3 px-4 w-32">Cant. a Devolver *</th>
                            <th class="py-3 px-4 text-center">Reingresar a Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($venta->detalles as $idx => $det)
                        @php
                            $disponible = $det->cantidadPendienteDevolucion();
                        @endphp
                        <tr class="{{ $disponible <= 0 ? 'bg-slate-50/50 opacity-60' : 'hover:bg-slate-50/70' }} transition">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $det->producto->nombre }}
                                <input type="hidden" name="items[{{ $idx }}][venta_detalle_id]" value="{{ $det->id }}">
                            </td>
                            <td class="py-3 px-4 text-center font-semibold text-slate-700">
                                {{ number_format($det->cantidad, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center text-amber-600 font-semibold">
                                {{ number_format($det->cantidad_devuelta, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center font-black text-emerald-700">
                                {{ number_format($disponible, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                ${{ number_format($det->precio_unitario, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <input type="number" step="0.01" min="0" max="{{ $disponible }}"
                                       name="items[{{ $idx }}][cantidad]"
                                       value="{{ old("items.$idx.cantidad", 0) }}"
                                       {{ $disponible <= 0 ? 'disabled' : '' }}
                                       class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-center focus:border-amber-500">
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="items[{{ $idx }}][reingresa_inventario]" value="1" checked
                                       {{ $disponible <= 0 ? 'disabled' : '' }}
                                       class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Opciones de Reintegro y Motivo -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Método de Reintegro *</label>
                    <select name="tipo_reintegro" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                        @foreach(App\Enums\TipoReintegroDevolucion::cases() as $tr)
                            <option value="{{ $tr->value }}" {{ old('tipo_reintegro') === $tr->value ? 'selected' : '' }}>
                                {{ $tr->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($sesionCaja)
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800">
                    <span class="font-bold">Caja Abierta:</span> {{ $sesionCaja->caja->nombre }}
                    <input type="hidden" name="caja_sesion_id" value="{{ $sesionCaja->id }}">
                    <div class="text-[11px] text-emerald-600 mt-0.5">Si se reintegra en efectivo, se creará el movimiento de egreso en esta caja.</div>
                </div>
                @else
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                    No hay turno de caja abierto en tu sucursal. Si seleccionas reintegro en efectivo, no se afectará el arqueo de caja físico.
                </div>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Motivo de la Devolución *</label>
                    <textarea name="motivo" rows="3" required placeholder="Ej: Producto en garantía por defecto de fábrica / Cliente cambió de opinión..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-amber-500">{{ old('motivo') }}</textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <a href="{{ route('ventas.show', $venta) }}" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl">Cancelar</a>
                    <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Confirmar y Procesar Devolución
                    </button>
                </div>
            </div>

        </div>

    </form>

</div>
@endsection
