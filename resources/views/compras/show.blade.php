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
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #0f172a !important;
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
<div class="screen-view print:hidden max-w-[1680px] mx-auto space-y-6" x-data="{
    anularModalOpen: false,
    motivo: ''
}">
    <!-- Header & Acciones Rápidas -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('compras.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center">
                    &larr; Volver al Historial
                </a>
            </div>
            <div class="flex items-center space-x-3 mt-1">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Factura #{{ $compra->numero_factura }}</h1>
                @if($compra->isRegistrada())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Registrada
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> Anulada
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Emitida el {{ $compra->fecha_emision->format('d/m/Y') }} &bull; Registrada por {{ $compra->user?->name ?? 'Usuario del Sistema' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Botón Imprimir -->
            <button type="button" onclick="window.print()"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Comprobante
            </button>

            <!-- Botón Anular (Solo si está Registrada y con permiso) -->
            @can('anular', $compra)
                @if($compra->isRegistrada())
                    <button type="button" @click="anularModalOpen = true"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 transition">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Anular Compra
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <!-- Bloque de Metadatos de la Factura -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Tarjeta Proveedor -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Proveedor / Emisor</div>
            <div class="text-base font-bold text-slate-900">{{ $compra->proveedor?->razon_social ?? 'Proveedor N/A' }}</div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400 font-mono">{{ $compra->proveedor?->tipo_documento?->value }}:</span> {{ $compra->proveedor?->numero_documento }}</div>
                @if($compra->proveedor?->nombre_contacto)
                    <div><span class="text-slate-400">Contacto:</span> {{ $compra->proveedor->nombre_contacto }}</div>
                @endif
                @if($compra->proveedor?->telefono)
                    <div><span class="text-slate-400">Tel:</span> {{ $compra->proveedor->telefono }}</div>
                @endif
                @if($compra->proveedor?->email)
                    <div><span class="text-slate-400">Email:</span> {{ $compra->proveedor->email }}</div>
                @endif
            </div>
        </div>

        <!-- Tarjeta Sede Receptora -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sede Receptora de Mercancía</div>
            <div class="text-base font-bold text-slate-900">{{ $compra->sucursal?->nombre ?? 'Sede N/A' }}</div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400">Código:</span> {{ $compra->sucursal?->codigo ?? 'S/C' }}</div>
                <div><span class="text-slate-400">Dirección:</span> {{ $compra->sucursal?->direccion ?? 'Sin dirección' }}</div>
                <div><span class="text-slate-400">Ciudad:</span> {{ $compra->sucursal?->ciudad ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Tarjeta Condiciones de Pago -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-2">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Condiciones Comerciales</div>
            <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-slate-800">Forma de Pago:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $compra->tipo_pago?->value === 'CONTADO' ? 'bg-slate-100 text-slate-800' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                    {{ $compra->tipo_pago?->value ?? 'CONTADO' }}
                </span>
            </div>
            <div class="text-xs text-slate-600 space-y-1">
                <div><span class="text-slate-400">Fecha de Registro:</span> {{ $compra->created_at->format('d/m/Y H:i') }}</div>
                @if($compra->observaciones)
                    <div class="pt-1 text-slate-500 italic bg-slate-50 p-2 rounded-lg border border-slate-100">
                        "{{ $compra->observaciones }}"
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla Detalle de Artículos Adquiridos -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Artículos y Líneas de Mercancía Recibidas</h2>
            <span class="text-xs font-semibold text-slate-500">{{ $compra->detalles->count() }} ítems</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">SKU</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Producto</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Costo Unitario</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Subtotal</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">% IVA</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($compra->detalles as $idx => $d)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 text-xs text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-bold text-slate-700">
                            {{ $d->producto?->codigo ?? $d->producto?->sku ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $d->producto?->nombre ?? 'Producto Eliminado' }}</div>
                            <div class="text-xs text-slate-400">
                                Unidad: {{ $d->producto?->unidadMedida?->nombre ?? 'Unidad' }} ({{ $d->producto?->unidadMedida?->codigo ?? 'UND' }})
                            </div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono font-bold text-sm text-slate-900">
                            {{ number_format($d->cantidad, 2) }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono text-sm text-slate-700">
                            ${{ number_format($d->costo_unitario, 2) }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono text-sm text-slate-700">
                            ${{ number_format($d->subtotal, 2) }}
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono text-xs text-slate-500">
                            {{ number_format($d->porcentaje_iva, 0) }}% (${{ number_format($d->valor_iva, 2) }})
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-right font-mono font-black text-sm text-slate-900">
                            ${{ number_format($d->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totales Liquidación -->
        <div class="bg-slate-50/70 p-6 border-t border-slate-100">
            <div class="max-w-xs ml-auto space-y-2 text-sm">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Neto:</span>
                    <span class="font-mono font-semibold text-slate-900">${{ number_format($compra->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Impuestos (IVA):</span>
                    <span class="font-mono font-semibold text-slate-900">${{ number_format($compra->impuestos, 2) }}</span>
                </div>
                @if($compra->descuento > 0)
                <div class="flex justify-between text-amber-600">
                    <span>Descuento Comercial:</span>
                    <span class="font-mono font-semibold">-${{ number_format($compra->descuento, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                    <span class="text-base font-bold text-slate-900">Total Liquidado:</span>
                    <span class="text-2xl font-black text-slate-900 font-mono">${{ number_format($compra->total, 2) }}</span>
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
                 class="relative w-full max-w-md transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all border border-slate-200">

                <form action="{{ route('compras.anular', $compra) }}" method="POST">
                    @csrf

                    <div class="p-6 space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <div class="text-center">
                            <h3 class="text-lg font-bold text-slate-900">¿Anular Factura de Compra?</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Esta acción revertirá las existencias ingresadas en la sede <strong class="text-slate-700">{{ $compra->sucursal?->nombre }}</strong> registrando una devolución al proveedor en el Kardex.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Motivo de la Anulación <span class="text-red-500">*</span>
                            </label>
                            <textarea name="motivo" x-model="motivo" rows="3" required
                                placeholder="Indica la razón: error de digitación, devolución de mercancía por garantía, etc."
                                class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition"></textarea>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex items-center justify-end space-x-3 rounded-b-3xl">
                        <button type="button" @click="anularModalOpen = false"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="!motivo.trim()"
                            class="px-5 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-sm transition">
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
                <div class="h-8 w-8 bg-slate-900 text-white font-black text-sm rounded flex items-center justify-center">
                    POS
                </div>
                <h1 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-none">
                    {{ $compra->empresa?->razon_social ?? $compra->empresa?->nombre_comercial ?? auth()->user()->empresa?->nombre_comercial ?? 'POS COMERCIAL' }}
                </h1>
            </div>
            <p class="text-[11px] font-bold text-slate-700">
                NIT: {{ $compra->empresa?->nit ?? auth()->user()->empresa?->nit ?? '900123456' }}{{ ($compra->empresa?->dv || auth()->user()->empresa?->dv) ? '-' . ($compra->empresa?->dv ?? auth()->user()->empresa?->dv) : '' }}
                &bull; Régimen Común / Responsable de IVA
            </p>
            <p class="text-[10px] text-slate-600">
                <span class="font-bold">Sede Receptora:</span> {{ $compra->sucursal?->nombre }} (Cód: {{ $compra->sucursal?->codigo ?? 'S/C' }})
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
                <span class="font-black text-[11px] uppercase tracking-wider block">COMPROBANTE DE COMPRA</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-300 block">Entrada a Inventario</span>
            </div>
            <div class="p-2 bg-slate-50 space-y-1 text-left text-[10px]">
                <div class="flex justify-between items-baseline">
                    <span class="font-bold text-slate-600">Factura N°:</span>
                    <span class="font-mono font-black text-slate-900 text-xs">{{ $compra->numero_factura }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-slate-600">Fecha Emisión:</span>
                    <span class="font-mono text-slate-800">{{ $compra->fecha_emision->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-slate-600">Fecha Entrada:</span>
                    <span class="font-mono text-slate-800">{{ $compra->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-slate-200">
                    <span class="font-bold text-slate-600">Estado:</span>
                    <span class="px-1.5 py-0.5 rounded font-black text-[9px] uppercase tracking-wider {{ $compra->isRegistrada() ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900' }}">
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
            <div class="text-[9px] font-black text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                <span>1. DATOS DEL PROVEEDOR (EMISOR)</span>
            </div>
            <div class="space-y-0.5 text-[10px] text-slate-700">
                <div class="font-black text-slate-900 text-[11px]">{{ $compra->proveedor?->razon_social }}</div>
                <div><span class="font-bold">{{ $compra->proveedor?->tipo_documento?->value ?? 'NIT' }}:</span> {{ $compra->proveedor?->numero_documento }}</div>
                @if($compra->proveedor?->nombre_contacto)
                    <div><span class="font-bold">Contacto:</span> {{ $compra->proveedor->nombre_contacto }}</div>
                @endif
                @if($compra->proveedor?->telefono)
                    <div><span class="font-bold">Teléfono:</span> {{ $compra->proveedor->telefono }}</div>
                @endif
                @if($compra->proveedor?->email)
                    <div><span class="font-bold">Email:</span> {{ $compra->proveedor->email }}</div>
                @endif
                @if($compra->proveedor?->direccion)
                    <div><span class="font-bold">Dirección:</span> {{ $compra->proveedor->direccion }} {{ $compra->proveedor->ciudad ? '(' . $compra->proveedor->ciudad . ')' : '' }}</div>
                @endif
            </div>
        </div>

        <!-- Condiciones y Recepción -->
        <div class="border border-slate-300 rounded p-2.5 bg-white">
            <div class="text-[9px] font-black text-slate-700 uppercase tracking-wider border-b border-slate-200 pb-1 mb-1.5 flex items-center justify-between">
                <span>2. RECEPCIÓN & CONDICIONES DE PAGO</span>
            </div>
            <div class="space-y-0.5 text-[10px] text-slate-700">
                <div><span class="font-bold">Forma de Pago:</span> <span class="font-bold text-slate-900">{{ $compra->tipo_pago?->value }}</span></div>
                <div><span class="font-bold">Sede Destino:</span> {{ $compra->sucursal?->nombre }} (Cód: {{ $compra->sucursal?->codigo ?? 'S/C' }})</div>
                <div><span class="font-bold">Dirección Sede:</span> {{ $compra->sucursal?->direccion ?? 'N/A' }}</div>
                <div><span class="font-bold">Registrado / Recibido por:</span> {{ $compra->user?->name ?? 'Usuario del Sistema' }}</div>
                <div class="pt-1 text-[9px] text-indigo-900 font-bold">
                    &bull; Stock ingresado en Kardex como ENTRADA_COMPRA
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Artículos Recibidos -->
    <div class="mt-3 border border-slate-300 rounded overflow-hidden">
        <table class="w-full text-left border-collapse text-[10px]">
            <thead>
                <tr class="bg-slate-800 text-white font-bold uppercase text-[9px] tracking-wider">
                    <th class="py-1.5 px-2 text-center w-8 border-r border-slate-700">#</th>
                    <th class="py-1.5 px-2.5 w-24 border-r border-slate-700">SKU / CÓD.</th>
                    <th class="py-1.5 px-3 border-r border-slate-700">DESCRIPCIÓN DEL ARTÍCULO</th>
                    <th class="py-1.5 px-2 text-center w-14 border-r border-slate-700">U.M.</th>
                    <th class="py-1.5 px-2 text-right w-16 border-r border-slate-700">CANT.</th>
                    <th class="py-1.5 px-2.5 text-right w-24 border-r border-slate-700">COSTO UNIT.</th>
                    <th class="py-1.5 px-2.5 text-right w-24 border-r border-slate-700">SUBTOTAL</th>
                    <th class="py-1.5 px-2 text-right w-20 border-r border-slate-700">% IVA</th>
                    <th class="py-1.5 px-3 text-right w-28">TOTAL</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($compra->detalles as $idx => $d)
                <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                    <td class="py-1.5 px-2 text-center font-mono text-slate-500 border-r border-slate-200">{{ $idx + 1 }}</td>
                    <td class="py-1.5 px-2.5 font-mono font-bold text-slate-800 border-r border-slate-200">
                        {{ $d->producto?->codigo ?? $d->producto?->sku ?? '—' }}
                    </td>
                    <td class="py-1.5 px-3 border-r border-slate-200">
                        <div class="font-bold text-slate-900 leading-tight">{{ $d->producto?->nombre ?? 'Producto Eliminado' }}</div>
                        @if($d->producto?->categoria)
                            <div class="text-[8px] text-slate-400">Cat: {{ $d->producto->categoria->nombre }}</div>
                        @endif
                    </td>
                    <td class="py-1.5 px-2 text-center font-mono text-[9px] text-slate-600 border-r border-slate-200">
                        {{ $d->producto?->unidadMedida?->codigo ?? 'UND' }}
                    </td>
                    <td class="py-1.5 px-2 text-right font-mono font-bold text-slate-900 border-r border-slate-200">
                        {{ number_format($d->cantidad, 2) }}
                    </td>
                    <td class="py-1.5 px-2.5 text-right font-mono text-slate-700 border-r border-slate-200">
                        ${{ number_format($d->costo_unitario, 2) }}
                    </td>
                    <td class="py-1.5 px-2.5 text-right font-mono text-slate-700 border-r border-slate-200">
                        ${{ number_format($d->subtotal, 2) }}
                    </td>
                    <td class="py-1.5 px-2 text-right font-mono text-[9px] text-slate-600 border-r border-slate-200">
                        {{ number_format($d->porcentaje_iva, 0) }}% (${{ number_format($d->valor_iva, 2) }})
                    </td>
                    <td class="py-1.5 px-3 text-right font-mono font-black text-slate-900">
                        ${{ number_format($d->total, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bloque Inferior: Observaciones y Totales -->
    <div class="grid grid-cols-12 gap-3 mt-3">
        <!-- Notas y Observaciones -->
        <div class="col-span-7 flex flex-col justify-between space-y-2">
            @if($compra->observaciones)
            <div class="border border-slate-200 rounded p-2 bg-slate-50">
                <span class="text-[9px] font-bold text-slate-600 uppercase tracking-wider block">Observaciones:</span>
                <p class="text-[10px] text-slate-800 italic mt-0.5 leading-relaxed">{{ $compra->observaciones }}</p>
            </div>
            @endif
            <div class="text-[9px] text-slate-400 leading-tight">
                <p><strong>Certificación:</strong> Este documento certifica la recepción física y registro contable de los artículos relacionados en la sucursal receptora. Los costos ingresados actualizan el costo de reposición del inventario maestro.</p>
            </div>
        </div>

        <!-- Cuadro de Totales -->
        <div class="col-span-5">
            <div class="border-2 border-slate-300 rounded overflow-hidden bg-white">
                <table class="w-full text-left text-[10px]">
                    <tbody>
                        <tr class="border-b border-slate-100">
                            <td class="py-1 px-3 font-semibold text-slate-600">Subtotal Neto:</td>
                            <td class="py-1 px-3 text-right font-mono font-bold text-slate-900">${{ number_format($compra->subtotal, 2) }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1 px-3 font-semibold text-slate-600">Impuestos (IVA):</td>
                            <td class="py-1 px-3 text-right font-mono font-bold text-slate-900">${{ number_format($compra->impuestos, 2) }}</td>
                        </tr>
                        @if($compra->descuento > 0)
                        <tr class="border-b border-slate-100 bg-amber-50/50">
                            <td class="py-1 px-3 font-semibold text-amber-700">Descuento Comercial:</td>
                            <td class="py-1 px-3 text-right font-mono font-bold text-amber-700">-${{ number_format($compra->descuento, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="bg-slate-800 text-white font-black">
                            <td class="py-2 px-3 text-[11px] uppercase tracking-wider">TOTAL COMPROBANTE:</td>
                            <td class="py-2 px-3 text-right font-mono text-sm tracking-tight">${{ number_format($compra->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Firmas de Responsabilidad Legal / Contable -->
    <div class="grid grid-cols-3 gap-6 mt-8 pt-4 border-t border-slate-300 text-center text-[9px]">
        <div>
            <div class="border-b border-slate-400 h-8 mx-2"></div>
            <div class="font-bold text-slate-900 uppercase mt-1">Recibido Conforme</div>
            <div class="text-slate-500">Almacén / Responsable Sede</div>
            <div class="text-slate-400 mt-0.5 font-mono">C.C. ___________________</div>
        </div>
        <div>
            <div class="border-b border-slate-400 h-8 mx-2"></div>
            <div class="font-bold text-slate-900 uppercase mt-1">Entregado Por</div>
            <div class="text-slate-500">Proveedor / Transportador</div>
            <div class="text-slate-400 mt-0.5 font-mono">C.C./NIT _______________</div>
        </div>
        <div>
            <div class="border-b border-slate-400 h-8 mx-2"></div>
            <div class="font-bold text-slate-900 uppercase mt-1">Aprobado</div>
            <div class="text-slate-500">Contabilidad / Gerencia</div>
            <div class="text-slate-400 mt-0.5 font-mono">Fecha: _________________</div>
        </div>
    </div>

    <!-- Pie de Página de Auditoría del Sistema -->
    <div class="mt-6 pt-2 border-t border-slate-200 flex items-center justify-between text-[8px] text-slate-400 font-mono">
        <span>POS Comercial &bull; Sistema Integrado de Inventario y Ventas</span>
        <span>Impreso el {{ now()->format('d/m/Y H:i:s') }} &bull; Usuario: {{ auth()->user()->name ?? 'Admin' }}</span>
    </div>
</div>

@if(request()->boolean('print'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
@endif
@endsection
