@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Presentaciones & Conversiones</h1>
        <p class="text-sm text-slate-500 font-medium">Producto: <span class="font-bold text-slate-700">{{ $producto->nombre }}</span> (Unidad Base: {{ $producto->unidadMedida?->nombre ?? 'Unidad' }})</p>
    </div>
    <a href="{{ route('productos.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
        Volver a Productos
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Formulario de Creación -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6 h-fit">
        <h2 class="text-base font-bold text-slate-800 mb-4">+ Nueva Presentación</h2>

        <form action="{{ route('productos.presentaciones.store', $producto) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nombre Presentación *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Caja x 100, Rollo 50m, Bulto 50kg">
                @error('nombre') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Factor de Conversión *</label>
                <input type="number" step="0.0001" name="factor_conversion" value="{{ old('factor_conversion', 1) }}" min="0.0001" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <span class="text-[11px] text-slate-400">Cantidad de unidades base contenidas (Ej: 1 caja = 100 unidades -> factor = 100)</span>
                @error('factor_conversion') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Precio de Venta Presentación *</label>
                <input type="number" step="0.01" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta) }}" min="0" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('precio_venta') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Unidad de Medida</label>
                <select name="unidad_medida_id" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccione...</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre }} ({{ $u->codigo }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Código de Barras (Opcional)</label>
                <input type="text" name="codigo_barras" value="{{ old('codigo_barras') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition text-sm">
                Guardar Presentación
            </button>
        </form>
    </div>

    <!-- Listado de Presentaciones -->
    <div class="lg:col-span-2 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Presentación</th>
                            <th class="px-6 py-4">Factor</th>
                            <th class="px-6 py-4">Equivalencia Base</th>
                            <th class="px-6 py-4">Precio Venta</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($presentaciones as $pres)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4 font-bold text-slate-800">
                                    {{ $pres->nombre }}
                                    @if($pres->es_predeterminada)
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 font-bold rounded-md">Default</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-indigo-600">x{{ number_format($pres->factor_conversion, 2) }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                                    1 {{ $pres->nombre }} = {{ number_format($pres->factor_conversion, 2) }} {{ $producto->unidadMedida?->nombre ?? 'unidades' }}
                                </td>
                                <td class="px-6 py-4 font-mono font-bold text-slate-800">${{ number_format($pres->precio_venta, 2) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('productos.presentaciones.destroy', [$producto, $pres]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta presentación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                    No hay presentaciones adicionales configuradas. El producto se comercializa únicamente en su unidad base ({{ $producto->unidadMedida?->nombre ?? 'Unidad' }}).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
