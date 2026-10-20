@extends('layouts.app')

@section('title', 'Caja: ' . $caja->nombre)

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Encabezado y Navegación -->
    <x-page-header
        :title="$caja->nombre"
        subtitle="Sucursal: {{ $caja->sucursal?->nombre ?? 'Sin sucursal' }} • Registro cronológico de aperturas, cierres y auditorías de arqueo."
        :breadcrumbs="[
            ['label' => 'Cajas & Turnos', 'url' => route('cajas.index')],
            ['label' => 'Historial de Turnos']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs px-2 py-1 bg-surface-muted text-text-primary rounded-md font-semibold border border-border">{{ $caja->codigo }}</span>
                @if($caja->estaAbierta())
                    <x-badge status="success" :dot="true">TURNO ABIERTO</x-badge>
                @else
                    <x-badge status="neutral">CERRADA</x-badge>
                @endif
                <x-button variant="outline" size="sm" href="{{ route('cajas.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Cajas
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Tabla de Turnos Históricos -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">Apertura / Cajero</th>
                <th class="px-5 py-3 text-left uppercase">Cierre / Auditor</th>
                <th class="px-5 py-3 text-right uppercase">Fondo Inicial</th>
                <th class="px-5 py-3 text-right uppercase">Total Ingresos</th>
                <th class="px-5 py-3 text-right uppercase">Total Egresos</th>
                <th class="px-5 py-3 text-right uppercase">Esperado</th>
                <th class="px-5 py-3 text-right uppercase">Contado</th>
                <th class="px-5 py-3 text-center uppercase">Diferencia</th>
                <th class="px-5 py-3 text-center uppercase">Estado</th>
                <th class="px-5 py-3 text-right uppercase">Comprobante</th>
            </tr>
        </x-slot:thead>

        @forelse($sesiones as $s)
            <tr class="hover:bg-surface-muted transition">
                <td class="px-5 py-3">
                    <div class="font-semibold text-text-primary">{{ $s->cajero->name }}</div>
                    <div class="text-text-secondary text-[11px] tabular-nums">{{ $s->fecha_apertura->format('d/m/Y H:i') }}</div>
                </td>
                <td class="px-5 py-3">
                    @if($s->fecha_cierre)
                        <div class="font-semibold text-text-primary">{{ $s->cajeroCierre?->name ?? 'Sistema' }}</div>
                        <div class="text-text-secondary text-[11px] tabular-nums">{{ $s->fecha_cierre->format('d/m/Y H:i') }}</div>
                    @else
                        <span class="text-text-muted italic text-xs">En operación...</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-medium text-text-secondary tabular-nums">${{ number_format($s->monto_apertura, 2) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-success tabular-nums">${{ number_format($s->total_ingresos, 2) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-danger tabular-nums">${{ number_format($s->total_egresos, 2) }}</td>
                <td class="px-5 py-3 text-right font-semibold text-text-primary tabular-nums">
                    @if($s->monto_cierre_esperado !== null)
                        ${{ number_format($s->monto_cierre_esperado, 2) }}
                    @else
                        ${{ number_format($s->calcularSaldoEsperadoEfectivo(), 2) }}
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-semibold text-text-primary tabular-nums">
                    @if($s->monto_cierre_contado !== null)
                        ${{ number_format($s->monto_cierre_contado, 2) }}
                    @else
                        <span class="text-text-muted">&mdash;</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center whitespace-nowrap">
                    @if($s->estaAbierta())
                        <span class="text-text-muted">&mdash;</span>
                    @elseif($s->diferencia == 0)
                        <x-badge status="success" size="sm">Cuadre Exacto</x-badge>
                    @elseif($s->diferencia > 0)
                        <x-badge status="warning" size="sm">+${{ number_format($s->diferencia, 2) }} Sobrante</x-badge>
                    @else
                        <x-badge status="danger" size="sm">-${{ number_format(abs($s->diferencia), 2) }} Faltante</x-badge>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <x-status-badge :status="$s->estado" size="sm" />
                </td>
                <td class="px-5 py-3 text-right whitespace-nowrap">
                    @if(! $s->estaAbierta())
                        <a href="{{ route('cajas.comprobante', $s) }}" target="_blank"
                            class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center">
                            Ver Z-Report &rarr;
                        </a>
                    @else
                        <a href="{{ route('cajas.cierre', $caja) }}"
                            class="text-xs font-semibold text-success hover:text-success-text inline-flex items-center">
                            Cerrar Turno &rarr;
                        </a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="px-5 py-12 text-center text-text-muted text-xs">
                    No hay turnos registrados en esta caja todavía.
                </td>
            </tr>
        @endforelse

        @if($sesiones->hasPages())
            <x-slot:pagination>
                {{ $sesiones->links() }}
            </x-slot:pagination>
        @endif
    </x-table>

</div>
@endsection
