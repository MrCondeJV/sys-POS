@extends('layouts.app')

@section('title', 'Comprobante de Devolución ' . $devolucion->numero_devolucion)

@section('content')
<div class="space-y-6">

    <x-page-header
        title="Comprobante {{ $devolucion->numero_devolucion }}"
        subtitle="Vinculada a Venta #{{ $devolucion->venta->numero_venta }} • {{ $devolucion->created_at->format('d/m/Y H:i') }}"
        :breadcrumbs="[
            ['label' => 'Devoluciones', 'url' => route('devoluciones.index')],
            ['label' => $devolucion->numero_devolucion]
        ]">
        <x-slot:actions>
            <span class="badge badge-success">
                {{ $devolucion->estado->label() }}
            </span>

            <a href="{{ route('devoluciones.comprobante', $devolucion) }}" target="_blank"
               class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Comprobante
            </a>

            <a href="{{ route('devoluciones.index') }}" class="btn-secondary">
                Volver al Listado
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Resumen -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <span class="kpi-label">Tipo Devolución</span>
            <span class="font-semibold text-slate-800 text-sm mt-1 block">{{ $devolucion->tipo_devolucion->label() }}</span>
        </div>
        <div class="card p-4">
            <span class="kpi-label">Método Reintegro</span>
            <span class="font-semibold text-slate-800 text-sm mt-1 block">{{ $devolucion->tipo_reintegro->label() }}</span>
        </div>
        <div class="card p-4">
            <span class="kpi-label">Cliente</span>
            <span class="font-semibold text-slate-800 text-sm mt-1 truncate block">{{ $devolucion->venta->cliente?->razon_social ?? 'Consumidor Final' }}</span>
        </div>
        <div class="card p-4">
            <span class="kpi-label">Operador</span>
            <span class="font-semibold text-slate-800 text-sm mt-1 truncate block">{{ $devolucion->usuario->name }}</span>
        </div>
    </div>

    @if($devolucion->motivo)
    <div class="card p-4 bg-slate-50 border-slate-200 text-xs text-slate-700">
        <span class="font-semibold text-slate-900">Motivo de devolución:</span> {{ $devolucion->motivo }}
    </div>
    @endif

    <!-- Items Devueltos -->
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Artículos Devueltos</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-center">Kardex</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devolucion->detalles as $det)
                    <tr>
                        <td class="font-semibold text-slate-900 text-xs">{{ $det->producto->nombre }}</td>
                        <td class="text-center font-mono font-medium text-xs text-slate-800 tabular-nums">{{ number_format($det->cantidad, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($det->subtotal, 2) }}</td>
                        <td class="text-center">
                            @if($det->reingresa_inventario)
                                <span class="badge badge-success">Reingresado</span>
                            @else
                                <span class="badge badge-neutral">Sin reingreso</span>
                            @endif
                        </td>
                        <td class="text-right font-mono font-semibold text-slate-900 text-xs tabular-nums">${{ number_format($det->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-5 bg-slate-50/70 border-t border-slate-200">
            <div class="max-w-xs ml-auto space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-mono font-semibold text-slate-800 tabular-nums">${{ number_format($devolucion->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>IVA / Impuestos:</span>
                    <span class="font-mono font-semibold text-slate-800 tabular-nums">${{ number_format($devolucion->impuesto, 2) }}</span>
                </div>
                <div class="border-t border-slate-200 pt-2 flex justify-between items-baseline">
                    <span class="text-sm font-semibold text-slate-900">Total Devuelto:</span>
                    <span class="text-xl font-bold text-slate-900 font-mono tabular-nums">${{ number_format($devolucion->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
