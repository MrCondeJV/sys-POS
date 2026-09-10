@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">Remisión {{ $traslado->consecutivo }}</h1>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $traslado->estado->badgeClasses() }}">
                    {{ $traslado->estado->label() }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Documento de traslado interno de inventario.</p>
        </div>
        <a href="{{ route('traslados.index') }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Volver al listado</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-6 space-y-6">
        {{-- Cabecera de sedes --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b border-slate-100 pb-5">
            <div class="space-y-1">
                <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Sucursal Origen</p>
                <p class="text-base font-bold text-slate-900">{{ $traslado->sucursalOrigen->nombre }}</p>
                <p class="text-xs text-slate-500">Despachado por: {{ $traslado->usuarioDespacho->name ?? 'Sistema' }}</p>
                <p class="text-xs text-slate-500">Fecha envío: {{ $traslado->fecha_envio?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Sucursal Destino</p>
                <p class="text-base font-bold text-slate-900">{{ $traslado->sucursalDestino->nombre }}</p>
                <p class="text-xs text-slate-500">Receptor: {{ $traslado->usuarioReceptor->name ?? 'Pendiente de entrega' }}</p>
                <p class="text-xs text-slate-500">Fecha recepción: {{ $traslado->fecha_recepcion?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>

        {{-- Motivo --}}
        <div class="space-y-1">
            <p class="text-xs text-slate-500 uppercase font-semibold tracking-wider">Motivo y Observaciones</p>
            <p class="text-sm text-slate-800 font-semibold">{{ $traslado->motivo }}</p>
            @if($traslado->observaciones)
                <p class="text-xs text-slate-600 mt-1 bg-slate-50 border border-slate-100 p-3 rounded-xl">{{ $traslado->observaciones }}</p>
            @endif
        </div>

        {{-- Tabla de artículos --}}
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-3 uppercase tracking-wide">Detalle de Artículos Trasladados</h3>
            <div class="overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lote</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cant. Enviada</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Cant. Recibida</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @foreach($traslado->detalles as $d)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $d->producto->nombre }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $d->lote?->numero_lote ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-900">{{ number_format($d->cantidad_enviada, 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($d->cantidad_recibida, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Acciones (si está en tránsito) --}}
        @if($traslado->isEnTransito())
            <div class="border-t border-slate-100 pt-5 flex flex-col sm:flex-row justify-end gap-3">
                <form method="POST" action="{{ route('traslados.rechazar', $traslado) }}" class="flex items-center gap-2"
                    onsubmit="return confirm('¿Confirmas que deseas rechazar este traslado? Se reversará el inventario al origen.');">
                    @csrf
                    <input type="text" name="motivo_rechazo" required
                        placeholder="Motivo de rechazo..."
                        class="text-xs w-48">
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Rechazar Traslado
                    </button>
                </form>

                <form method="POST" action="{{ route('traslados.recibir', $traslado) }}"
                    onsubmit="return confirm('¿Confirmas la recepción física del inventario en la sede destino?');">
                    @csrf
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                        Confirmar y Recibir en Destino
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
