@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Traslados entre Sucursales</h1>
            <p class="text-sm text-gray-500">Gestión, despacho y recepción formal de mercancía entre sedes.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reportes.multisucursal') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Ver Consolidado
            </a>
            <a href="{{ route('traslados.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                Nuevo Traslado
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-4">
        <form method="GET" action="{{ route('traslados.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700">Sucursal Origen</label>
                <select name="origen_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Todas las sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('origen_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Sucursal Destino</label>
                <select name="destino_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Todas las sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('destino_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Estado</label>
                <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $e)
                        <option value="{{ $e->value }}" {{ request('estado') == $e->value ? 'selected' : '' }}>{{ $e->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Consecutivo</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Ruta</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Motivo</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Fecha Envío</th>
                    <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Acción</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($traslados as $t)
                    <tr>
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $t->consecutivo }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $t->sucursalOrigen->nombre }} &rarr; {{ $t->sucursalDestino->nombre }}
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ Str::limit($t->motivo, 30) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $t->estado->badgeClasses() }}">
                                {{ $t->estado->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $t->fecha_envio?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('traslados.show', $t) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron traslados de sucursal registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $traslados->links() }}
        </div>
    </div>
</div>
@endsection
