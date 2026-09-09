@extends('layouts.app')

@section('title', 'Crédito y Cartera')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6" x-data="{
    abonoModalOpen: false,
    cuentaSeleccionada: {
        id: null,
        numero: '',
        cliente: '',
        saldo: 0,
        urlAbono: ''
    },
    abrirModalAbono(cuenta) {
        this.cuentaSeleccionada = {
            id: cuenta.id,
            numero: cuenta.numero_documento,
            cliente: cuenta.cliente ? cuenta.cliente.razon_social : 'Cliente',
            saldo: parseFloat(cuenta.saldo_pendiente),
            urlAbono: '/cartera/' + cuenta.id + '/abono'
        };
        this.abonoModalOpen = true;
    }
}">

    <!-- Encabezado & Acciones -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Fase 8
                </span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Crédito y Cartera</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Control de cuentas por cobrar, recaudos, abonos parciales, cartera en mora y estados de cuenta.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 shadow-sm transition">
                <svg class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Cartera
            </button>
            <a href="{{ route('cartera.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Cuenta por Cobrar
            </a>
        </div>
    </div>

    <!-- Mensajes Flash de Éxito / Error -->
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

    <!-- Tarjetas de Métricas Rápidas (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Saldo Total Cartera -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera Total por Cobrar</div>
                <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">${{ number_format($totalCartera, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Saldo pendiente acumulado</div>
        </div>

        <!-- Cartera Vigente -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera Vigente</div>
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600">${{ number_format($carteraVigente, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Facturas dentro del plazo</div>
        </div>

        <!-- Cartera Vencida (En Mora) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera Vencida (Mora)</div>
                <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-600">${{ number_format($carteraVencida, 2) }}</div>
            <div class="text-xs text-rose-600 font-semibold mt-1">
                {{ $facturasEnMora }} {{ $facturasEnMora === 1 ? 'cuenta vencida' : 'cuentas vencidas' }}
            </div>
        </div>

        <!-- Total Recaudado en el Mes -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recaudo de Cartera (Mes)</div>
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-600">${{ number_format($totalRecaudadoMes, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Abonos aplicados en {{ now()->translatedFormat('F') }}</div>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('cartera.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    placeholder="Buscar por documento, concepto o cliente..."
                    class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="cliente_id" class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected(($clienteId ?? '') == $c->id)>
                            {{ $c->razon_social }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="estado" class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTES" @selected(($estado ?? '') === 'PENDIENTES')>Todas Pendientes (Con saldo)</option>
                    <option value="VIGENTE" @selected(($estado ?? '') === 'VIGENTE')>Vigentes (Al día)</option>
                    <option value="VENCIDA" @selected(($estado ?? '') === 'VENCIDA')>Vencidas (En mora)</option>
                    <option value="PAGADA" @selected(($estado ?? '') === 'PAGADA')>Totalmente Pagadas</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 transition">
                    Filtrar
                </button>
                @if(!empty($term) || !empty($estado) || !empty($clienteId))
                    <a href="{{ route('cartera.index') }}"
                        class="p-2 border border-slate-300 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-50 transition" title="Limpiar filtros">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Cuentas por Cobrar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Documento / Concepto
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Cliente Deudor
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Deuda & Saldo
                        </th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Vencimiento
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
                    @forelse($cuentas as $cuenta)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-xl flex items-center justify-center font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        CXC
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('cartera.show', $cuenta) }}" class="font-bold text-indigo-600 hover:text-indigo-800 text-sm">
                                                {{ $cuenta->numero_documento }}
                                            </a>
                                            <span class="text-xs text-slate-400">({{ $cuenta->fecha_emision->format('d/m/Y') }})</span>
                                        </div>
                                        <span class="text-xs text-slate-600 font-medium block truncate max-w-xs">
                                            {{ $cuenta->concepto }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 block">
                                            {{ $cuenta->sucursal?->nombre ?? 'Sede Central' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="min-w-0">
                                    <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}"
                                        class="text-sm font-bold text-slate-900 hover:text-indigo-600 transition block truncate max-w-xs"
                                        title="Ver estado de cuenta de {{ $cuenta->cliente->razon_social }}">
                                        {{ $cuenta->cliente->razon_social }}
                                    </a>
                                    <span class="text-xs text-slate-500 font-medium block">
                                        {{ $cuenta->cliente->tipo_documento->value }} {{ $cuenta->cliente->numero_documento }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block">
                                        {{ $cuenta->cliente->telefono ?? 'Sin teléfono' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-black {{ (float) $cuenta->saldo_pendiente > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                    ${{ number_format($cuenta->saldo_pendiente, 2) }}
                                    <span class="text-xs font-normal text-slate-400">/ ${{ number_format($cuenta->monto_total, 2) }}</span>
                                </div>
                                <div class="w-36 mt-1.5">
                                    <div class="flex justify-between text-[10px] text-slate-500 font-semibold mb-0.5">
                                        <span>{{ $cuenta->porcentajePagado() }}% pagado</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $cuenta->estado->value === 'PAGADA' ? 'bg-emerald-500' : ($cuenta->estaVencida() ? 'bg-rose-500' : 'bg-indigo-600') }}"
                                             style="width: {{ $cuenta->porcentajePagado() }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}
                                </div>
                                @if($cuenta->estado->value === 'PAGADA')
                                    <span class="text-xs text-emerald-600 font-medium">Cancelada</span>
                                @elseif($cuenta->estaVencida())
                                    <div class="mt-0.5 inline-flex items-center text-xs font-bold text-rose-600">
                                        <svg class="h-3.5 w-3.5 mr-1 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        {{ abs($cuenta->diasDiferenciaVencimiento()) }} días de mora
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500">
                                        Vence en {{ $cuenta->diasDiferenciaVencimiento() }} días
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $cuenta->estado->badgeClasses() }}">
                                    {{ $cuenta->estado->label() }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    @if((float) $cuenta->saldo_pendiente > 0 && $cuenta->estado->value !== 'ANULADA')
                                        <button @click="abrirModalAbono({{ json_encode($cuenta) }})" type="button"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm"
                                            title="Registrar Abono">
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Abonar
                                        </button>
                                    @endif

                                    <a href="{{ route('cartera.show', $cuenta) }}"
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                        title="Ver detalle de la cuenta">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('cartera.show', $cuenta) }}?print=1" target="_blank"
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                        title="Imprimir Ficha de Cuenta">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('cartera.estado-cuenta', $cuenta->cliente) }}"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                        title="Ver estado de cuenta completo del cliente">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div class="h-12 w-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900">No hay cuentas por cobrar</h3>
                                    <p class="text-xs text-slate-500 mt-1">No se encontraron cuentas con los filtros o términos de búsqueda seleccionados.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('cartera.create') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                            Registrar Cuenta por Cobrar
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cuentas->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $cuentas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Reactivo de Abono Rápido (Alpine.js) -->
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
                        <h3 class="text-base font-bold text-slate-900" x-text="'Registrar Abono a: ' + cuentaSeleccionada.numero"></h3>
                        <p class="text-xs text-slate-500" x-text="cuentaSeleccionada.cliente"></p>
                    </div>
                    <button type="button" @click="abonoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Saldo Pendiente Banner -->
                <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-900">Saldo Pendiente Actual:</span>
                    <span class="text-base font-black text-indigo-700" x-text="'$' + cuentaSeleccionada.saldo.toLocaleString('es-CO', {minimumFractionDigits: 2})"></span>
                </div>

                <form :action="cuentaSeleccionada.urlAbono" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Monto a Abonar ($ COP) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                            <input type="number" step="0.01" min="0.01" :max="cuentaSeleccionada.saldo" name="monto" :value="cuentaSeleccionada.saldo" required
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
                            Notas / Concepto de Abono <span class="text-slate-400 text-[10px] font-normal">(Opcional)</span>
                        </label>
                        <input type="text" name="notas" placeholder="Ej: Abono parcial acuerdo de pago..."
                            class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="abonoModalOpen = false"
                            class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                            Confirmar & Aplicar Abono
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
