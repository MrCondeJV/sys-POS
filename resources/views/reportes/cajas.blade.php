@extends('layouts.app')

@section('title', 'Reporte de Cajas')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Reporte de Turnos y Arqueos de Caja"
        subtitle="Auditoría de aperturas, cierres de turno, control de efectivo y diferencias de arqueo."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Cajas']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.cajas.imprimir', request()->all()) }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.cajas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="label-base">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="input text-xs">
            </div>
            <div>
                <label class="label-base">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="input text-xs">
            </div>
            <div>
                <label class="label-base">Caja</label>
                <select name="caja_id" class="input text-xs">
                    <option value="">Todas</option>
                    @foreach($cajas as $c)
                        <option value="{{ $c->id }}" {{ $cajaId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Estado</label>
                <select name="estado" class="input text-xs">
                    <option value="">Todos</option>
                    <option value="ABIERTA" {{ $estado === 'ABIERTA' ? 'selected' : '' }}>Abierta</option>
                    <option value="CERRADA" {{ $estado === 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.cajas') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Ventas en Efectivo"
            value="${{ number_format($totalVentasEfectivo, 2) }}"
            context="Cobrado en ventanilla"
            variant="success"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Ingresos Extra"
            value="${{ number_format($totalIngresos, 2) }}"
            context="Inyecciones a caja"
            icon="check"
        />

        <x-kpi-card
            label="Egresos / Gastos"
            value="${{ number_format($totalEgresos, 2) }}"
            context="Salidas de dinero"
            variant="danger"
            icon="alert"
        />

        <x-kpi-card
            label="Diferencias Arqueo"
            value="${{ number_format($totalDiferencias, 2) }}"
            context="Sobrantes (+) / Faltantes (-)"
            variant="{{ $totalDiferencias < 0 ? 'danger' : 'default' }}"
            icon="credit-card"
        />
    </div>

    <!-- Tabla -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Caja / Sede</th>
                        <th>Cajero</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th class="text-right">Inicial</th>
                        <th class="text-right">Esperado</th>
                        <th class="text-right">Contado</th>
                        <th class="text-right">Diferencia</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesiones as $s)
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-900 text-xs">{{ $s->caja?->nombre }}</div>
                            <div class="text-[11px] text-slate-400">{{ $s->sucursal?->nombre }}</div>
                        </td>
                        <td class="text-xs text-slate-700">{{ $s->cajero?->name }}</td>
                        <td class="text-xs text-slate-500 font-mono">{{ $s->fecha_apertura->format('d/m/Y H:i') }}</td>
                        <td class="text-xs text-slate-500 font-mono">{{ $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : '--' }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($s->monto_apertura, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($s->monto_cierre_esperado ?? 0, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($s->monto_cierre_contado ?? 0, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs tabular-nums {{ (float)$s->diferencia < 0 ? 'text-danger-text' : ((float)$s->diferencia > 0 ? 'text-success-text' : 'text-slate-700') }}">
                            ${{ number_format($s->diferencia ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $s->estado->value === 'ABIERTA' ? 'badge-warning' : 'badge-neutral' }}">
                                {{ $s->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="empty-state-title">No hay sesiones de caja</p>
                                <p class="empty-state-desc">No se encontraron turnos de caja para los filtros seleccionados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $sesiones->links() }}
        </div>
    </div>
</div>
@endsection
