@extends('layouts.app')

@section('title', 'Detalle de Cuenta por Cobrar: ' . $cuenta->numero_documento)

@section('content')
<div class="space-y-6" x-data="{
    abonoModalOpen: false
}">

    <!-- Breadcrumb & Encabezado -->
    <x-page-header
        title="Cuenta por Cobrar: {{ $cuenta->numero_documento }}"
        description="{{ $cuenta->concepto }}"
        :breadcrumbs="[
            ['label' => 'Crédito & Cartera', 'url' => route('cartera.index')],
            ['label' => $cuenta->numero_documento]
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cartera.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Listado
            </x-button>

            <x-button variant="secondary" href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Estado de Cuenta
            </x-button>

            @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                <x-button variant="primary" @click="abonoModalOpen = true" type="button">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Abonar
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <!-- Alertas Flash -->
    @if(session('success'))
        <div class="p-4 rounded-lg bg-success-bg border border-success-border text-success-text flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-success-text flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-semibold">{{ session('success') }}</span>
            </div>
            @if(session('recibo_id'))
                <x-button variant="secondary" size="sm" href="{{ route('cartera.recibo', session('recibo_id')) }}" target="_blank">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Recibo de Caja
                </x-button>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-lg bg-danger-bg border border-danger-border text-danger-text flex items-center space-x-2">
            <svg class="h-5 w-5 text-danger-text flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna Izquierda: Información de Deuda y Cliente -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Tarjeta 1: Estado del Saldo -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado del Saldo</span>
                    <x-status-badge :status="$cuenta->estado->value" />
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-medium">Saldo Pendiente por Cobrar:</div>
                    <div class="text-2xl font-bold font-mono tabular-nums mt-1 {{ (float) $cuenta->saldo_pendiente <= 0 ? 'text-success-text' : ($cuenta->estaVencida() ? 'text-danger-text' : 'text-slate-900') }}">
                        ${{ number_format($cuenta->saldo_pendiente, 2) }}
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div>
                    <div class="flex justify-between text-xs text-slate-500 font-medium mb-1">
                        <span>Progreso de pago</span>
                        <span>{{ $cuenta->porcentajePagado() }}%</span>
                    </div>
                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $cuenta->estado->value === 'PAGADA' ? 'bg-success-text' : ($cuenta->estaVencida() ? 'bg-danger-text' : 'bg-brand-600') }}"
                             style="width: {{ $cuenta->porcentajePagado() }}%"></div>
                    </div>
                </div>

                <div class="space-y-2.5 pt-2 text-xs border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Monto Total Inicial:</span>
                        <span class="font-semibold font-mono text-slate-900">${{ number_format($cuenta->monto_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Monto Abonado / Pagado:</span>
                        <span class="font-semibold font-mono text-success-text">${{ number_format($cuenta->monto_pagado, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Fecha de Emisión:</span>
                        <span class="font-medium text-slate-800">{{ $cuenta->fecha_emision->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Fecha de Vencimiento:</span>
                        <span class="font-medium text-slate-800">{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-slate-500">Condición de Plazo:</span>
                        @if($cuenta->estado->value === 'PAGADA')
                            <span class="font-semibold text-success-text">Totalmente Cancelada</span>
                        @elseif($cuenta->estaVencida())
                            <span class="font-semibold text-danger-text flex items-center">
                                <svg class="h-3.5 w-3.5 mr-1 text-danger-text" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ abs($cuenta->diasDiferenciaVencimiento()) }} días de mora
                            </span>
                        @else
                            <span class="font-medium text-slate-700">Faltan {{ $cuenta->diasDiferenciaVencimiento() }} días</span>
                        @endif
                    </div>
                </div>

                @if($cuenta->observaciones)
                    <div class="p-3 bg-slate-50 rounded-lg text-xs text-slate-600 border border-slate-200">
                        <strong class="text-slate-700 block mb-0.5">Observaciones:</strong>
                        {{ $cuenta->observaciones }}
                    </div>
                @endif
            </div>

            <!-- Tarjeta 2: Datos del Cliente -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente Titular</span>
                    <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                        Ver Cartera &rarr;
                    </a>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="h-9 w-9 flex-shrink-0 rounded-lg flex items-center justify-center font-bold text-xs {{ $cuenta->cliente->tipo_persona->value === 'JURIDICA' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ strtoupper(substr($cuenta->cliente->razon_social, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 text-sm truncate">
                            {{ $cuenta->cliente->razon_social }}
                        </div>
                        <div class="text-xs text-slate-500 font-mono">
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
                        <span class="font-semibold font-mono text-slate-900">${{ number_format($cuenta->cliente->cupo_credito, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Cupo Disponible:</span>
                        <span class="font-bold font-mono text-success-text">${{ number_format($cuenta->cliente->cupoDisponible(), 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Historial de Pagos / Abonos -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Historial de Pagos & Abonos</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Recibos de caja aplicados transaccionalmente a esta obligación.</p>
                    </div>

                    @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                        <x-button size="sm" variant="primary" @click="abonoModalOpen = true" type="button">
                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nuevo Abono
                        </x-button>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">
                                    Recibo & Fecha
                                </th>
                                <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">
                                    Método / Ref
                                </th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-600 uppercase tracking-wider">
                                    Monto Abonado
                                </th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-600 uppercase tracking-wider">
                                    Nuevo Saldo
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($cuenta->pagos as $pago)
                                <tr class="hover:bg-slate-50/60 transition {{ $pago->isAnulado() ? 'opacity-60 bg-slate-50/40' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-slate-900">
                                            {{ $pago->numero_recibo }}
                                        </div>
                                        <div class="text-slate-500">
                                            {{ $pago->fecha_pago->format('d/m/Y') }}
                                        </div>
                                        @if($pago->usuario)
                                            <div class="text-[10px] text-slate-400">Por: {{ $pago->usuario->name }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <x-badge color="neutral">
                                            {{ $pago->metodo_pago->label() }}
                                        </x-badge>
                                        @if($pago->referencia_pago)
                                            <div class="text-slate-500 mt-0.5">{{ $pago->referencia_pago }}</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="font-semibold font-mono text-success-text">
                                            ${{ number_format($pago->monto, 2) }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Ant: ${{ number_format($pago->saldo_anterior, 2) }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <div class="font-semibold font-mono text-slate-800">
                                            ${{ number_format($pago->saldo_posterior, 2) }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        @if($pago->estado->value === 'APLICADO')
                                            <x-badge color="success">Aplicado</x-badge>
                                        @else
                                            <x-badge color="danger">Anulado</x-badge>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right text-xs">
                                        <div class="flex items-center justify-end space-x-1.5">
                                            <x-button size="sm" variant="secondary" href="{{ route('cartera.recibo', $pago) }}?print=1" target="_blank" title="Imprimir comprobante de recibo">
                                                <svg class="h-3.5 w-3.5 mr-1 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                                Imprimir
                                            </x-button>

                                            @if(!$pago->isAnulado())
                                                <x-modal-confirm
                                                    name="anular-recibo-{{ $pago->id }}"
                                                    title="Anular recibo de abono {{ $pago->numero_recibo }}"
                                                    message="¿Estás seguro de que deseas anular este abono por ${{ number_format($pago->monto, 2) }}?"
                                                    consequence="El recibo quedará marcado como ANULADO y el saldo pendiente de la cuenta por cobrar se incrementará nuevamente en ${{ number_format($pago->monto, 2) }}."
                                                    confirmText="Anular abono"
                                                    variant="danger"
                                                    :action="route('cartera.recibo.anular', $pago)"
                                                    method="POST"
                                                >
                                                    <x-slot:trigger>
                                                        <x-icon-button type="button" variant="danger" icon="trash" title="Anular abono" />
                                                    </x-slot:trigger>
                                                </x-modal-confirm>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">
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
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs"
                 @click="abonoModalOpen = false"></div>

            <div x-show="abonoModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Aplicar Abono: {{ $cuenta->numero_documento }}</h3>
                        <p class="text-xs text-slate-500">{{ $cuenta->cliente->razon_social }}</p>
                    </div>
                    <button type="button" @click="abonoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-3.5 rounded-lg bg-brand-50 border border-brand-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-brand-900">Saldo Pendiente:</span>
                    <span class="text-base font-bold font-mono tabular-nums text-brand-700">${{ number_format($cuenta->saldo_pendiente, 2) }}</span>
                </div>

                <form action="{{ route('cartera.abono.store', $cuenta) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Monto a Abonar ($ COP) <span class="text-danger-text">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" max="{{ $cuenta->saldo_pendiente }}" name="monto" value="{{ $cuenta->saldo_pendiente }}" required
                                class="block w-full pl-7 pr-3 py-2 border border-slate-300 rounded-lg text-sm font-semibold font-mono tabular-nums text-slate-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Método de Pago <span class="text-danger-text">*</span>
                            </label>
                            <select name="metodo_pago" required class="input">
                                @foreach(\App\Enums\MetodoPagoCartera::cases() as $mp)
                                    <option value="{{ $mp->value }}">{{ $mp->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                Fecha de Pago <span class="text-danger-text">*</span>
                            </label>
                            <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" required
                                class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm text-slate-700 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Referencia / N° Comprobante <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="referencia_pago" placeholder="Ej: TR-192834 ó Nequi 310..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            Notas de Abono <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="notas" placeholder="Ej: Pago total de saldo..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <x-button type="button" variant="secondary" @click="abonoModalOpen = false">
                            Cancelar
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Confirmar Abono
                        </x-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
