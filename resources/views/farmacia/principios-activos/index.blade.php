@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Principios Activos / Fármacos Base</h1>
        <p class="text-sm text-slate-500 font-medium">Moléculas terapéuticas, concentraciones y compatibilidad de genéricos</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('farmacia.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
            Dashboard Farmacia
        </a>
        <a href="{{ route('principios-activos.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
            + Nuevo Principio Activo
        </a>
    </div>
</div>

<div class="space-y-4">
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Principio Activo</th>
                        <th class="px-6 py-4">Concentración</th>
                        <th class="px-6 py-4">Medicamentos Asociados</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($principios as $pa)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $pa->nombre }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-600">{{ $pa->concentracion ?? 'N/A' }}</td>
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $pa->productos_count }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $pa->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $pa->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('principios-activos.edit', $pa) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Editar</a>
                                <form action="{{ route('principios-activos.destroy', $pa) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro de eliminar este principio activo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                No hay principios activos registrados aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($principios->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $principios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
