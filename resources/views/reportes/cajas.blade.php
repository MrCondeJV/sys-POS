@extends('layouts.app')

@section('title', 'Reporte de Cajas')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Cajas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reporte de Turnos y Arqueos de Caja</h1>
        </div>
        <div>
            <a href="{{ route('reportes.cajas.imprimir', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.cajas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Caja</label>
                <select name="caja_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas</option>
                    @foreach($cajas as $c)
                        <option value="{{ $c->id }}" {{ $cajaId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado</label>
                <select name="estado" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todos</option>
                    <option value="ABIERTA" {{ $estado === 'ABIERTA' ? 'selected' : '' }}>Abierta</option>
                    <option value="CERRADA" {{ $estado === 'CERRADA' ? 'selected' : '' }}>Cerrada</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.cajas') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ventas en Efectivo</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">${{ number_format($totalVentasEfectivo, 2) }}</div>
            <span class="text-xs text-slate-500">Cobrado en ventanilla</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ingresos Extra</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalIngresos, 2) }}</div>
            <span class="text-xs text-slate-500">Inyecciones a caja</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Egresos / Gastos</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($totalEgresos, 2) }}</div>
            <span class="text-xs text-slate-500">Salidas de dinero</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Diferencias Arqueo</span>
            <div class="text-2xl font-extrabold {{ $totalDiferencias < 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">${{ number_format($totalDiferencias, 2) }}</div>
            <span class="text-xs text-slate-500">Sobrantes (+) / Faltantes (-)</span>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Caja / Sede</th>
                        <th class="py-3.5 px-4">Cajero</th>
                        <th class="py-3.5 px-4">Apertura</th>
                        <th class="py-3.5 px-4">Cierre</th>
                        <th class="py-3.5 px-4 text-right">Inicial</th>
                        <th class="py-3.5 px-4 text-right">Esperado</th>
                        <th class="py-3.5 px-4 text-right">Contado</th>
                        <th class="py-3.5 px-4 text-right">Diferencia</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sesiones as $s)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $s->caja?->nombre }}</div>
                            <div class="text-xs text-slate-400">{{ $s->sucursal?->nombre }}</div>
                        </td>
                        <td class="py-3 px-4">{{ $s->cajero?->name }}</td>
                        <td class="py-3 px-4 text-xs text-slate-500">{{ $s->fecha_apertura->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-xs text-slate-500">{{ $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : '--' }}</td>
                        <td class="py-3 px-4 text-right">${{ number_format($s->monto_apertura, 2) }}</td>
                        <td class="py-3 px-4 text-right font-medium">${{ number_format($s->monto_cierre_esperado ?? 0, 2) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">${{ number_format($s->monto_cierre_contado ?? 0, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold {{ (float)$s->diferencia < 0 ? 'text-rose-600' : ((float)$s->diferencia > 0 ? 'text-emerald-600' : 'text-slate-700') }}">
                            ${{ number_format($s->diferencia ?? 0, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $s->estado->value === 'ABIERTA' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                                {{ $s->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">No hay sesiones de caja registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $sesiones->links() }}
        </div>
    </div>
</div>
@endsection
