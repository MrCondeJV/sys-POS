@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Trazabilidad de Lotes Farmacéuticos</h1>
        <p class="text-sm text-slate-500 font-medium">Control de caducidad, existencias por lote y rotación FEFO</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('farmacia.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
            Dashboard Farmacia
        </a>
        <a href="{{ route('lotes.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
            + Nuevo Lote
        </a>
    </div>
</div>

<div class="space-y-4">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4">
        <form method="GET" action="{{ route('lotes.index') }}" class="flex flex-wrap gap-3 items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por número de lote o medicamento..." class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 flex-1 min-w-[200px]">

            <select name="filtro" class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todos los Estados</option>
                <option value="disponibles" @selected(request('filtro') === 'disponibles')>Disponibles y Vigentes</option>
                <option value="proximos" @selected(request('filtro') === 'proximos')>Próximos a Vencer (< 30 días)</option>
                <option value="vencidos" @selected(request('filtro') === 'vencidos')>Vencidos</option>
            </select>

            <select name="sucursal_id" class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todas las Sedes</option>
                @foreach($sucursales as $suc)
                    <option value="{{ $suc->id }}" @selected(request('sucursal_id') == $suc->id)>{{ $suc->nombre }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition">
                Filtrar
            </button>
        </form>
    </div>

    <!-- Tabla de Lotes -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Lote</th>
                        <th class="px-6 py-4">Medicamento / Producto</th>
                        <th class="px-6 py-4">Sede</th>
                        <th class="px-6 py-4">Stock Actual</th>
                        <th class="px-6 py-4">Fecha Vencimiento</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($lotes as $lote)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $lote->numero_lote }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $lote->producto->nombre }}
                                @if($lote->producto->laboratorio)
                                    <span class="block text-xs font-normal text-slate-400">{{ $lote->producto->laboratorio->nombre }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">{{ $lote->sucursal->nombre }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-slate-800">{{ number_format($lote->stock_actual, 2) }}</td>
                            <td class="px-6 py-4 text-xs">
                                <span class="font-bold {{ $lote->estaVencido() ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $lote->fecha_vencimiento->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $lote->estado->badgeClasses() }}">
                                    {{ $lote->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro de eliminar este lote?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No se encontraron lotes registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lotes->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $lotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
