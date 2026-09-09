@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Laboratorios Farmacéuticos</h1>
        <p class="text-sm text-slate-500 font-medium">Fabricantes y distribuidores autorizados de medicamentos</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('farmacia.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
            Dashboard Farmacia
        </a>
        <a href="{{ route('laboratorios.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
            + Nuevo Laboratorio
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
                        <th class="px-6 py-4">Laboratorio</th>
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Contacto</th>
                        <th class="px-6 py-4">Medicamentos Asociados</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($laboratorios as $lab)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $lab->nombre }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $lab->codigo ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-xs">
                                <div>{{ $lab->telefono ?? 'Sin teléfono' }}</div>
                                <div class="text-slate-400">{{ $lab->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $lab->productos_count }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $lab->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $lab->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('laboratorios.edit', $lab) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Editar</a>
                                <form action="{{ route('laboratorios.destroy', $lab) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro de eliminar este laboratorio?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No hay laboratorios registrados aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($laboratorios->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $laboratorios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
