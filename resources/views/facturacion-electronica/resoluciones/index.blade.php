@extends('layouts.app')

@section('content')
<div class=\"mb-6\">
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Resoluciones de Facturación DIAN</h1>
                <p class="text-sm text-slate-500 font-medium">Rangos de numeración, prefijos y vigencias autorizadas</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('facturacion-electronica.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
                    Ver Facturas Electrónicas
                </a>
                <a href="{{ route('resoluciones.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                    + Nueva Resolución
                </a>
            </div>
        </div>
</div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Resolución</th>
                                <th class="px-6 py-4">Prefijo</th>
                                <th class="px-6 py-4">Rango Autorizado</th>
                                <th class="px-6 py-4">Actual</th>
                                <th class="px-6 py-4">Vigencia</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($resoluciones as $res)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $res->numero_resolucion }}
                                        @if($res->es_predeterminada)
                                            <span class="ml-2 px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 font-bold rounded-md border border-indigo-200">Predeterminada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $res->prefijo }}</td>
                                    <td class="px-6 py-4 font-mono">{{ number_format($res->rango_desde) }} - {{ number_format($res->rango_hasta) }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-slate-700">{{ number_format($res->consecutivo_actual) }}</td>
                                    <td class="px-6 py-4 text-xs">
                                        <div>Desde: {{ $res->fecha_inicio->format('d/m/Y') }}</div>
                                        <div>Hasta: {{ $res->fecha_vigencia->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($res->estaVigente())
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Activa y Vigente</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">{{ $res->estado->label() }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('resoluciones.edit', $res) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Editar</a>
                                        <form action="{{ route('resoluciones.destroy', $res) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro de eliminar esta resolución?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                        No hay resoluciones de facturación configuradas para esta empresa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($resoluciones->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $resoluciones->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
