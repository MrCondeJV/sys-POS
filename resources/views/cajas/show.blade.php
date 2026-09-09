@extends('layouts.app')

@section('title', 'Caja: ' . $caja->nombre)

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Encabezado y Navegación -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('cajas.index') }}" class="hover:text-indigo-600 transition">Cajas & Turnos</a>
                <span>/</span>
                <span class="text-slate-800">Historial de Turnos</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $caja->nombre }}</h1>
                <span class="font-mono text-xs px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-bold">{{ $caja->codigo }}</span>
                @if($caja->estaAbierta())
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        TURNO ABIERTO
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        CERRADA
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500">Sucursal: <span class="font-semibold text-slate-700">{{ $caja->sucursal->nombre }}</span> &bull; Registro cronológico de aperturas, cierres y auditorías.</p>
        </div>

        <div>
            <a href="{{ route('cajas.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cajas
            </a>
        </div>
    </div>

    <!-- Tabla de Turnos Históricos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Historial de Turnos y Arqueos</h2>
                <p class="text-xs text-slate-500 mt-0.5">Auditoría completa de cortes de caja realizados.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $sesiones->total() }} turnos registrados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left uppercase">Apertura / Cajero</th>
                        <th class="px-6 py-3.5 text-left uppercase">Cierre / Auditor</th>
                        <th class="px-6 py-3.5 text-right uppercase">Fondo Inicial</th>
                        <th class="px-6 py-3.5 text-right uppercase">Total Ingresos</th>
                        <th class="px-6 py-3.5 text-right uppercase">Total Egresos</th>
                        <th class="px-6 py-3.5 text-right uppercase">Esperado</th>
                        <th class="px-6 py-3.5 text-right uppercase">Contado</th>
                        <th class="px-6 py-3.5 text-center uppercase">Diferencia</th>
                        <th class="px-6 py-3.5 text-center uppercase">Estado</th>
                        <th class="px-6 py-3.5 text-right uppercase">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($sesiones as $s)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900">{{ $s->cajero->name }}</div>
                                <div class="text-slate-500 text-[11px]">{{ $s->fecha_apertura->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($s->fecha_cierre)
                                    <div class="font-bold text-slate-800">{{ $s->cajeroCierre?->name ?? 'Sistema' }}</div>
                                    <div class="text-slate-500 text-[11px]">{{ $s->fecha_cierre->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="text-slate-400 italic">En operación...</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-700">${{ number_format($s->monto_apertura, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-semibold text-emerald-600">${{ number_format($s->total_ingresos, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-semibold text-rose-600">${{ number_format($s->total_egresos, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-900">
                                @if($s->monto_cierre_esperado !== null)
                                    ${{ number_format($s->monto_cierre_esperado, 2) }}
                                @else
                                    ${{ number_format($s->calcularSaldoEsperadoEfectivo(), 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-900">
                                @if($s->monto_cierre_contado !== null)
                                    ${{ number_format($s->monto_cierre_contado, 2) }}
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                @if($s->estaAbierta())
                                    <span class="text-slate-400">&mdash;</span>
                                @elseif($s->diferencia == 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Cuadre Exacto
                                    </span>
                                @elseif($s->diferencia > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        +${{ number_format($s->diferencia, 2) }} Sobrante
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        -${{ number_format(abs($s->diferencia), 2) }} Faltante
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s->estaAbierta() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $s->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                @if(! $s->estaAbierta())
                                    <a href="{{ route('cajas.comprobante', $s) }}" target="_blank"
                                        class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center">
                                        Ver Z-Report &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('cajas.cierre', $caja) }}"
                                        class="text-xs font-bold text-emerald-600 hover:text-emerald-800 inline-flex items-center">
                                        Cerrar Turno &rarr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                No hay turnos registrados en esta caja todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sesiones->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sesiones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
