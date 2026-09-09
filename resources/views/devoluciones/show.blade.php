@extends('layouts.app')

@section('title', 'Comprobante de Devolución ' . $devolucion->numero_devolucion)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block">Devolución Exitosa</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                    {{ $devolucion->estado->label() }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-0.5">{{ $devolucion->numero_devolucion }}</h1>
            <div class="text-xs text-slate-500 mt-1">
                Vinculada a Venta <a href="{{ route('ventas.show', $devolucion->venta) }}" class="text-indigo-600 font-bold hover:underline">#{{ $devolucion->venta->numero_venta }}</a>
                &bull; {{ $devolucion->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('devoluciones.comprobante', $devolucion) }}" target="_blank"
               class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                <span>Imprimir Comprobante</span>
            </a>
            <a href="{{ route('devoluciones.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                &larr; Volver al Listado
            </a>
        </div>
    </div>

    <!-- Resumen -->
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-slate-400 font-bold uppercase block text-[10px]">Tipo Devolución</span>
            <span class="font-bold text-slate-800 text-sm">{{ $devolucion->tipo_devolucion->label() }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase block text-[10px]">Método Reintegro</span>
            <span class="font-bold text-slate-800 text-sm">{{ $devolucion->tipo_reintegro->label() }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase block text-[10px]">Cliente</span>
            <span class="font-bold text-slate-800 text-sm truncate block">{{ $devolucion->venta->cliente?->razon_social ?? 'Consumidor Final' }}</span>
        </div>
        <div>
            <span class="text-slate-400 font-bold uppercase block text-[10px]">Operador</span>
            <span class="font-bold text-slate-800 text-sm truncate block">{{ $devolucion->usuario->name }}</span>
        </div>
    </div>

    @if($devolucion->motivo)
    <div class="bg-amber-50/60 border border-amber-200 p-4 rounded-2xl text-xs text-amber-900">
        <strong>Motivo indicado:</strong> {{ $devolucion->motivo }}
    </div>
    @endif

    <!-- Items Devueltos -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-6 space-y-4">
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Productos Devueltos</h3>
        <div class="overflow-x-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Producto</th>
                        <th class="py-3 px-4 text-center">Cantidad</th>
                        <th class="py-3 px-4 text-right">Precio Unit.</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-center">Kardex</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($devolucion->detalles as $det)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $det->producto->nombre }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">{{ number_format($det->cantidad, 2) }}</td>
                        <td class="py-3 px-4 text-right">${{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="py-3 px-4 text-right">${{ number_format($det->subtotal, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($det->reingresa_inventario)
                                <span class="text-emerald-600 font-bold text-[11px]">Reingresado</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Sin reingreso</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">${{ number_format($det->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end pt-2">
            <div class="w-64 space-y-1.5 text-xs text-right">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800">${{ number_format($devolucion->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>IVA / Impuestos:</span>
                    <span class="font-bold text-slate-800">${{ number_format($devolucion->impuesto, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Devuelto:</span>
                    <span class="text-amber-600">${{ number_format($devolucion->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
