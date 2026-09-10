@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Traslados entre Sucursales</h1>
            <p class="text-sm text-slate-500">Gestión, despacho y recepción formal de mercancía entre sedes.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reportes.multisucursal') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                Ver Consolidado
            </a>
            <a href="{{ route('traslados.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                Nuevo Traslado
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-4">
        <form method="GET" action="{{ route('traslados.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sucursal Origen</label>
                <select name="origen_id" class="w-full text-sm">
                    <option value="">Todas las sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('origen_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sucursal Destino</label>
                <select name="destino_id" class="w-full text-sm">
                    <option value="">Todas las sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('destino_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                <select name="estado" class="w-full text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $e)
                        <option value="{{ $e->value }}" {{ request('estado') == $e->value ? 'selected' : '' }}>{{ $e->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Consecutivo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ruta</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Motivo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha Envío</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-100">
                @forelse($traslados as $t)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $t->consecutivo }}</td>
                        <td class="px-6 py-4 text-slate-700">
                            {{ $t->sucursalOrigen->nombre }} &rarr; {{ $t->sucursalDestino->nombre }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ Str::limit($t->motivo, 30) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold border {{ $t->estado->badgeClasses() }}">
                                {{ $t->estado->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $t->fecha_envio?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('traslados.show', $t) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No se encontraron traslados de sucursal registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $traslados->links() }}
        </div>
    </div>
</div>
@endsection
