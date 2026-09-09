@extends('layouts.app')

@section('title', 'Precios de Lista: ' . $listaPrecio->nombre)

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ $listaPrecio->nombre }}</h1>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $listaPrecio->estado === App\Enums\EstadoGeneral::ACTIVO ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $listaPrecio->estado->label() }}
                    </span>
                    @if($listaPrecio->es_predeterminada)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-800">
                            Predeterminada
                        </span>
                    @endif
                </div>
                <div class="text-xs text-slate-500 flex items-center space-x-3 mt-1">
                    <span>Regla Base: <strong>{{ $listaPrecio->tipo_ajuste->label() }}</strong></span>
                    <span>&bull;</span>
                    <span>Ajuste: <strong>{{ $listaPrecio->porcentaje_defecto }}%</strong></span>
                    <span>&bull;</span>
                    <span>Clientes asignados: <strong>{{ $listaPrecio->clientes_count }}</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('listas-precios.edit', $listaPrecio) }}" class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs rounded-xl transition">
                Editar Reglas
            </a>
            <a href="{{ route('listas-precios.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold text-xs rounded-xl transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Formulario para Asignar / Fijar Precio Específico a un Producto -->
    @can('update', $listaPrecio)
    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-3">
            Fijar Precio Individual a un Producto
        </h3>
        <form action="{{ route('listas-precios.precios.store', $listaPrecio) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            @csrf
            <div class="sm:col-span-7">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Selecciona el Producto</label>
                <select name="producto_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                    <option value="">Buscar producto del catálogo...</option>
                    @foreach($productos as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->nombre }} (Precio Base: ${{ number_format($prod->precio_venta, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Precio Especial ($)</label>
                <input type="number" step="0.01" min="0" name="precio" required placeholder="0.00"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-black text-slate-900">
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Asignar Precio
                </button>
            </div>
        </form>
    </div>
    @endcan

    <!-- Listado de Precios Personalizados Configurados -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase">
                Productos con precio específico en esta lista ({{ $detalles->total() }})
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Producto</th>
                        <th class="py-3.5 px-4">Categoría</th>
                        <th class="py-3.5 px-4 text-right">Precio Base</th>
                        <th class="py-3.5 px-4 text-right">Precio en esta Lista</th>
                        <th class="py-3.5 px-4 text-center">Variación</th>
                        <th class="py-3.5 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($detalles as $det)
                    @php
                        $base = (float) $det->producto->precio_venta;
                        $especial = (float) $det->precio;
                        $diff = $especial - $base;
                        $pct = $base > 0 ? round(($diff / $base) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $det->producto->nombre }}
                            <div class="text-[10px] font-mono text-slate-400">{{ $det->producto->codigo ?? $det->producto->sku }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $det->producto->categoria?->nombre ?? 'General' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-500">
                            ${{ number_format($base, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm">
                            ${{ number_format($especial, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($diff < 0)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-800">
                                    {{ $pct }}%
                                </span>
                            @elseif($diff > 0)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-800">
                                    +{{ $pct }}%
                                </span>
                            @else
                                <span class="text-slate-400 font-mono">0%</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @can('update', $listaPrecio)
                            <form action="{{ route('listas-precios.precios.destroy', [$listaPrecio, $det]) }}" method="POST" onsubmit="return confirm('¿Eliminar precio especial de este producto?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-red-500 hover:text-red-700 text-xs font-bold">
                                    Quitar
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            No hay productos con precio específico en esta lista.<br>
                            Se aplicará la regla general: <strong>{{ $listaPrecio->tipo_ajuste->label() }}</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($detalles->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $detalles->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
