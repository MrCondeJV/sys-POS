@extends('layouts.app')

@section('title', 'Cierre y Arqueo: ' . $caja->nombre)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    montoEsperado: {{ $saldoEsperadoEfectivo }},
    montoContado: {{ $saldoEsperadoEfectivo }},
    confirmCierreOpen: false,
    calcularDiferencia() {
        let diff = (parseFloat(this.montoContado) || 0) - this.montoEsperado;
        return Math.round(diff * 100) / 100;
    }
}">

    <!-- Encabezado y Navegación -->
    <x-page-header
        :title="'Cierre de Turno: ' . $caja->nombre"
        subtitle="Compara el dinero físico contado contra el saldo calculado por el sistema para cerrar el turno."
        :breadcrumbs="[
            ['label' => 'Cajas & Turnos', 'url' => route('cajas.index')],
            ['label' => 'Arqueo & Corte']
        ]"
    >
        <x-slot:actions>
            <x-button variant="outline" size="sm" href="{{ route('cajas.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Cancelar y Volver
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Tarjetas de Información del Turno -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5">
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">Cajero en Turno</span>
            <div class="text-base font-semibold text-text-primary mt-1">{{ $sesion->cajero->name }}</div>
            <div class="text-[11px] text-text-secondary mt-0.5 tabular-nums">Apertura: {{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</div>
        </div>

        <div class="card p-5">
            <span class="text-xs font-semibold text-text-muted uppercase tracking-wider block">Fondo Inicial</span>
            <div class="text-base font-semibold text-text-primary mt-1 tabular-nums">${{ number_format($montoInicial, 2) }}</div>
            <div class="text-[11px] text-text-secondary mt-0.5">Base entregada al inicio</div>
        </div>

        <div class="card p-5 bg-success-bg/30 border-success-border">
            <span class="text-xs font-semibold text-success-text uppercase tracking-wider block">Saldo Esperado en Efectivo</span>
            <div class="text-2xl font-semibold text-success-text mt-0.5 tabular-nums">${{ number_format($saldoEsperadoEfectivo, 2) }}</div>
            <div class="text-[11px] text-success-text font-medium mt-0.5">Base + Ingresos &minus; Egresos</div>
        </div>
    </div>

    <!-- Formulario de Arqueo y Cierre -->
    <div class="card p-6 space-y-6">
        <div class="border-b border-border pb-4">
            <h2 class="text-base font-semibold text-text-primary">Arqueo Físico de Efectivo</h2>
            <p class="text-xs text-text-secondary mt-0.5">Ingresa el total del dinero en efectivo que contaste físicamente en el cajón.</p>
        </div>

        <form id="formCierreCaja" action="{{ route('cajas.cierre.store', $caja) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <!-- Input Contado -->
                <div>
                    <label class="label-base uppercase">
                        Monto Físico Contado <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-lg font-semibold text-text-muted pointer-events-none">$</span>
                        <input type="number" step="0.01" min="0" name="monto_cierre_contado" required
                            x-model.number="montoContado"
                            placeholder="0.00"
                            class="input pl-8 pr-4 py-2.5 text-xl font-semibold text-text-primary tabular-nums @error('monto_cierre_contado') border-danger @enderror">
                    </div>
                    <p class="text-[11px] text-text-secondary mt-1.5">Cuenta billetes y monedas disponibles en el cajón.</p>
                    @error('monto_cierre_contado')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Comparador Reactivo de Diferencia -->
                <div class="p-4 rounded-lg border"
                    :class="{
                        'bg-success-bg/60 border-success-border text-success-text': calcularDiferencia() === 0,
                        'bg-warning-bg/60 border-warning-border text-warning-text': calcularDiferencia() > 0,
                        'bg-danger-bg/60 border-danger-border text-danger-text': calcularDiferencia() < 0
                    }">
                    <span class="text-xs font-semibold uppercase tracking-wider block">
                        Resultado del Arqueo
                    </span>

                    <div class="text-xl font-semibold mt-1 tabular-nums">
                        <span x-show="calcularDiferencia() === 0">Cuadre Exacto ($0.00)</span>
                        <span x-show="calcularDiferencia() > 0">+ $<span x-text="calcularDiferencia().toFixed(2)"></span> (Sobrante)</span>
                        <span x-show="calcularDiferencia() < 0">&minus; $<span x-text="Math.abs(calcularDiferencia()).toFixed(2)"></span> (Faltante)</span>
                    </div>

                    <p class="text-xs mt-1 text-text-secondary">
                        <span x-show="calcularDiferencia() === 0">El dinero físico coincide con el cálculo del sistema.</span>
                        <span x-show="calcularDiferencia() > 0">Hay más dinero físico en caja del registrado por el sistema.</span>
                        <span x-show="calcularDiferencia() < 0">Hay menos dinero físico en caja del esperado. Revisa egresos o ventas pendientes.</span>
                    </p>
                </div>
            </div>

            <x-field label="Observaciones / Justificación de Cierre" name="observaciones">
                <textarea name="observaciones" rows="2" placeholder="Notas sobre el corte, justificación de diferencias o novedades..."
                    class="input text-xs @error('observaciones') border-danger @enderror">{{ old('observaciones') }}</textarea>
            </x-field>

            <div class="flex items-center justify-between pt-4 border-t border-border">
                <a href="{{ route('cajas.index') }}" class="text-xs font-semibold text-text-secondary hover:text-text-primary">
                    &larr; Volver sin cerrar
                </a>

                <x-button type="button" variant="primary" @click="confirmCierreOpen = true">
                    Confirmar Arqueo & Cerrar Caja
                </x-button>
            </div>
        </form>
    </div>

    <!-- Modal Confirmación de Cierre -->
    <x-modal-confirm
        show="confirmCierreOpen"
        title="Confirmar Cierre de Turno"
        message="¿Estás seguro de cerrar este turno de caja? Esta acción finalizará el turno actual, fijará los saldos y generará el reporte Z de corte."
        consequence="Se registrará el monto contado, la diferencia de arqueo y la fecha de cierre de la sesión, redirigiendo al comprobante Z imprimible."
        confirm-text="Sí, cerrar turno"
        cancel-text="Revisar de nuevo"
        confirm-variant="danger"
        @confirm="document.getElementById('formCierreCaja').submit()"
        @cancel="confirmCierreOpen = false"
    />

    <!-- Detalle de Movimientos del Turno -->
    <div class="space-y-2">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-sm font-semibold text-text-primary">Movimientos del Turno Actual</h3>
            <span class="text-xs font-semibold text-text-secondary tabular-nums">{{ $sesion->movimientos->count() }} movimientos</span>
        </div>

        <x-table>
            <x-slot:thead>
                <tr>
                    <th class="px-5 py-3 text-left uppercase">Hora</th>
                    <th class="px-5 py-3 text-left uppercase">Tipo</th>
                    <th class="px-5 py-3 text-left uppercase">Concepto</th>
                    <th class="px-5 py-3 text-left uppercase">Medio de Pago</th>
                    <th class="px-5 py-3 text-left uppercase">Usuario</th>
                    <th class="px-5 py-3 text-right uppercase">Monto</th>
                </tr>
            </x-slot:thead>

            @forelse($sesion->movimientos as $m)
                <tr class="hover:bg-surface-muted transition">
                    <td class="px-5 py-3 text-text-secondary tabular-nums whitespace-nowrap">{{ $m->created_at->format('H:i:s') }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <x-badge :status="$m->tipo->value === 'INGRESO' ? 'success' : 'danger'" size="sm">
                            {{ $m->tipo->label() }}
                        </x-badge>
                    </td>
                    <td class="px-5 py-3 text-text-primary">{{ $m->concepto }}</td>
                    <td class="px-5 py-3 text-text-secondary whitespace-nowrap">{{ $m->metodo_pago }}</td>
                    <td class="px-5 py-3 text-text-secondary whitespace-nowrap">{{ $m->usuario->name }}</td>
                    <td class="px-5 py-3 text-right font-semibold whitespace-nowrap tabular-nums {{ $m->tipo->value === 'INGRESO' ? 'text-success' : 'text-danger' }}">
                        {{ $m->tipo->value === 'INGRESO' ? '+' : '-' }}${{ number_format($m->monto, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-text-muted text-xs">
                        No se registraron movimientos adicionales en este turno. Solo aplica el fondo inicial.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

</div>
@endsection
