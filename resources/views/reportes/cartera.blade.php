@extends('layouts.app')

@section('title', 'Reporte de Cartera y Envejecimiento')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Cartera</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reporte de Cartera y Antigüedad de Saldos</h1>
        </div>
        <div>
            <a href="{{ route('reportes.cartera.imprimir') }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.cartera') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Cliente</label>
                <select name="cliente_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todos los Clientes</option>
                    @foreach($clientes as $cl)
                        <option value="{{ $cl->id }}" {{ $clienteId == $cl->id ? 'selected' : '' }}>{{ $cl->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.cartera') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs Antigüedad -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Saldo Total Pendiente</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($saldoTotalPendiente, 2) }}</div>
            <span class="text-xs text-slate-500">Deuda global activa</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Corriente (Al día)</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">${{ number_format($corriente, 2) }}</div>
            <span class="text-xs text-slate-500">No vencida</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mora 1 - 30 Días</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">${{ number_format($mora1a30, 2) }}</div>
            <span class="text-xs text-slate-500">Vencimiento reciente</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mora 31 - 60 Días</span>
            <div class="text-2xl font-extrabold text-orange-600 mt-1">${{ number_format($mora31a60, 2) }}</div>
            <span class="text-xs text-slate-500">Mora intermedia</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mora > 60 Días</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($moraMas60, 2) }}</div>
            <span class="text-xs text-slate-500">Cartera de alto riesgo</span>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Documento</th>
                        <th class="py-3.5 px-4">Cliente</th>
                        <th class="py-3.5 px-4">Emisión</th>
                        <th class="py-3.5 px-4">Vencimiento</th>
                        <th class="py-3.5 px-4 text-right">Monto Original</th>
                        <th class="py-3.5 px-4 text-right">Abonado</th>
                        <th class="py-3.5 px-4 text-right">Saldo Pendiente</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cuentasPaginadas as $cp)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $cp->numero_documento }}</td>
                        <td class="py-3 px-4">{{ $cp->cliente?->razon_social }}</td>
                        <td class="py-3 px-4 text-xs text-slate-500">{{ $cp->fecha_emision->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-xs font-semibold {{ $cp->estaVencida() ? 'text-rose-600' : 'text-slate-600' }}">
                            {{ $cp->fecha_vencimiento->format('d/m/Y') }}
                            @if($cp->estaVencida())
                                <span class="text-[10px] text-rose-500">({{ abs($cp->diasDiferenciaVencimiento()) }} días mora)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">${{ number_format($cp->monto_total, 2) }}</td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-medium">${{ number_format($cp->monto_pagado, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">${{ number_format($cp->saldo_pendiente, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $cp->estado->value === 'PAGADA' ? 'bg-emerald-100 text-emerald-800' : ($cp->estaVencida() ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $cp->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No hay cuentas por cobrar registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $cuentasPaginadas->links() }}
        </div>
    </div>
</div>
@endsection
