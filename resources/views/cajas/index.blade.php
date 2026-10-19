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
    <x-page-header
        title="Gestión de Cajas & Arqueos"
        subtitle="Control de fondos iniciales, ingresos, retiros de efectivo y cortes de turno."
        :breadcrumbs="[
            ['label' => 'Operaciones'],
            ['label' => 'Cajas & Turnos']
        ]"
    >
        <x-slot:actions>
            @can('create', App\Models\Caja::class)
                <x-button variant="primary" @click="modalCrearCajaOpen = true">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Caja
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <!-- KPIs del Módulo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Cajas Registradas"
            :value="$totalCajas"
            subtext="En sucursales activas"
            icon="cash"
        />

        <x-kpi-card
            label="Turnos Abiertos"
            :value="$cajasAbiertas"
            status="success"
            subtext="Operando en este momento"
            icon="shield"
        />

        <x-kpi-card
            label="Cajas Cerradas"
            :value="$cajasCerradas"
            status="neutral"
            subtext="Listas para apertura"
            icon="calendar"
        />

        <x-kpi-card
            label="Efectivo en Cajas"
            :value="'$' . number_format($totalDineroEnCaja, 2)"
            status="info"
            subtext="Suma de saldos disponibles"
            icon="coins"
        />
    </div>

    <!-- Grilla de Cajas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($cajas as $caja)
            @php
                $sesion = $caja->sesionActual;
                $estaAbierta = $sesion !== null;
                $saldoActual = $estaAbierta ? $sesion->calcularSaldoEsperadoEfectivo() : 0;
            @endphp
            <div class="bg-white rounded-xl border {{ $estaAbierta ? 'border-emerald-200/90 shadow-xs' : 'border-slate-200/80 shadow-xs' }} overflow-hidden flex flex-col justify-between transition hover:border-slate-300">
                <div>
                    <!-- Header Card -->
                    <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-4 {{ $estaAbierta ? 'bg-emerald-50/20' : 'bg-slate-50/40' }}">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="h-10 w-10 rounded-lg flex-shrink-0 flex items-center justify-center font-bold text-sm {{ $estaAbierta ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $caja->nombre }}</h3>
                                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                                    <span class="font-mono bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-semibold text-[11px]">{{ $caja->codigo }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate">{{ $caja->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            @if($estaAbierta)
                                <x-badge status="success" :dot="true">ABIERTA</x-badge>
                            @else
                                <x-badge status="neutral">CERRADA</x-badge>
                            @endif
                        </div>
                    </div>

                    <!-- Contenido del Card -->
                    <div class="p-5 space-y-4">
                        @if($estaAbierta)
                            <div class="bg-slate-50/80 rounded-lg p-3.5 border border-slate-100 space-y-2 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Cajero en Turno:</span>
                                    <span class="font-semibold text-slate-900">{{ $sesion->cajero->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Apertura:</span>
                                    <span class="text-slate-700 tabular-nums">{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Fondo Inicial:</span>
                                    <span class="font-semibold text-slate-900 tabular-nums">${{ number_format($sesion->monto_apertura, 2) }}</span>
                                </div>
                            </div>

                            <div class="p-4 bg-emerald-50/40 rounded-lg border border-emerald-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block">Saldo Actual en Efectivo</span>
                                    <div class="text-2xl font-bold text-emerald-800 tracking-tight tabular-nums mt-0.5">
                                        ${{ number_format($saldoActual, 2) }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-emerald-700 font-semibold tabular-nums">
                                    {{ $sesion->movimientos->count() }} movs.
                                </div>
                            </div>
                        @else
                            <div class="py-6 text-center text-slate-400 text-xs">
                                <svg class="h-8 w-8 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <p class="font-medium text-slate-600">Esta caja se encuentra cerrada.</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">Inicia un nuevo turno para registrar ventas y movimientos.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
                    <x-button variant="outline" size="sm" href="{{ route('cajas.show', $caja) }}">
                        Historial
                    </x-button>

                    <div class="flex items-center space-x-2">
                        @if($estaAbierta)
                            @can('movimiento', $caja)
                                <x-button variant="secondary" size="sm" @click="abrirModalMovimiento({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}' }, {{ $saldoActual }})">
                                    Movimiento &plusmn;
                                </x-button>
                            @endcan

                            @can('cerrar', $caja)
                                <x-button variant="primary" size="sm" href="{{ route('cajas.cierre', $caja) }}">
                                    Corte / Arqueo &rarr;
                                </x-button>
                            @endcan
                        @else
                            @can('abrir', $caja)
                                <x-button variant="primary" size="sm" @click="abrirModalApertura({ id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}', codigo: '{{ $caja->codigo }}' })">
                                    <svg class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    Abrir Turno
                                </x-button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-empty-state
                    title="No hay cajas registradas"
                    description="Crea una caja registradora o punto de cobro para que tus cajeros puedan abrir turnos y gestionar ventas."
                >
                    <x-slot:action>
                        @can('create', App\Models\Caja::class)
                            <x-button variant="primary" @click="modalCrearCajaOpen = true">
                                Crear la primera caja
                            </x-button>
                        @endcan
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>

    <!-- MODAL 1: Crear Nueva Caja -->
    <div x-cloak x-show="modalCrearCajaOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalCrearCajaOpen" @click="modalCrearCajaOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalCrearCajaOpen" class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Registrar Nueva Caja</h3>
                    <button @click="modalCrearCajaOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('cajas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-field label="Nombre de la Caja" name="nombre" :required="true">
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej: Caja Principal 01"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </x-field>

                    <x-field label="Código Único" name="codigo" :required="true">
                        <input type="text" name="codigo" id="codigo" required placeholder="Ej: CAJ-001"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono uppercase focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </x-field>

                    <x-field label="Sucursal Asignada" name="sucursal_id" :required="true">
                        <select name="sucursal_id" id="sucursal_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }} ({{ $s->codigo }})</option>
                            @endforeach
                        </select>
                    </x-field>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                        <x-button variant="outline" size="sm" type="button" @click="modalCrearCajaOpen = false">
                            Cancelar
                        </x-button>
                        <x-button variant="primary" size="sm" type="submit">
                            Guardar Caja
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Abrir Caja (Turno) -->
    <div x-cloak x-show="modalAbrirCajaOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalAbrirCajaOpen" @click="modalAbrirCajaOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalAbrirCajaOpen" class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Apertura de Turno</span>
                        <h3 class="text-base font-bold text-slate-900 mt-0.5" x-text="cajaParaAbrir.nombre"></h3>
                    </div>
                    <button @click="modalAbrirCajaOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + cajaParaAbrir.id + '/abrir'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Monto de Apertura (Fondo Inicial) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0" name="monto_apertura" required placeholder="0.00"
                                class="w-full pl-7 pr-3 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-900 tabular-nums focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Efectivo base en caja para cambio/vuelto.</p>
                    </div>

                    <x-field label="Observaciones (Opcional)" name="observaciones">
                        <input type="text" name="observaciones" placeholder="Ej: Billetes de baja denominación incluidos"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </x-field>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                        <x-button variant="outline" size="sm" type="button" @click="modalAbrirCajaOpen = false">
                            Cancelar
                        </x-button>
                        <x-button variant="primary" size="sm" type="submit">
                            Confirmar Apertura
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: Registrar Movimiento (Ingreso / Retiro) -->
    <div x-cloak x-show="modalMovimientoOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalMovimientoOpen" @click="modalMovimientoOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalMovimientoOpen" class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Movimiento de Caja</span>
                        <h3 class="text-base font-bold text-slate-900 mt-0.5" x-text="cajaParaMovimiento.nombre"></h3>
                    </div>
                    <button @click="modalMovimientoOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + cajaParaMovimiento.id + '/movimiento'" method="POST" class="space-y-4">
                    @csrf
                    <!-- Selector Tipo -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-lg">
                        <button type="button" @click="tipoMovimiento = 'INGRESO'"
                            :class="tipoMovimiento === 'INGRESO' ? 'bg-white text-emerald-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="py-1.5 text-xs rounded-md transition text-center">
                            &plus; Ingreso
                        </button>
                        <button type="button" @click="tipoMovimiento = 'EGRESO'"
                            :class="tipoMovimiento === 'EGRESO' ? 'bg-white text-rose-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="py-1.5 text-xs rounded-md transition text-center">
                            &minus; Retiro / Egreso
                        </button>
                    </div>
                    <input type="hidden" name="tipo" :value="tipoMovimiento">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0.00"
                                class="w-full pl-7 pr-3 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-900 tabular-nums focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </div>
                        <template x-if="tipoMovimiento === 'EGRESO'">
                            <p class="text-[11px] text-rose-600 mt-1">Efectivo disponible: $<span x-text="cajaParaMovimiento.saldoEfectivo.toFixed(2)"></span></p>
                        </template>
                    </div>

                    <x-field label="Concepto / Motivo" name="concepto" :required="true">
                        <input type="text" name="concepto" required placeholder="Ej: Pago de flete / Retiro para depósito"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </x-field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-field label="Medio de Pago" name="metodo_pago">
                            <select name="metodo_pago" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="TARJETA_DEBITO">Tarjeta Débito</option>
                                <option value="TARJETA_CREDITO">Tarjeta Crédito</option>
                            </select>
                        </x-field>
                        <x-field label="N° Comprobante" name="comprobante">
                            <input type="text" name="comprobante" placeholder="Opcional"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        </x-field>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                        <x-button variant="outline" size="sm" type="button" @click="modalMovimientoOpen = false">
                            Cancelar
                        </x-button>
                        <button type="submit"
                            :class="tipoMovimiento === 'INGRESO' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'"
                            class="inline-flex items-center justify-center font-medium rounded-lg px-3 py-1.5 text-xs shadow-xs transition">
                            Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
