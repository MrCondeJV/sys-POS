@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">💊 Módulo Farmacéutico & Droguería</h1>
        <p class="text-sm text-slate-500 font-medium">Control estricto de lotes, vencimientos, laboratorios y principios activos</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('lotes.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
            + Registrar Lote
        </a>
    </div>
</div>

<div class="space-y-6">
    <!-- Métricas Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold uppercase text-slate-400">Lotes Vigentes</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($lotesDisponibles) }}</div>
            <div class="text-xs text-slate-400 mt-1">Disponibles para dispensación</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold uppercase text-slate-400">Por Vencer (< 30 días)</div>
            <div class="text-2xl font-black text-amber-500 mt-1">{{ number_format($lotesProximos) }}</div>
            <div class="text-xs text-slate-400 mt-1">Prioridad de rotación FEFO</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold uppercase text-slate-400">Lotes Vencidos</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($lotesVencidos) }}</div>
            <div class="text-xs text-slate-400 mt-1">Retirar de inventario activo</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="text-xs font-bold uppercase text-slate-400">Catálogo Farmacéutico</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalMedicamentos) }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $totalLaboratorios }} Laboratorios | {{ $totalPrincipios }} Principios</div>
        </div>
    </div>

    <!-- Navegación Rápida -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('lotes.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <div class="font-bold text-slate-800 group-hover:text-indigo-600">📦 Gestión de Lotes</div>
                <div class="text-xs text-slate-500">Trazabilidad por fecha de caducidad</div>
            </div>
            <span class="text-slate-400 group-hover:translate-x-1 transition">→</span>
        </a>

        <a href="{{ route('laboratorios.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <div class="font-bold text-slate-800 group-hover:text-indigo-600">🏢 Laboratorios</div>
                <div class="text-xs text-slate-500">Fabricantes y casas farmacéuticas</div>
            </div>
            <span class="text-slate-400 group-hover:translate-x-1 transition">→</span>
        </a>

        <a href="{{ route('principios-activos.index') }}" class="p-5 bg-white rounded-2xl border border-slate-200/80 hover:border-indigo-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <div class="font-bold text-slate-800 group-hover:text-indigo-600">🧪 Principios Activos</div>
                <div class="text-xs text-slate-500">Moléculas y concentraciones</div>
            </div>
            <span class="text-slate-400 group-hover:translate-x-1 transition">→</span>
        </a>
    </div>

    <!-- Lotes en Riesgo -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-bold text-slate-800">⚠️ Lotes Críticos (Vencidos o Próximos a Vencer)</h2>
            <a href="{{ route('lotes.index', ['filtro' => 'proximos']) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Ver Todos</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Medicamento</th>
                        <th class="px-6 py-3">Lote</th>
                        <th class="px-6 py-3">Sucursal</th>
                        <th class="px-6 py-3">Stock Actual</th>
                        <th class="px-6 py-3">Vencimiento</th>
                        <th class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lotesCriticos as $lote)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-3 font-semibold text-slate-800">
                                {{ $lote->producto->nombre }}
                                @if($lote->producto->registro_sanitario)
                                    <span class="block text-xs font-normal text-slate-400">Invima: {{ $lote->producto->registro_sanitario }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-mono font-bold text-slate-700">{{ $lote->numero_lote }}</td>
                            <td class="px-6 py-3 text-xs text-slate-600">{{ $lote->sucursal->nombre }}</td>
                            <td class="px-6 py-3 font-mono font-bold text-slate-800">{{ number_format($lote->stock_actual, 2) }}</td>
                            <td class="px-6 py-3 text-xs">
                                <span class="font-bold {{ $lote->estaVencido() ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                </span>
                                <span class="block text-slate-400">
                                    {{ $lote->estaVencido() ? 'Vencido hace ' . abs($lote->diasParaVencer()) . ' días' : 'Vence en ' . $lote->diasParaVencer() . ' días' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $lote->estado->badgeClasses() }}">
                                    {{ $lote->estado->label() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No hay lotes vencidos ni próximos a vencer. ¡Inventario farmacéutico en óptimas condiciones!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
