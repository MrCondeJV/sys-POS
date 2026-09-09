@extends('layouts.app')

@section('title', 'Detalle de Cuenta por Cobrar: ' . $cuenta->numero_documento)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    abonoModalOpen: false
}">

    <!-- Breadcrumb & Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('cartera.index') }}" class="hover:text-indigo-600 transition">Crédito & Cartera</a>
                <span>/</span>
                <span class="text-slate-800">{{ $cuenta->numero_documento }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Cuenta por Cobrar: {{ $cuenta->numero_documento }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $cuenta->estado->badgeClasses() }}">
                    {{ $cuenta->estado->label() }}
                </span>
            </div>
            <p class="text-sm text-slate-500">{{ $cuenta->concepto }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('cartera.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Listado
            </a>

            <button type="button" onclick="window.print()"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm"
                title="Imprimir ficha de cuenta por cobrar">
                <svg class="h-4 w-4 mr-1.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Ficha
            </button>

            <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-emerald-300 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Estado de Cuenta
            </a>

            @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                <button @click="abonoModalOpen = true" type="button"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Abonar
                </button>
            @endif
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            @if(session('recibo_id'))
                <a href="{{ route('cartera.recibo', session('recibo_id')) }}" target="_blank"
                    class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 transition">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Recibo de Caja
                </a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center space-x-2">
            <svg class="h-5 w-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna Izquierda: Información de Deuda y Cliente -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Tarjeta 1: Estado del Saldo -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado del Saldo</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $cuenta->porcentajePagado() }}% pagado</span>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-medium">Saldo Pendiente por Cobrar:</div>
                    <div class="text-3xl font-black mt-1 {{ (float) $cuenta->saldo_pendiente <= 0 ? 'text-emerald-600' : ($cuenta->estaVencida() ? 'text-rose-600' : 'text-slate-900') }}">
                        ${{ number_format($cuenta->saldo_pendiente, 2) }}
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $cuenta->estado->value === 'PAGADA' ? 'bg-emerald-500' : ($cuenta->estaVencida() ? 'bg-rose-500' : 'bg-indigo-600') }}"
                         style="width: {{ $cuenta->porcentajePagado() }}%"></div>
                </div>

                <div class="space-y-2.5 pt-2 text-xs border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Monto Total Inicial:</span>
                        <span class="font-bold text-slate-900">${{ number_format($cuenta->monto_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Monto Abonado / Pagado:</span>
                        <span class="font-bold text-emerald-600">${{ number_format($cuenta->monto_pagado, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Fecha de Emisión:</span>
                        <span class="font-semibold text-slate-800">{{ $cuenta->fecha_emision->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Fecha de Vencimiento:</span>
                        <span class="font-semibold text-slate-800">{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-slate-500">Condición de Plazo:</span>
                        @if($cuenta->estado->value === 'PAGADA')
                            <span class="font-bold text-emerald-600">Totalmente Cancelada</span>
                        @elseif($cuenta->estaVencida())
                            <span class="font-bold text-rose-600 flex items-center">
                                <svg class="h-3.5 w-3.5 mr-1 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ abs($cuenta->diasDiferenciaVencimiento()) }} días de mora
                            </span>
                        @else
                            <span class="font-semibold text-slate-700">Faltan {{ $cuenta->diasDiferenciaVencimiento() }} días</span>
                        @endif
                    </div>
                </div>

                @if($cuenta->observaciones)
                    <div class="p-3 bg-slate-50 rounded-xl text-xs text-slate-600 border border-slate-100">
                        <strong class="text-slate-700 block mb-0.5">Observaciones:</strong>
                        {{ $cuenta->observaciones }}
                    </div>
                @endif
            </div>

            <!-- Tarjeta 2: Datos del Cliente -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cliente Titular</span>
                    <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Ver Cartera &rarr;
                    </a>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="h-10 w-10 flex-shrink-0 rounded-xl flex items-center justify-center font-bold text-sm {{ $cuenta->cliente->tipo_persona->value === 'JURIDICA' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700' }}">
                        {{ strtoupper(substr($cuenta->cliente->razon_social, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-sm truncate">
                            {{ $cuenta->cliente->razon_social }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $cuenta->cliente->tipo_documento->value }} {{ $cuenta->cliente->numero_documento }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2 text-xs border-t border-slate-100 text-slate-600">
                    <div class="flex items-center justify-between">
                        <span>Teléfono:</span>
                        <span class="font-medium text-slate-900">{{ $cuenta->cliente->telefono ?? 'Sin registrar' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Email:</span>
                        <span class="font-medium text-slate-900 truncate max-w-xs">{{ $cuenta->cliente->email ?? 'Sin registrar' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Cupo de Crédito:</span>
                        <span class="font-bold text-slate-900">${{ number_format($cuenta->cliente->cupo_credito, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Cupo Disponible:</span>
                        <span class="font-bold text-emerald-600">${{ number_format($cuenta->cliente->cupoDisponible(), 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Historial de Pagos / Abonos -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Historial de Pagos & Abonos</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Recibos de caja aplicados transaccionalmente a esta obligación.</p>
                    </div>

                    @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                        <button @click="abonoModalOpen = true" type="button"
                            class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nuevo Abono
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Recibo & Fecha
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Método / Ref
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Monto Abonado
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Nuevo Saldo
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($cuenta->pagos as $pago)
                                <tr class="hover:bg-slate-50/70 transition {{ $pago->isAnulado() ? 'opacity-60 bg-slate-50/40' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900">
                                            {{ $pago->numero_recibo }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $pago->fecha_pago->format('d/m/Y') }}
                                        </div>
                                        @if($pago->usuario)
                                            <div class="text-[10px] text-slate-400">Por: {{ $pago->usuario->name }}</div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $pago->metodo_pago->label() }}
                                        </span>
                                        @if($pago->referencia_pago)
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $pago->referencia_pago }}</div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-black text-emerald-600">
                                            ${{ number_format($pago->monto, 2) }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            Ant: ${{ number_format($pago->saldo_anterior, 2) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="text-sm font-bold text-slate-800">
                                            ${{ number_format($pago->saldo_posterior, 2) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($pago->estado->value === 'APLICADO')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Aplicado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                Anulado
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="{{ route('cartera.recibo', $pago) }}?print=1" target="_blank"
                                                class="inline-flex items-center px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-lg transition shadow-sm"
                                                title="Imprimir comprobante de recibo">
                                                <svg class="h-3.5 w-3.5 mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                Imprimir Recibo
                                            </a>

                                            @if(!$pago->isAnulado())
                                                <form action="{{ route('cartera.recibo.anular', $pago) }}" method="POST"
                                                    onsubmit="return confirm('¿Estás seguro de que deseas anular este abono? El saldo de la obligación se incrementará de inmediato.');"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                        title="Anular abono">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400 text-xs">
                                        No se han registrado abonos todavía para esta cuenta por cobrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal para Registrar Abono -->
    <div x-cloak x-show="abonoModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="abonoModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                 @click="abonoModalOpen = false"></div>

            <div x-show="abonoModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-100 p-6 space-y-5">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Aplicar Abono a: {{ $cuenta->numero_documento }}</h3>
                        <p class="text-xs text-slate-500">{{ $cuenta->cliente->razon_social }}</p>
                    </div>
                    <button type="button" @click="abonoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-900">Saldo Pendiente:</span>
                    <span class="text-base font-black text-indigo-700">${{ number_format($cuenta->saldo_pendiente, 2) }}</span>
                </div>

                <form action="{{ route('cartera.abono.store', $cuenta) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Monto a Abonar ($ COP) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                            <input type="number" step="0.01" min="0.01" max="{{ $cuenta->saldo_pendiente }}" name="monto" value="{{ $cuenta->saldo_pendiente }}" required
                                class="block w-full pl-8 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Método de Pago <span class="text-rose-500">*</span>
                            </label>
                            <select name="metodo_pago" required class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                @foreach(\App\Enums\MetodoPagoCartera::cases() as $mp)
                                    <option value="{{ $mp->value }}">{{ $mp->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Fecha de Pago <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" required
                                class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Referencia / N° Comprobante <span class="text-slate-400 text-[10px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="referencia_pago" placeholder="Ej: TR-192834 ó Nequi 310..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Notas de Abono <span class="text-slate-400 text-[10px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="notas" placeholder="Ej: Pago total de saldo..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="abonoModalOpen = false"
                            class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                            Confirmar Abono
                        </button>
                    </div>
                </form>

            </div>
        </div>
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
