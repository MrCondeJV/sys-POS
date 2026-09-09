@extends('layouts.app')

@section('title', 'Cierre y Arqueo: ' . $caja->nombre)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    montoEsperado: {{ $saldoEsperadoEfectivo }},
    montoContado: {{ $saldoEsperadoEfectivo }},
    calcularDiferencia() {
        let diff = (parseFloat(this.montoContado) || 0) - this.montoEsperado;
        return Math.round(diff * 100) / 100;
    }
}">

    <!-- Encabezado y Navegación -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('cajas.index') }}" class="hover:text-indigo-600 transition">Cajas & Turnos</a>
                <span>/</span>
                <span class="text-slate-800">Arqueo & Corte</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Cierre de Turno: {{ $caja->nombre }}</h1>
            <p class="text-sm text-slate-500">Compara el dinero físico contado contra el saldo calculado por el sistema.</p>
        </div>

        <div>
            <a href="{{ route('cajas.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Cancelar y Volver
            </a>
        </div>
    </div>

    <!-- Tarjetas de Información del Turno -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cajero en Turno</span>
            <div class="text-lg font-black text-slate-900 mt-1">{{ $sesion->cajero->name }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Apertura: {{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Fondo Inicial</span>
            <div class="text-lg font-black text-slate-800 mt-1">${{ number_format($montoInicial, 2) }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Base entregada al inicio</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200 bg-emerald-50/30 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Saldo Esperado en Efectivo</span>
            <div class="text-2xl font-black text-emerald-700 mt-0.5">${{ number_format($saldoEsperadoEfectivo, 2) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Base + Ingresos - Egresos</div>
        </div>
    </div>

    <!-- Formulario de Arqueo y Cierre -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-black text-slate-900">Arqueo Físico de Efectivo</h2>
            <p class="text-xs text-slate-500 mt-0.5">Ingresa el total del dinero en efectivo que contaste físicamente en la caja.</p>
        </div>

        <form action="{{ route('cajas.cierre.store', $caja) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Input Contado -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        Monto Físico Contado *
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-lg font-bold text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" name="monto_cierre_contado" required
                            x-model.number="montoContado"
                            placeholder="0.00"
                            class="w-full pl-9 pr-4 py-3 rounded-2xl border border-slate-200 text-2xl font-black text-slate-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">Cuenta billetes y monedas disponibles en el cajón.</p>
                </div>

                <!-- Comparador Reactivo de Diferencia -->
                <div class="p-5 rounded-2xl border"
                    :class="{
                        'bg-emerald-50 border-emerald-200': calcularDiferencia() === 0,
                        'bg-amber-50 border-amber-200': calcularDiferencia() > 0,
                        'bg-rose-50 border-rose-200': calcularDiferencia() < 0
                    }">
                    <span class="text-xs font-bold uppercase tracking-wider block"
                        :class="{
                            'text-emerald-800': calcularDiferencia() === 0,
                            'text-amber-800': calcularDiferencia() > 0,
                            'text-rose-800': calcularDiferencia() < 0
                        }">
                        Resultado del Arqueo
                    </span>

                    <div class="text-2xl font-black mt-1"
                        :class="{
                            'text-emerald-700': calcularDiferencia() === 0,
                            'text-amber-700': calcularDiferencia() > 0,
                            'text-rose-700': calcularDiferencia() < 0
                        }">
                        <span x-show="calcularDiferencia() === 0">✓ Cuadre Exacto ($0.00)</span>
                        <span x-show="calcularDiferencia() > 0">+ $<span x-text="calcularDiferencia().toFixed(2)"></span> (Sobrante)</span>
                        <span x-show="calcularDiferencia() < 0">- $<span x-text="Math.abs(calcularDiferencia()).toFixed(2)"></span> (Faltante)</span>
                    </div>

                    <p class="text-xs mt-1"
                        :class="{
                            'text-emerald-700': calcularDiferencia() === 0,
                            'text-amber-700': calcularDiferencia() > 0,
                            'text-rose-700': calcularDiferencia() < 0
                        }">
                        <span x-show="calcularDiferencia() === 0">El dinero físico coincide perfectamente con el sistema.</span>
                        <span x-show="calcularDiferencia() > 0">Hay más dinero físico en caja que el registrado por el sistema.</span>
                        <span x-show="calcularDiferencia() < 0">Hay menos dinero físico en caja del esperado. Revisa egresos o ventas no registradas.</span>
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones / Justificación de Cierre</label>
                <textarea name="observaciones" rows="2" placeholder="Notas sobre el corte, justificación de diferencias o novedades..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a href="{{ route('cajas.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700">
                    &larr; Volver sin cerrar
                </a>

                <button type="submit" onclick="return confirm('¿Estás seguro de cerrar este turno de caja? Esta acción finalizará el turno y no podrá deshacerse.')"
                    class="px-6 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-sm font-bold text-white shadow-lg transition">
                    Confirmar Arqueo & Cerrar Caja
                </button>
            </div>
        </form>
    </div>

    <!-- Detalle de Movimientos del Turno -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Movimientos del Turno Actual</h3>
                <p class="text-xs text-slate-500 mt-0.5">Ingresos y retiros aplicados a este corte.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $sesion->movimientos->count() }} movimientos</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left uppercase">Hora</th>
                        <th class="px-6 py-3 text-left uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left uppercase">Concepto</th>
                        <th class="px-6 py-3 text-left uppercase">Medio de Pago</th>
                        <th class="px-6 py-3 text-left uppercase">Usuario</th>
                        <th class="px-6 py-3 text-right uppercase">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($sesion->movimientos as $m)
                        <tr>
                            <td class="px-6 py-3 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('H:i:s') }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->tipo->value === 'INGRESO' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $m->tipo->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-800">{{ $m->concepto }}</td>
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $m->metodo_pago }}</td>
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">{{ $m->usuario->name }}</td>
                            <td class="px-6 py-3 text-right font-black whitespace-nowrap {{ $m->tipo->value === 'INGRESO' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->tipo->value === 'INGRESO' ? '+' : '-' }}${{ number_format($monto = $m->monto, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No se registraron movimientos adicionales en este turno. Solo aplica el fondo inicial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
