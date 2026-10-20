@extends('layouts.app')

@section('title', 'Factura de Compra #' . $compra->numero_factura)

@section('content')
<style>
    @media print {
        @page {
            size: letter portrait;
            margin: 8mm 10mm;
        }
        body {
            background-color: var(--color-surface) !important;
            background: var(--color-surface) !important;
            color: var(--color-text-primary) !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .screen-view {
            display: none !important;
        }
        .print-voucher-view {
            display: block !important;
        }
    }
</style>

<!-- ========================================== -->
<!-- 1. VISTA INTERACTIVA PARA PANTALLA (WEB)    -->
<!-- ========================================== -->
<div class="screen-view print:hidden space-y-6" x-data="{
    anularModalOpen: false,
    motivo: ''
}">
    <!-- Header & Acciones Rápidas -->
    <x-page-header
        title="Factura #{{ $compra->numero_factura }}"
        subtitle="Emitida el {{ $compra->fecha_emision->format('d/m/Y') }} • Registrada por {{ $compra->user?->name ?? 'Usuario del Sistema' }}"
        :breadcrumbs="[
            ['label' => 'Historial de Compras', 'url' => route('compras.index')],
            ['label' => '#' . $compra->numero_factura]
        ]">
        <x-slot:actions>
            @if($compra->isRegistrada())
                <span class="badge badge-success">Registrada</span>
            @else
                <span class="badge badge-danger">Anulada</span>
            @endif

            <button type="button" onclick="window.print()" class="btn-secondary">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Comprobante
            </button>

            @can('anular', $compra)
                @if($compra->isRegistrada())
                    <button type="button" @click="anularModalOpen = true"
                        class="btn-danger">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Anular Compra
                    </button>
                @endif
            @endcan
        </x-slot:actions>
    </x-page-header>

    <!-- Bloque de Metadatos de la Factura -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Tarjeta Proveedor -->
        <div class="card p-5 space-y-2">
            <div class="kpi-label">Proveedor / Emisor</div>
            <div class="text-sm font-semibold text-slate-900">{{ $compra->proveedor?->razon_social ?? 'Proveedor N/A' }}</div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400 font-mono">{{ $compra->proveedor?->tipo_documento?->value }}:</span> <span class="font-mono">{{ $compra->proveedor?->numero_documento }}</span></div>
                @if($compra->proveedor?->nombre_contacto)
                    <div><span class="text-slate-400">Contacto:</span> {{ $compra->proveedor->nombre_contacto }}</div>
                @endif
                @if($compra->proveedor?->telefono)
                    <div><span class="text-slate-400">Tel:</span> <span class="font-mono tabular-nums">{{ $compra->proveedor->telefono }}</span></div>
                @endif
                @if($compra->proveedor?->email)
                    <div><span class="text-slate-400">Email:</span> {{ $compra->proveedor->email }}</div>
                @endif
            </div>
        </div>

        <!-- Tarjeta Sede Receptora -->
        <div class="card p-5 space-y-2">
            <div class="kpi-label">Sede Receptora de Mercancía</div>
            <div class="text-sm font-semibold text-slate-900">{{ $compra->sucursal?->nombre ?? 'Sede N/A' }}</div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400">Código:</span> <span class="font-mono">{{ $compra->sucursal?->codigo ?? 'S/C' }}</span></div>
                <div><span class="text-slate-400">Dirección:</span> {{ $compra->sucursal?->direccion ?? 'Sin dirección' }}</div>
                <div><span class="text-slate-400">Ciudad:</span> {{ $compra->sucursal?->ciudad ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Tarjeta Condiciones de Pago -->
        <div class="card p-5 space-y-2">
            <div class="kpi-label">Condiciones Comerciales</div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-600">Forma de Pago:</span>
                <span class="badge {{ $compra->tipo_pago?->value === 'CONTADO' ? 'badge-neutral' : 'badge-warning' }}">
                    {{ $compra->tipo_pago?->value ?? 'CONTADO' }}
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400">Fecha de Registro:</span> <span class="font-mono tabular-nums">{{ $compra->created_at->format('d/m/Y H:i') }}</span></div>
                @if($compra->observaciones)
                    <div class="pt-1 text-slate-600 italic bg-slate-50 p-2 rounded border border-slate-200">
                        "{{ $compra->observaciones }}"
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla Detalle de Artículos Adquiridos -->
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <h2 class="text-sm font-semibold text-slate-900">Artículos y Líneas de Mercancía Recibidas</h2>
            <span class="text-xs font-medium text-slate-500 tabular-nums">{{ $compra->detalles->count() }} ítems</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>SKU</th>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Costo Unitario</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">% IVA</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->detalles as $idx => $d)
                    <tr>
                        <td class="text-xs text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="font-mono text-xs font-semibold text-slate-700">
                            {{ $d->producto?->codigo ?? $d->producto?->sku ?? '—' }}
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900 text-sm">{{ $d->producto?->nombre ?? 'Producto Eliminado' }}</div>
                            <div class="text-xs text-slate-400 font-mono">
                                Unidad: {{ $d->producto?->unidadMedida?->nombre ?? 'Unidad' }} ({{ $d->producto?->unidadMedida?->codigo ?? 'UND' }})
                            </div>
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">
                            {{ number_format($d->cantidad, 2) }}
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">
                            ${{ number_format($d->costo_unitario, 2) }}
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">
                            ${{ number_format($d->subtotal, 2) }}
                        </td>
                        <td class="text-right font-mono text-xs text-slate-500 tabular-nums">
                            {{ number_format($d->porcentaje_iva, 0) }}% (${{ number_format($d->valor_iva, 2) }})
                        </td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">
                            ${{ number_format($d->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales Liquidación -->
        <div class="bg-slate-50/70 p-5 border-t border-slate-200">
            <div class="max-w-xs ml-auto space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Neto:</span>
                    <span class="font-mono font-semibold text-slate-900 tabular-nums">${{ number_format($compra->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Impuestos (IVA):</span>
                    <span class="font-mono font-semibold text-slate-900 tabular-nums">${{ number_format($compra->impuestos, 2) }}</span>
                </div>
                @if($compra->descuento > 0)
                <div class="flex justify-between text-warning-text">
                    <span>Descuento Comercial:</span>
                    <span class="font-mono font-semibold tabular-nums">-${{ number_format($compra->descuento, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-slate-200 pt-2 flex justify-between items-baseline">
                    <span class="text-sm font-semibold text-slate-900">Total Liquidado:</span>
                    <span class="text-xl font-bold text-slate-900 font-mono tabular-nums">${{ number_format($compra->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Anulación de Compra -->
    <div x-cloak x-show="anularModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="anularModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="anularModalOpen = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div x-show="anularModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all border border-slate-200">

                <form action="{{ route('compras.anular', $compra) }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-4">
                        <div class="w-10 h-10 rounded-lg bg-danger-bg text-danger-text flex items-center justify-center mx-auto border border-danger-border">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <div class="text-center">
                            <h3 class="text-base font-semibold text-slate-900">¿Anular Factura de Compra?</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Esta acción revertirá las existencias ingresadas en la sede <strong class="text-slate-700">{{ $compra->sucursal?->nombre }}</strong> registrando una devolución al proveedor en el Kardex.
                            </p>
                        </div>

                        <div>
                            <label class="label-base">
                                Motivo de la Anulación <span class="text-danger-text">*</span>
                            </label>
                            <textarea name="motivo" x-model="motivo" rows="3" required
                                placeholder="Indica la razón: error de digitación, devolución de mercancía por garantía, etc."
                                class="input"></textarea>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 flex items-center justify-end space-x-3">
                        <button type="button" @click="anularModalOpen = false" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="!motivo.trim()"
                            class="btn-danger disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. VISTA EXCLUSIVA PARA IMPRESIÓN OFICIAL  -->
<!-- ========================================== -->
<div class="print-voucher-view hidden print:block w-full max-w-[800px] mx-auto bg-white text-slate-900 font-sans text-xs p-1">
    <!-- Encabezado Principal -->
    <div class="flex items-start justify-between border-b-2 border-slate-800 pb-3">
        <!-- Datos de la Empresa Emisora -->
        <div class="space-y-1 max-w-[480px]">
            <div class="flex items-center space-x-2">
                <div class="h-8 w-8 bg-slate-900 text-white font-bold text-sm rounded flex items-center justify-center">
                    POS
                </div>
                <h1 class="text-lg font-bold text-slate-900 uppercase tracking-tight leading-none">
                    {{ $compra->empresa?->razon_social ?? $compra->empresa?->nombre_comercial ?? auth()->user()->empresa?->nombre_comercial ?? 'POS COMERCIAL' }}
                </h1>
            </div>
            <p class="text-[11px] font-semibold text-slate-700">
                NIT: {{ $compra->empresa?->nit ?? auth()->user()->empresa?->nit ?? '900123456' }}{{ ($compra->empresa?->dv || auth()->user()->empresa?->dv) ? '-' . ($compra->empresa?->dv ?? auth()->user()->empresa?->dv) : '' }}
                &bull; Régimen Común / Responsable de IVA
            </p>
            <p class="text-[10px] text-slate-600">
                <span class="font-semibold">Sede Receptora:</span> {{ $compra->sucursal?->nombre }} (Cód: {{ $compra->sucursal?->codigo ?? 'S/C' }})
            </p>
            <p class="text-[10px] text-slate-600">
                {{ $compra->sucursal?->direccion ?? 'Dirección Comercial Principal' }} {{ $compra->sucursal?->ciudad ? '• ' . $compra->sucursal->ciudad : '' }}
            </p>
            <p class="text-[10px] text-slate-600">
                Tel: {{ $compra->sucursal?->telefono ?? $compra->empresa?->telefono ?? 'N/A' }} &bull; Email: {{ $compra->empresa?->email ?? 'administracion@poscomercial.com' }}
            </p>
        </div>

        <!-- Cajetín Oficial del Documento -->
        <div class="w-64 border-2 border-slate-800 rounded overflow-hidden text-center flex-shrink-0">
            <div class="bg-slate-800 text-white py-1 px-2">
                <span class="font-bold text-[11px] uppercase tracking-wider block">COMPROBANTE DE COMPRA</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-300 block">Entrada a Inventario</span>
            </div>
            <div class="p-2 bg-slate-50 space-y-1 text-left text-[10px]">
                <div class="flex justify-between items-baseline">
                    <span class="font-semibold text-slate-600">Factura N°:</span>
                    <span class="font-mono font-bold text-slate-900 text-xs">{{ $compra->numero_factura }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-slate-600">Fecha Emisión:</span>
                    <span class="font-mono text-slate-800">{{ $compra->fecha_emision->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold text-slate-600">Fecha Entrada:</span>
                    <span class="font-mono text-slate-800">{{ $compra->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-slate-200">
                    <span class="font-semibold text-slate-600">Estado:</span>
                    <span class="px-1.5 py-0.5 rounded font-bold text-[9px] uppercase tracking-wider {{ $compra->isRegistrada() ? 'bg-success-bg text-success-text' : 'bg-danger-bg text-danger-text' }}">
                        {{ $compra->estado->value }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cajas Informativas de Proveedor y Recepción -->
    <div class="grid grid-cols-2 gap-3 mt-3">
        <!-- Datos del Proveedor -->
        <div class="border border-slate-300 rounded p-2.5 bg-white">
            <div class="text-[9px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                <span>1. DATOS DEL PROVEEDOR (EMISOR)</span>
            </div>
            <div class="space-y-0.5 text-[10px] text-slate-700">
                <div class="font-bold text-slate-900 text-[11px]">{{ $compra->proveedor?->razon_social }}</div>
                <div><span class="font-semibold">{{ $compra->proveedor?->tipo_documento?->value ?? 'NIT' }}:</span> {{ $compra->proveedor?->numero_documento }}</div>
                @if($compra->proveedor?->nombre_contacto)
                    <div><span class="font-semibold">Contacto:</span> {{ $compra->proveedor->nombre_contacto }}</div>
                @endif
                @if($compra->proveedor?->telefono)
                    <div><span class="font-semibold">Teléfono:</span> {{ $compra->proveedor->telefono }}</div>
                @endif
                @if($compra->proveedor?->email)
                    <div><span class="font-semibold">Email:</span> {{ $compra->proveedor->email }}</div>
                @endif
                @if($compra->proveedor?->direccion)
                    <div><span class="font-semibold">Dirección:</span> {{ $compra->proveedor->direccion }} {{ $compra->proveedor->ciudad ? '(' . $compra->proveedor->ciudad . ')' : '' }}</div>
                @endif
            </div>
        </div>

        <!-- Condiciones y Recepción -->
        <div class="border border-slate-300 rounded p-2.5 bg-white">
            <div class="text-[9px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                <span>2. RECEPCIÓN & CONDICIONES DE PAGO</span>
            </div>
            <div class="space-y-0.5 text-[10px] text-slate-700">
                <div><span class="font-semibold">Forma de Pago:</span> <span class="font-bold text-slate-900">{{ $compra->tipo_pago?->value }}</span></div>
                <div><span class="font-semibold">Sede Destino:</span> {{ $compra->sucursal?->nombre }} (Cód: {{ $compra->sucursal?->codigo ?? 'S/C' }})</div>
                <div><span class="font-semibold">Dirección Sede:</span> {{ $compra->sucursal?->direccion ?? 'N/A' }}</div>
                <div><span class="font-semibold">Registrado / Recibido por:</span> {{ $compra->user?->name ?? 'Usuario del Sistema' }}</div>
                <div class="pt-1 text-[9px] text-slate-700 font-semibold">
                    &bull; Stock ingresado en Kardex como ENTRADA_COMPRA
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Artículos Impresión -->
    <div class="mt-3 border border-slate-800 rounded overflow-hidden">
        <table class="w-full border-collapse text-[10px]">
            <thead>
                <tr class="bg-slate-800 text-white text-left">
                    <th class="py-1 px-2 border-r border-slate-700 w-6">#</th>
                    <th class="py-1 px-2 border-r border-slate-700">Cód / SKU</th>
                    <th class="py-1 px-2 border-r border-slate-700">Descripción de Mercancía</th>
                    <th class="py-1 px-2 border-r border-slate-700 text-right">Cantidad</th>
                    <th class="py-1 px-2 border-r border-slate-700 text-right">Costo Unit.</th>
                    <th class="py-1 px-2 border-r border-slate-700 text-right">Subtotal</th>
                    <th class="py-1 px-2 border-r border-slate-700 text-right">% IVA</th>
                    <th class="py-1 px-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($compra->detalles as $idx => $d)
                <tr class="even:bg-slate-50">
                    <td class="py-1 px-2 border-r border-slate-200 text-center font-mono">{{ $idx + 1 }}</td>
                    <td class="py-1 px-2 border-r border-slate-200 font-mono font-semibold">{{ $d->producto?->codigo ?? $d->producto?->sku ?? '—' }}</td>
                    <td class="py-1 px-2 border-r border-slate-200">
                        <span class="font-semibold">{{ $d->producto?->nombre }}</span>
                        <span class="text-[9px] text-slate-500 font-mono">({{ $d->producto?->unidadMedida?->codigo ?? 'UND' }})</span>
                    </td>
                    <td class="py-1 px-2 border-r border-slate-200 text-right font-mono font-semibold">{{ number_format($d->cantidad, 2) }}</td>
                    <td class="py-1 px-2 border-r border-slate-200 text-right font-mono">${{ number_format($d->costo_unitario, 2) }}</td>
                    <td class="py-1 px-2 border-r border-slate-200 text-right font-mono">${{ number_format($d->subtotal, 2) }}</td>
                    <td class="py-1 px-2 border-r border-slate-200 text-right font-mono">{{ number_format($d->porcentaje_iva, 0) }}%</td>
                    <td class="py-1 px-2 text-right font-mono font-bold">${{ number_format($d->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totales y Firmas Impresión -->
    <div class="grid grid-cols-2 gap-3 mt-3">
        <!-- Notas y Firmas -->
        <div class="space-y-4">
            @if($compra->observaciones)
            <div class="border border-slate-300 rounded p-2 bg-slate-50">
                <span class="font-bold text-[9px] uppercase tracking-wider block text-slate-600">Observaciones:</span>
                <p class="text-[10px] text-slate-700 italic">{{ $compra->observaciones }}</p>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-2 pt-6">
                <div class="text-center">
                    <div class="border-t border-slate-400 pt-1 text-[9px] font-bold text-slate-600 uppercase">
                        Recibido Conforme (Sede)
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t border-slate-400 pt-1 text-[9px] font-bold text-slate-600 uppercase">
                        Entregado por (Proveedor)
                    </div>
                </div>
            </div>
        </div>

        <!-- Liquidación Totales Impresión -->
        <div class="border border-slate-800 rounded overflow-hidden">
            <table class="w-full text-[10px]">
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="py-1 px-2 font-semibold text-slate-600 bg-slate-50">Subtotal Neto:</td>
                        <td class="py-1 px-2 text-right font-mono font-bold text-slate-900">${{ number_format($compra->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 font-semibold text-slate-600 bg-slate-50">Impuestos (IVA):</td>
                        <td class="py-1 px-2 text-right font-mono font-bold text-slate-900">${{ number_format($compra->impuestos, 2) }}</td>
                    </tr>
                    @if($compra->descuento > 0)
                    <tr>
                        <td class="py-1 px-2 font-semibold text-warning-text bg-slate-50">Descuento Comercial:</td>
                        <td class="py-1 px-2 text-right font-mono font-bold text-warning-text">-${{ number_format($compra->descuento, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="bg-slate-800 text-white font-bold">
                        <td class="py-1.5 px-2 text-[11px] uppercase tracking-wider">Total Factura:</td>
                        <td class="py-1.5 px-2 text-right font-mono text-xs">${{ number_format($compra->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
