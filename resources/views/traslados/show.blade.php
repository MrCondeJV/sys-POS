@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Remisión {{ $traslado->consecutivo }}</h1>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $traslado->estado->badgeClasses() }}">
                    {{ $traslado->estado->label() }}
                </span>
            </div>
            <p class="text-sm text-gray-500">Documento de traslado interno de inventario.</p>
        </div>
        <a href="{{ route('traslados.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Volver al listado</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-md text-sm">
            @foreach($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-b pb-4">
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Sucursal Origen</p>
                <p class="text-base font-semibold text-gray-900">{{ $traslado->sucursalOrigen->nombre }}</p>
                <p class="text-xs text-gray-600">Despachado por: {{ $traslado->usuarioDespacho->name ?? 'Sistema' }}</p>
                <p class="text-xs text-gray-600">Fecha envío: {{ $traslado->fecha_envio?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Sucursal Destino</p>
                <p class="text-base font-semibold text-gray-900">{{ $traslado->sucursalDestino->nombre }}</p>
                <p class="text-xs text-gray-600">Receptor: {{ $traslado->usuarioReceptor->name ?? 'Pendiente de entrega' }}</p>
                <p class="text-xs text-gray-600">Fecha recepción: {{ $traslado->fecha_recepcion?->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Motivo y Observaciones</p>
            <p class="text-sm text-gray-800 font-medium">{{ $traslado->motivo }}</p>
            @if($traslado->observaciones)
                <p class="text-xs text-gray-600 mt-1 bg-gray-50 p-2 rounded">{{ $traslado->observaciones }}</p>
            @endif
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-900 mb-3 uppercase">Detalle de Artículos Trasladados</h3>
            <table class="min-w-full divide-y divide-gray-200 text-sm border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Producto</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Lote</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Cant. Enviada</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Cant. Recibida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($traslado->detalles as $d)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $d->producto->nombre }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $d->lote?->numero_lote ?? 'N/A' }}</td>
                            <td class="px-4 py-2 text-right font-bold text-gray-900">{{ number_format($d->cantidad_enviada, 2) }}</td>
                            <td class="px-4 py-2 text-right text-emerald-600 font-bold">{{ number_format($d->cantidad_recibida, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($traslado->isEnTransito())
            <div class="border-t pt-6 flex flex-col sm:flex-row justify-end gap-4">
                <form method="POST" action="{{ route('traslados.rechazar', $traslado) }}" class="flex items-center gap-2" onsubmit="return confirm('¿Confirmas que deseas rechazar este traslado? Se reversará el inventario al origen.');">
                    @csrf
                    <input type="text" name="motivo_rechazo" required placeholder="Motivo de rechazo..." class="text-xs rounded-md border-gray-300 shadow-sm">
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-md shadow">
                        Rechazar Traslado
                    </button>
                </form>

                <form method="POST" action="{{ route('traslados.recibir', $traslado) }}" onsubmit="return confirm('¿Confirmas la recepción física del inventario en la sede destino?');">
                    @csrf
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-md shadow">
                        Confirmar y Recibir en Destino
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
