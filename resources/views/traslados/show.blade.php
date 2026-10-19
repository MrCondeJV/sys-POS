@extends('layouts.app')

@section('title', 'Remisión ' . $traslado->consecutivo)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <x-page-header
        :title="'Remisión ' . $traslado->consecutivo"
        subtitle="Documento de traslado interno de existencias entre sucursales."
        :breadcrumbs="[
            ['label' => 'Traslados', 'url' => route('traslados.index')],
            ['label' => 'Detalle de Remisión']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-status-badge :status="$traslado->estado" size="md" />
                <x-button variant="outline" size="sm" href="{{ route('traslados.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white shadow-xs rounded-xl border border-slate-200/80 p-6 space-y-6">
        <!-- Cabecera de Sedes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 border-b border-slate-100 pb-5">
            <div class="space-y-1">
                <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Sucursal Origen</p>
                <p class="text-sm font-bold text-slate-900">{{ $traslado->sucursalOrigen?->nombre ?? 'Sin sucursal' }}</p>
                <p class="text-xs text-slate-500">Despachado por: <span class="font-medium text-slate-700">{{ $traslado->usuarioDespacho->name ?? 'Sistema' }}</span></p>
                <p class="text-xs text-slate-500 tabular-nums">Fecha envío: {{ $traslado->fecha_envio?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            <div class="space-y-1">
                <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Sucursal Destino</p>
                <p class="text-sm font-bold text-slate-900">{{ $traslado->sucursalDestino?->nombre ?? 'Sin sucursal' }}</p>
                <p class="text-xs text-slate-500">Receptor: <span class="font-medium text-slate-700">{{ $traslado->usuarioReceptor->name ?? 'Pendiente de entrega' }}</span></p>
                <p class="text-xs text-slate-500 tabular-nums">Fecha recepción: {{ $traslado->fecha_recepcion?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>

        <!-- Motivo -->
        <div class="space-y-1">
            <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Motivo y Observaciones</p>
            <p class="text-xs text-slate-800 font-semibold">{{ $traslado->motivo }}</p>
            @if($traslado->observaciones)
                <p class="text-xs text-slate-600 mt-1 bg-slate-50 border border-slate-200/60 p-3 rounded-lg">{{ $traslado->observaciones }}</p>
            @endif
        </div>

        <!-- Tabla de Artículos -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">Artículos Trasladados</h3>
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th class="px-5 py-3 text-left uppercase">Producto</th>
                        <th class="px-4 py-3 text-left uppercase">Lote</th>
                        <th class="px-4 py-3 text-right uppercase">Cant. Enviada</th>
                        <th class="px-5 py-3 text-right uppercase">Cant. Recibida</th>
                    </tr>
                </x-slot:thead>

                @foreach($traslado->detalles as $d)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-3 text-xs font-semibold text-slate-900">{{ $d->producto->nombre }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 font-mono">{{ $d->lote?->numero_lote ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-xs font-bold text-slate-900 tabular-nums">{{ number_format($d->cantidad_enviada, 2) }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-emerald-600 tabular-nums">{{ number_format($d->cantidad_recibida, 2) }}</td>
                    </tr>
                @endforeach
            </x-table>
        </div>

        <!-- Acciones si está en tránsito -->
        @if($traslado->isEnTransito())
            <div class="border-t border-slate-100 pt-5 flex flex-col sm:flex-row justify-end gap-3" x-data="{ motivoRechazo: '' }">
                <form method="POST" action="{{ route('traslados.rechazar', $traslado) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="motivo_rechazo" required
                        placeholder="Motivo de rechazo..."
                        class="text-xs py-2 px-3 border border-slate-300 rounded-lg w-48 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <x-button variant="danger" size="sm" type="submit">
                        Rechazar
                    </x-button>
                </form>

                <form method="POST" action="{{ route('traslados.recibir', $traslado) }}">
                    @csrf
                    <x-button variant="primary" size="sm" type="submit">
                        Confirmar y Recibir en Destino
                    </x-button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
