@extends('layouts.app')

@section('title', 'Cajas & Turnos')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{
    // Plantillas de rutas generadas por Laravel con placeholder reemplazable
    urlAbrirTemplate: @js(route('cajas.abrir', '__CAJA__')),
    urlMovimientoTemplate: @js(route('cajas.movimiento', '__CAJA__')),

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
            <div class="card overflow-hidden flex flex-col justify-between transition hover:border-border-strong {{ $estaAbierta ? 'border-success-border' : '' }}">
                <div>
                    <!-- Header Card -->
                    <div class="p-5 border-b border-border flex items-start justify-between gap-4 {{ $estaAbierta ? 'bg-success-bg/30' : 'bg-surface-muted' }}">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="h-10 w-10 rounded-lg shrink-0 flex items-center justify-center font-semibold text-sm {{ $estaAbierta ? 'bg-success-bg text-success-text border border-success-border' : 'bg-surface text-text-secondary border border-border' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-text-primary truncate">{{ $caja->nombre }}</h3>
                                <div class="flex items-center space-x-2 text-xs text-text-secondary mt-0.5">
                                    <span class="font-mono bg-surface text-text-primary border border-border px-1.5 py-0.5 rounded font-semibold text-[11px]">{{ $caja->codigo }}</span>
                                    <span>&bull;</span>
                                    <span class="truncate">{{ $caja->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0">
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
                            <div class="bg-surface-muted rounded-lg p-3.5 border border-border space-y-2 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-text-secondary">Cajero en Turno:</span>
                                    <span class="font-semibold text-text-primary">{{ $sesion->cajero->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-text-secondary">Apertura:</span>
                                    <span class="text-text-primary tabular-nums">{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-text-secondary">Fondo Inicial:</span>
                                    <span class="font-semibold text-text-primary tabular-nums">${{ number_format($sesion->monto_apertura, 2) }}</span>
                                </div>
                            </div>

                            <div class="p-4 bg-success-bg/40 rounded-lg border border-success-border flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-semibold text-success-text uppercase tracking-wider block">Saldo Actual en Efectivo</span>
                                    <div class="text-2xl font-semibold text-success-text tracking-tight tabular-nums mt-0.5">
                                        ${{ number_format($saldoActual, 2) }}
                                    </div>
                                </div>
                                <div class="text-right text-xs text-success-text font-semibold tabular-nums">
                                    {{ $sesion->movimientos->count() }} movs.
                                </div>
                            </div>
                        @else
                            <div class="py-6 text-center text-text-muted text-xs">
                                <svg class="h-8 w-8 mx-auto text-text-muted mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <p class="font-medium text-text-secondary">Esta caja se encuentra cerrada.</p>
                                <p class="mt-0.5 text-[11px] text-text-muted">Inicia un nuevo turno para registrar ventas y movimientos.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Acciones -->
                <div class="p-4 border-t border-border bg-surface-muted flex items-center justify-between gap-2">
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
    <div
        x-cloak
        x-show="modalCrearCajaOpen"
        @keydown.escape.window="if (modalCrearCajaOpen) modalCrearCajaOpen = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalCrearCajaOpen" @click="modalCrearCajaOpen = false" class="fixed inset-0 bg-brand-900/60 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalCrearCajaOpen" class="relative bg-surface rounded-xl max-w-md w-full p-6 shadow-xl border border-border z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <h3 class="text-base font-semibold text-text-primary">Registrar Nueva Caja</h3>
                    <button type="button" @click="modalCrearCajaOpen = false" class="text-text-muted hover:text-text-primary transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('cajas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <x-field label="Nombre de la Caja" name="nombre" :required="true">
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Caja Principal 01"
                            class="input @error('nombre') border-danger @enderror">
                    </x-field>

                    <x-field label="Código Único" name="codigo" :required="true">
                        <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" required placeholder="Ej: CAJ-001"
                            class="input font-mono uppercase @error('codigo') border-danger @enderror">
                    </x-field>

                    <x-field label="Sucursal Asignada" name="sucursal_id" :required="true">
                        <select name="sucursal_id" id="sucursal_id" required class="input @error('sucursal_id') border-danger @enderror">
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ old('sucursal_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }} ({{ $s->codigo }})</option>
                            @endforeach
                        </select>
                    </x-field>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-border">
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
    <div
        x-cloak
        x-show="modalAbrirCajaOpen"
        @keydown.escape.window="if (modalAbrirCajaOpen) modalAbrirCajaOpen = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalAbrirCajaOpen" @click="modalAbrirCajaOpen = false" class="fixed inset-0 bg-brand-900/60 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalAbrirCajaOpen" class="relative bg-surface rounded-xl max-w-md w-full p-6 shadow-xl border border-border z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <span class="text-xs font-semibold text-success uppercase tracking-wider block">Apertura de Turno</span>
                        <h3 class="text-base font-semibold text-text-primary mt-0.5" x-text="cajaParaAbrir.nombre"></h3>
                    </div>
                    <button type="button" @click="modalAbrirCajaOpen = false" class="text-text-muted hover:text-text-primary transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="urlAbrirTemplate.replace('__CAJA__', cajaParaAbrir.id)" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="label-base">Monto de Apertura (Fondo Inicial) <span class="text-danger">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-semibold text-text-muted pointer-events-none">$</span>
                            <input type="number" step="0.01" min="0" name="monto_apertura" value="{{ old('monto_apertura') }}" required placeholder="0.00"
                                class="input pl-8 font-semibold tabular-nums @error('monto_apertura') border-danger @enderror">
                        </div>
                        <p class="text-[11px] text-text-secondary mt-1">Efectivo base en caja para cambio/vuelto.</p>
                        @error('monto_apertura')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-field label="Observaciones (Opcional)" name="observaciones">
                        <input type="text" name="observaciones" value="{{ old('observaciones') }}" placeholder="Ej: Billetes de baja denominación incluidos"
                            class="input text-xs @error('observaciones') border-danger @enderror">
                    </x-field>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-border">
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
    <div
        x-cloak
        x-show="modalMovimientoOpen"
        @keydown.escape.window="if (modalMovimientoOpen) modalMovimientoOpen = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalMovimientoOpen" @click="modalMovimientoOpen = false" class="fixed inset-0 bg-brand-900/60 backdrop-blur-xs transition-opacity"></div>
            <div x-show="modalMovimientoOpen" class="relative bg-surface rounded-xl max-w-md w-full p-6 shadow-xl border border-border z-10 space-y-5">
                <div class="flex items-center justify-between border-b border-border pb-3">
                    <div>
                        <span class="text-xs font-semibold text-text-secondary uppercase tracking-wider block">Movimiento de Caja</span>
                        <h3 class="text-base font-semibold text-text-primary mt-0.5" x-text="cajaParaMovimiento.nombre"></h3>
                    </div>
                    <button type="button" @click="modalMovimientoOpen = false" class="text-text-muted hover:text-text-primary transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="urlMovimientoTemplate.replace('__CAJA__', cajaParaMovimiento.id)" method="POST" class="space-y-4">
                    @csrf
                    <!-- Selector Tipo -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-surface-muted border border-border rounded-lg">
                        <button type="button" @click="tipoMovimiento = 'INGRESO'"
                            :class="tipoMovimiento === 'INGRESO' ? 'bg-surface text-success-text shadow-xs font-semibold border border-success-border' : 'text-text-secondary hover:text-text-primary font-medium'"
                            class="py-1.5 text-xs rounded-md transition text-center">
                            &plus; Ingreso
                        </button>
                        <button type="button" @click="tipoMovimiento = 'EGRESO'"
                            :class="tipoMovimiento === 'EGRESO' ? 'bg-surface text-danger-text shadow-xs font-semibold border border-danger-border' : 'text-text-secondary hover:text-text-primary font-medium'"
                            class="py-1.5 text-xs rounded-md transition text-center">
                            &minus; Retiro / Egreso
                        </button>
                    </div>
                    <input type="hidden" name="tipo" :value="tipoMovimiento">

                    <div>
                        <label class="label-base">Monto <span class="text-danger">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-semibold text-text-muted pointer-events-none">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" required placeholder="0.00"
                                class="input pl-8 font-semibold tabular-nums @error('monto') border-danger @enderror">
                        </div>
                        <template x-if="tipoMovimiento === 'EGRESO'">
                            <p class="text-[11px] text-danger mt-1 tabular-nums">Efectivo disponible: $<span x-text="cajaParaMovimiento.saldoEfectivo.toFixed(2)"></span></p>
                        </template>
                        @error('monto')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-field label="Concepto / Motivo" name="concepto" :required="true">
                        <input type="text" name="concepto" value="{{ old('concepto') }}" required placeholder="Ej: Pago de flete / Retiro para depósito"
                            class="input text-xs @error('concepto') border-danger @enderror">
                    </x-field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-field label="Medio de Pago" name="metodo_pago">
                            <select name="metodo_pago" class="input text-xs @error('metodo_pago') border-danger @enderror">
                                <option value="EFECTIVO" {{ old('metodo_pago') === 'EFECTIVO' ? 'selected' : '' }}>Efectivo</option>
                                <option value="TRANSFERENCIA" {{ old('metodo_pago') === 'TRANSFERENCIA' ? 'selected' : '' }}>Transferencia</option>
                                <option value="TARJETA_DEBITO" {{ old('metodo_pago') === 'TARJETA_DEBITO' ? 'selected' : '' }}>Tarjeta Débito</option>
                                <option value="TARJETA_CREDITO" {{ old('metodo_pago') === 'TARJETA_CREDITO' ? 'selected' : '' }}>Tarjeta Crédito</option>
                            </select>
                        </x-field>
                        <x-field label="N° Comprobante" name="comprobante">
                            <input type="text" name="comprobante" value="{{ old('comprobante') }}" placeholder="Opcional"
                                class="input text-xs @error('comprobante') border-danger @enderror">
                        </x-field>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-4 border-t border-border">
                        <x-button variant="outline" size="sm" type="button" @click="modalMovimientoOpen = false">
                            Cancelar
                        </x-button>
                        <button type="submit"
                            :class="tipoMovimiento === 'INGRESO' ? 'btn btn-primary' : 'btn btn-danger'"
                            class="h-8 px-3 text-xs">
                            Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
