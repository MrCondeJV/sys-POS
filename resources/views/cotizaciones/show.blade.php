@extends('layouts.app')

@section('title', 'Cotización ' . $cotizacion->numero_cotizacion)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ modalAnular: false }">

    <x-page-header
        :title="$cotizacion->numero_cotizacion"
        subtitle="Emitida el {{ $cotizacion->fecha->format('d/m/Y') }} · {{ $cotizacion->usuario?->name ?? 'Sin asesor' }}"
        :breadcrumbs="[
            ['label' => 'Cotizaciones', 'url' => route('cotizaciones.index')],
            ['label' => $cotizacion->numero_cotizacion]
        ]">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <x-status-badge :status="$cotizacion->estado->value" size="md" />

                @if($cotizacion->isPendiente())
                    @can('create', App\Models\Venta::class)
                        <x-button variant="primary" size="sm" href="{{ route('pos.index', ['cotizacion' => $cotizacion->id]) }}">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Convertir en Venta
                        </x-button>
                    @endcan
                @endif

                <x-enviar-documento
                    :documento="$cotizacion"
                    :correo-url="route('cotizaciones.enviar-correo', $cotizacion)"
                    :pdf-url="route('cotizaciones.pdf', $cotizacion)"
                />

                <x-button variant="secondary" size="sm" href="{{ route('cotizaciones.pdf', ['cotizacion' => $cotizacion, 'ver' => 1]) }}" target="_blank">
                    Ver PDF
                </x-button>

                @if($cotizacion->isPendiente())
                    @can('anular', $cotizacion)
                        <x-button variant="danger" size="sm" type="button" @click="modalAnular = true">Anular</x-button>
                    @endcan
                @endif
            </div>
        </x-slot:actions>
    </x-page-header>

    <x-flash-messages />

    @if($errors->any())
        <div class="p-4 rounded-lg bg-danger-bg border border-danger-border text-danger-text text-sm">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    @if($cotizacion->venta)
        <div class="p-4 rounded-lg bg-success-bg border border-success-border text-success-text text-sm flex items-center justify-between gap-3">
            <span>Convertida en venta el {{ $cotizacion->fecha_conversion?->format('d/m/Y H:i') }}.</span>
            <a href="{{ route('ventas.show', $cotizacion->venta) }}" class="font-semibold font-mono hover:underline">{{ $cotizacion->venta->numero_venta }} →</a>
        </div>
    @elseif($cotizacion->estaVencida())
        <div class="p-4 rounded-lg bg-warning-bg border border-warning-border text-warning-text text-sm">
            La fecha de validez ({{ $cotizacion->fecha_validez->format('d/m/Y') }}) ya pasó. Aún puedes convertirla; la venta se cobra con los precios vigentes del catálogo.
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5 md:col-span-2">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Cliente</div>
            <div class="text-base font-bold text-slate-900">{{ $cotizacion->cliente?->razon_social ?? 'Consumidor Final' }}</div>
            @if($cotizacion->cliente)
                <div class="text-xs text-slate-500 font-mono">{{ $cotizacion->cliente->tipo_documento?->value }} {{ $cotizacion->cliente->numero_documento }}</div>
                <div class="text-xs text-slate-600 mt-1 space-x-3">
                    <span>Tel: {{ $cotizacion->cliente->telefono ?? 'Sin teléfono' }}</span>
                    <span>{{ $cotizacion->cliente->email ?? 'Sin correo' }}</span>
                </div>
            @endif
        </div>
        <div class="card p-5 space-y-1.5 text-xs">
            <div class="flex justify-between"><span class="text-slate-500">Fecha</span><span class="font-medium">{{ $cotizacion->fecha->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Válida hasta</span><span class="font-medium">{{ $cotizacion->fecha_validez?->format('d/m/Y') ?? 'Sin fecha límite' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Sede</span><span class="font-medium">{{ $cotizacion->sucursal?->nombre ?? '—' }}</span></div>
        </div>
    </div>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-left">Producto</th>
                <th class="px-4 py-3 text-center">Cantidad</th>
                <th class="px-4 py-3 text-right">Precio Unit.</th>
                <th class="px-4 py-3 text-right">Descuento</th>
                <th class="px-4 py-3 text-center">IVA</th>
                <th class="px-4 py-3 text-right">Total</th>
            </tr>
        </x-slot:head>
        @foreach($cotizacion->detalles as $d)
            <tr>
                <td class="px-4 py-3">
                    <div class="text-sm font-semibold text-slate-900">{{ $d->descripcion }}</div>
                    <div class="text-[11px] text-slate-500 font-mono">{{ $d->producto?->codigo ?? '—' }}</div>
                </td>
                <td class="px-4 py-3 text-center font-mono">{{ rtrim(rtrim(number_format($d->cantidad, 2, ',', '.'), '0'), ',') }}</td>
                <td class="px-4 py-3 text-right font-mono">${{ number_format($d->precio_unitario, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ (float) $d->descuento > 0 ? '$' . number_format($d->descuento, 0, ',', '.') : '—' }}</td>
                <td class="px-4 py-3 text-center font-mono">{{ number_format($d->impuesto_porcentaje, 0) }}%</td>
                <td class="px-4 py-3 text-right font-mono font-semibold">${{ number_format($d->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </x-table>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5 md:col-span-2 text-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Observaciones</div>
            <p class="text-slate-700">{{ $cotizacion->observaciones ?: 'Sin observaciones.' }}</p>
        </div>
        <div class="card p-5 space-y-1.5 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-mono">${{ number_format($cotizacion->subtotal, 0, ',', '.') }}</span></div>
            @if((float) $cotizacion->descuento > 0)
                <div class="flex justify-between"><span class="text-slate-500">Descuentos</span><span class="font-mono">-${{ number_format($cotizacion->descuento, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-slate-500">IVA</span><span class="font-mono">${{ number_format($cotizacion->impuesto, 0, ',', '.') }}</span></div>
            <div class="flex justify-between pt-2 border-t border-slate-200 text-base font-bold"><span>Total</span><span class="font-mono">${{ number_format($cotizacion->total, 0, ',', '.') }}</span></div>
        </div>
    </div>

    @can('anular', $cotizacion)
        <form id="formAnularCotizacion" action="{{ route('cotizaciones.anular', $cotizacion) }}" method="POST" class="hidden">@csrf</form>
        <x-modal-confirm
            show="modalAnular"
            title="Anular Cotización"
            message="La cotización quedará anulada y ya no podrá convertirse en venta. ¿Deseas continuar?"
            confirm-text="Sí, anular"
            cancel-text="Cancelar"
            confirm-variant="danger"
            @confirm="document.getElementById('formAnularCotizacion').submit()"
            @cancel="modalAnular = false"
        />
    @endcan
</div>
@endsection
