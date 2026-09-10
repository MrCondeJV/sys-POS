@extends('layouts.app')

@section('title', 'Cajas & Turnos')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6" x-data="{
    // Modal Crear Caja
    modalCrearCajaOpen: false,

    // Modal Abrir Caja
    modalAbrirCajaOpen: false,
    cajaParaAbrir: { id: null, nombre: '', codigo: '' },
    abrirModalApertura(caja) {
        this.cajaParaAbrir = caja;
        this.modalAbrirCajaOpen = true;
    },

    // Modal Movimiento (Ingreso/Egreso)
    modalMovimientoOpen: false,
    cajaParaMovimiento: { id: null, nombre: '', saldoEfectivo: 0 },
    tipoMovimiento: 'INGRESO',
    abrirModalMovimiento(caja, saldo) {
        this.cajaParaMovimiento = caja;
        this.cajaParaMovimiento.saldoEfectivo = saldo;
        this.tipoMovimiento = 'INGRESO';
        this.modalMovimientoOpen = true;
    }
}">

    <!-- Encabezado de la Sección -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Operaciones</span>
                <span>/</span>
                <span class="text-slate-800">Cajas & Turnos</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestión de Cajas & Arqueos</h1>
            <p class="text-sm text-slate-500">Control de fondos iniciales, ingresos, retiros de efectivo y cortes de turno.</p>
        </div>

        @can('create', App\Models\Caja::class)
        <div>
            <button @click="modalCrearCajaOpen = true" type="button"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Caja
            </button>
        </div>
        @endcan
    </div>

    <!-- KPIs del Módulo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/70 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cajas Registradas</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalCajas }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">En sucursales activas</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/70 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Turnos Abiertos</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $cajasAbiertas }}</div>
                <div class="text-[11px] text-emerald-700 mt-0.5">Operando en este momento</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/70 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cajas Cerradas</span>
                <div class="text-2xl font-black text-slate-600 mt-1">{{ $cajasCerradas }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Listas para apertura</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/70 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Total Efectivo en Cajas</span>
                <div class="text-2xl font-black text-indigo-600 mt-1">${{ number_format($totalDineroEnCaja, 2) }}</div>
                <div class="text-[11px] text-indigo-700 mt-0.5">Suma de saldos disponibles</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Lista / Grilla de Cajas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($cajas as $caja)
            @php
                $sesion = $caja->sesionActual;
                $estaAbierta = $sesion !== null;
                $saldoActual = $estaAbierta ? $sesion->calcularSaldoEsperadoEfectivo() : 0;
            @endphp
            <div class="bg-white rounded-3xl border {{ $estaAbierta ? 'border-emerald-200 shadow-sm' : 'border-slate-200' }} overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Header Card -->
                    <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-4 {{ $estaAbierta ? 'bg-emerald-50/40' : 'bg-slate-50/40' }}">
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-black text-sm {{ $estaAbierta ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 leading-snug">{{ $caja->nombre }}</h3>
                                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                                    <span class="font-mono bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-semibold text-[10px]">{{ $caja->codigo }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $caja->sucursal->nombre }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($estaAbierta)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    ABIERTA
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                    CERRADA
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Contenido del Card -->
                    <div class="p-5 space-y-4">
                        @if($estaAbierta)
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-2">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500">Cajero en Turno:</span>
                                    <span class="font-bold text-slate-800">{{ $sesion->cajero->name }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500">Apertura:</span>
                                    <span class="text-slate-700">{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500">Fondo Inicial:</span>
                                    <span class="font-bold text-slate-800">${{ number_format($sesion->monto_apertura, 2) }}</span>
                                </div>
                            </div>

                            <div class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Saldo Actual en Efectivo</span>
                                    <div class="text-2xl font-black text-emerald-700 mt-0.5">
                                        ${{ number_format($saldoActual, 2) }}
                                    </div>
                                </div>
                                <div class="text-right text-[11px] text-emerald-700 font-semibold">
                                    {{ $sesion->movimientos->count() }} movs.
                                </div>
                            </div>
                        @else
                            <div class="py-6 text-center text-slate-400 text-xs">
                                <p>Esta caja se encuentra cerrada.</p>
                                <p class="mt-1 text-[11px] text-slate-400">Inicia un nuevo turno para registrar ventas y movimientos de dinero.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="p-5 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between gap-2">
                    <a href="{{ route('cajas.show', $caja) }}"
                        class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        Historial
                    </a>

                    <div class="flex items-center space-x-2">
                        @if($estaAbierta)
                            @can('movimiento', $caja)
                            <button @click="abrirModalMovimiento({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}' }, {{ $saldoActual }})" type="button"
                                class="px-3 py-2 rounded-xl border border-indigo-200 bg-indigo-50 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition">
                                Movimiento &plusmn;
                            </button>
                            @endcan

                            @can('cerrar', $caja)
                            <a href="{{ route('cajas.cierre', $caja) }}"
                                class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-xs font-bold text-white transition shadow-sm">
                                Corte / Arqueo &rarr;
                            </a>
                            @endcan
                        @else
                            @can('abrir', $caja)
                            <button @click="abrirModalApertura({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}', codigo: '{{ $caja->codigo }}' })" type="button"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition shadow-sm flex items-center">
                                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Abrir Turno
                            </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-12 rounded-3xl border border-slate-200 text-center">
                <svg class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-base font-bold text-slate-800">No hay cajas registradas</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Crea una caja física o registradora para que tus cajeros puedan abrir turnos y gestionar ventas.</p>
                @can('create', App\Models\Caja::class)
                <button @click="modalCrearCajaOpen = true" type="button" class="mt-4 inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-sm transition">
                    Crear la primera caja
                </button>
                @endcan
            </div>
        @endforelse
    </div>

    <!-- MODAL 1: Crear Nueva Caja -->
    <div x-cloak x-show="modalCrearCajaOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalCrearCajaOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <div x-show="modalCrearCajaOpen" @click.away="modalCrearCajaOpen = false"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h3 class="text-lg font-black text-slate-900">Registrar Nueva Caja</h3>
                    <button @click="modalCrearCajaOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('cajas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre de la Caja *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Caja Principal 01"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Código Único *</label>
                        <input type="text" name="codigo" required placeholder="Ej: CAJ-001"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono uppercase focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Sucursal Asignada *</label>
                        <select name="sucursal_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }} ({{ $s->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button @click="modalCrearCajaOpen = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white shadow-sm">
                            Guardar Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Abrir Caja (Turno) -->
    <div x-cloak x-show="modalAbrirCajaOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalAbrirCajaOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <div x-show="modalAbrirCajaOpen" @click.away="modalAbrirCajaOpen = false"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Apertura de Turno</span>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5" x-text="cajaParaAbrir.nombre"></h3>
                    </div>
                    <button @click="modalAbrirCajaOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + cajaParaAbrir.id + '/abrir'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monto de Apertura (Fondo Inicial) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0" name="monto_apertura" required placeholder="0.00"
                                class="w-full pl-8 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-base font-bold text-slate-900 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Efectivo inicial en caja para cambio/vuelto.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones / Notas (Opcional)</label>
                        <input type="text" name="observaciones" placeholder="Ej: Billetes de baja denominación incluidos"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button @click="modalAbrirCajaOpen = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white shadow-sm">
                            Confirmar Apertura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Registrar Movimiento (Ingreso / Retiro) -->
    <div x-cloak x-show="modalMovimientoOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalMovimientoOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <div x-show="modalMovimientoOpen" @click.away="modalMovimientoOpen = false"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Movimiento de Caja</span>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5" x-text="cajaParaMovimiento.nombre"></h3>
                    </div>
                    <button @click="modalMovimientoOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + cajaParaMovimiento.id + '/movimiento'" method="POST" class="space-y-4">
                    @csrf
                    <!-- Selector Tipo -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <button type="button" @click="tipoMovimiento = 'INGRESO'"
                            :class="tipoMovimiento === 'INGRESO' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500'"
                            class="py-2 text-xs font-bold rounded-lg transition text-center">
                            &plus; Ingreso
                        </button>
                        <button type="button" @click="tipoMovimiento = 'EGRESO'"
                            :class="tipoMovimiento === 'EGRESO' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-500'"
                            class="py-2 text-xs font-bold rounded-lg transition text-center">
                            &minus; Retiro / Egreso
                        </button>
                    </div>
                    <input type="hidden" name="tipo" :value="tipoMovimiento">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0.00"
                                class="w-full pl-8 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-base font-bold text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                        </div>
                        <template x-if="tipoMovimiento === 'EGRESO'">
                            <p class="text-[11px] text-rose-600 mt-1">Efectivo disponible: $<span x-text="cajaParaMovimiento.saldoEfectivo.toFixed(2)"></span></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Concepto / Motivo *</label>
                        <input type="text" name="concepto" required placeholder="Ej: Pago de flete / Retiro para depósito"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Medio de Pago</label>
                            <select name="metodo_pago" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="TARJETA_DEBITO">Tarjeta Débito</option>
                                <option value="TARJETA_CREDITO">Tarjeta Crédito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">N° Comprobante</label>
                            <input type="text" name="comprobante" placeholder="Opcional"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button @click="modalMovimientoOpen = false" type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit"
                            :class="tipoMovimiento === 'INGRESO' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm transition">
                            Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
